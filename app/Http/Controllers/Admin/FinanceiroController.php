<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceiroMovimentacao;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $ano = (int) $request->get('ano', now()->year);

        $query = FinanceiroMovimentacao::whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('descricao', 'like', '%' . $request->search . '%')
                    ->orWhere('descricao_original', 'like', '%' . $request->search . '%');
            });
        }

        $ordenacao = $request->get('ordenacao', 'data_desc');
        switch ($ordenacao) {
            case 'data_asc':
                $query->orderBy('data_vencimento', 'ASC');
                break;
            case 'valor_desc':
                $query->orderBy('valor', 'DESC');
                break;
            case 'valor_asc':
                $query->orderBy('valor', 'ASC');
                break;
            case 'data_desc':
            default:
                $query->orderBy('data_vencimento', 'DESC');
                break;
        }

        $movimentacoes = $query->get();

        $totaisMes = FinanceiroMovimentacao::whereMonth('data_vencimento', $mes)->whereYear('data_vencimento', $ano)->get();
        $totalReceitas = $totaisMes->where('tipo', 'receita')->where('status_pagamento', 'pago')->sum('valor');
        $totalDespesas = $totaisMes->where('tipo', 'despesa')->where('status_pagamento', 'pago')->sum('valor');
        $saldo = $totalReceitas - $totalDespesas;

        $despesasPorCategoria = $totaisMes->where('tipo', 'despesa')->where('status_pagamento', 'pago')
            ->groupBy('categoria')->map(fn($row) => $row->sum('valor'))->sortDesc();

        $totalServicosAvista = \App\Models\Servico::whereMonth('data_servico', $mes)->whereYear('data_servico', $ano)
            ->where('status_pagamento', 'pago')->where('tipo_pagamento', 'avista')->sum('valor');
        $totalParcelas = \Illuminate\Support\Facades\DB::table('parcelas')->whereMonth('data_pagamento', $mes)
            ->whereYear('data_pagamento', $ano)->where('status', 'paga')->whereNull('deleted_at')->sum('valor_parcela');
        $ganhoServicosRegistrados = $totalServicosAvista + $totalParcelas;

        $graficoLabels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $graficoReceitas = array_fill(0, 12, 0);
        $graficoDespesas = array_fill(0, 12, 0);
        $graficoServicos = array_fill(0, 12, 0);

        $movsAno = FinanceiroMovimentacao::whereYear('data_vencimento', $ano)->where('status_pagamento', 'pago')->get();
        foreach ($movsAno as $mov) {
            $mesIdx = $mov->data_vencimento->month - 1;
            if ($mov->tipo == 'receita') $graficoReceitas[$mesIdx] += $mov->valor;
            if ($mov->tipo == 'despesa') $graficoDespesas[$mesIdx] += $mov->valor;
        }

        $servicosAno = \App\Models\Servico::whereYear('data_servico', $ano)->where('status_pagamento', 'pago')->where('tipo_pagamento', 'avista')->get();
        foreach ($servicosAno as $servico) {
            $mesIdx = \Carbon\Carbon::parse($servico->data_servico)->month - 1;
            $graficoServicos[$mesIdx] += $servico->valor;
        }

        $parcelasAno = \Illuminate\Support\Facades\DB::table('parcelas')->whereYear('data_pagamento', $ano)->where('status', 'paga')->whereNull('deleted_at')->get();
        foreach ($parcelasAno as $parcela) {
            $mesIdx = \Carbon\Carbon::parse($parcela->data_pagamento)->month - 1;
            $graficoServicos[$mesIdx] += $parcela->valor_parcela;
        }

        $categorias = FinanceiroMovimentacao::distinct()->pluck('categoria')->sort();

        return view('admin.financeiro.index', compact(
            'movimentacoes',
            'mes',
            'ano',
            'totalReceitas',
            'totalDespesas',
            'saldo',
            'categorias',
            'despesasPorCategoria',
            'ganhoServicosRegistrados',
            'ordenacao',
            'graficoLabels',
            'graficoReceitas',
            'graficoDespesas',
            'graficoServicos'
        ));
    }

    public function analisarOfx(Request $request)
    {
        set_time_limit(300);

        $request->validate(['arquivo_ofx' => 'required|file']);

        try {
            $content = file_get_contents($request->file('arquivo_ofx')->getPathname());
            preg_match_all('/<STMTTRN>([\s\S]*?)(?=<\/?STMTTRN>|<\/BANKTRANLIST>)/', $content, $matches);

            if (empty($matches[0])) return response()->json(['success' => false, 'message' => 'Nenhuma transação identificada no arquivo.']);

            $lancamentos = [];
            $descricoesParaIA = [];

            foreach ($matches[0] as $index => $t) {
                preg_match('/<DTPOSTED>(\d{4})(\d{2})(\d{2})/', $t, $dt);
                $data = $dt ? "{$dt[1]}-{$dt[2]}-{$dt[3]}" : date('Y-m-d');
                
                preg_match('/<TRNAMT>([-\d\.]+)/', $t, $amt);
                $valorBruto = $amt ? (float) $amt[1] : 0;

                preg_match('/<MEMO>([\s\S]*?)(?:<\/MEMO>|<[A-Z]+>|\n<|$)/', $t, $memo);
                $descricaoOriginal = $memo ? trim(preg_replace('/\s+/', ' ', $memo[1])) : 'Transação Bancária';

                $lancamentos[$index] = [
                    'id_temp' => $index,
                    'data_vencimento' => $data,
                    'descricao_original' => $descricaoOriginal,
                    'descricao' => Str::limit($descricaoOriginal, 255),
                    'valor' => abs($valorBruto),
                    'tipo' => $valorBruto > 0 ? 'receita' : 'despesa',
                    'categoria' => 'Outros'
                ];

                $descricoesParaIA[] = [
                    'id' => $index,
                    'descricao' => $descricaoOriginal,
                    'tipo' => $valorBruto > 0 ? 'Entrada' : 'Saida',
                    'valor' => abs($valorBruto)
                ];
            }

            $debugIA = 'IA Iniciada. ';

            try {
                $apiKey = env('GEMINI_API_KEY');
                if ($apiKey) {                    
                    $chunks = array_chunk($descricoesParaIA, 50);
                    $debugIA .= "Dividido em " . count($chunks) . " lotes. ";

                    foreach ($chunks as $indiceLote => $chunk) {
                        $prompt = "Você é um analista financeiro corporativo. Avalie transações de um OFX (Sicredi). 
                        Retorne EXATAMENTE E APENAS um array JSON plano: [{\"id\": 0, \"categoria\": \"...\", \"descricao_limpa\": \"...\"}]
                        
                        REGRAS PARA 'descricao_limpa':
                        - Limpe a sujeira. Remova códigos (PIX, LIQ.COBRANCA, RECEBIMENTO, PAGAMENTO, COMPRAS NACIONAIS, números de transação, CNPJ/CPF).
                        - Capitalize como nomes próprios. (Ex: 'RECEBIMENTO PIX-PIX_CRED 01017964041 CLADIMIR DENARDI' vira 'Cladimir Denardi'). (Ex: 'COMPRAS NACIONAIS-VE0493509 POSTO SERRA' vira 'Posto Serra').
                        
                        REGRAS DE CATEGORIA:
                        - Recebimentos de Pix e Cobranças Simples -> 'Receita de Serviços'
                        - RGE SUL, POSTO, COMERCIO -> 'Custos de Operação'
                        - PICHAU, TERABYTE -> 'Equipamentos'
                        - SEFAZ, DETRAN, DARF, SIND -> 'Impostos e Taxas'
                        - TARIFA, CESTA -> 'Tarifas Bancárias'
                        - Outros gastos fixos com distribuidoras/Cia/Importação -> 'Fornecedores'
                        
                        5. Funcionarios:
                            - Artur Soares, Estevan Zimermann de Carli, Alex da Rosa Rodrigues
                            - Classifique como 'Salários e Pró-labore'
                            
                        Transações para classificar: " . json_encode($chunk);

                        $response = Http::withoutVerifying()
                            ->timeout(60)
                            ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                            ->withHeaders(['Content-Type' => 'application/json'])
                            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $apiKey, [
                                'contents' => [['parts' => [['text' => $prompt]]]],
                                'generationConfig' => [
                                    'response_mime_type' => 'application/json',
                                ]
                            ]);

                        if ($response->successful()) {
                            $iaText = $response->json('candidates.0.content.parts.0.text');
                            
                            $resultadoIA = json_decode(trim($iaText), true);

                            if (json_last_error() === JSON_ERROR_NONE && is_array($resultadoIA)) {
                                $lista = isset($resultadoIA['transacoes']) ? $resultadoIA['transacoes'] : $resultadoIA;

                                foreach ($lista as $item) {
                                    $idx = isset($item['id']) ? (int)$item['id'] : -1;
                                    
                                    if (isset($lancamentos[$idx])) {
                                        if (!empty($item['categoria'])) {
                                            $lancamentos[$idx]['categoria'] = $item['categoria'];
                                        }
                                        if (!empty($item['descricao_limpa'])) {
                                            $lancamentos[$idx]['descricao'] = Str::title(strtolower(trim($item['descricao_limpa'])));
                                        }
                                    }
                                }
                                $debugIA .= "[Lote ".($indiceLote+1)." OK] ";
                            } else {
                                $debugIA .= "[Erro JSON Lote ".($indiceLote+1)."] ";
                            }
                        } else {
                            $debugIA .= "[Erro HTTP Lote ".($indiceLote+1)."] ";
                        }
                    }
                }
            } catch (\Exception $e) {
                $debugIA .= "Erro Fatal IA: " . $e->getMessage();
            }

            return response()->json(['success' => true, 'dados' => array_values($lancamentos), 'debug' => $debugIA]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    public function salvarLote(Request $request)
    {
        $dados = $request->validate([
            'lancamentos' => 'required|array',
            'lancamentos.*.descricao' => 'required|string',
            'lancamentos.*.descricao_original' => 'required|string',
            'lancamentos.*.valor' => 'required|numeric',
            'lancamentos.*.data_vencimento' => 'required|date',
            'lancamentos.*.tipo' => 'required|in:receita,despesa',
            'lancamentos.*.categoria' => 'required|string',
        ]);

        $loteId = Str::uuid();
        $count = 0;

        foreach ($dados['lancamentos'] as $lanc) {
            // A verificação de duplicidade usa a DESCRIÇÃO ORIGINAL que nunca muda!
            $existe = FinanceiroMovimentacao::where('valor', $lanc['valor'])
                ->where('data_vencimento', $lanc['data_vencimento'])
                ->where('descricao_original', $lanc['descricao_original'])
                ->where('tipo', $lanc['tipo'])
                ->exists();

            if (!$existe) {
                FinanceiroMovimentacao::create([
                    'descricao' => $lanc['descricao'],
                    'descricao_original' => $lanc['descricao_original'],
                    'valor' => $lanc['valor'],
                    'data_vencimento' => $lanc['data_vencimento'],
                    'data_pagamento' => $lanc['data_vencimento'],
                    'tipo' => $lanc['tipo'],
                    'categoria' => $lanc['categoria'],
                    'status_pagamento' => 'pago',
                    'lote_importacao' => $loteId,
                    'user_id' => Auth::id()
                ]);
                $count++;
            }
        }

        return response()->json(['success' => true, 'message' => "{$count} lançamentos conciliados com sucesso!"]);
    }

    public function destroy($id)
    {
        $movimentacao = FinanceiroMovimentacao::findOrFail($id);
        $movimentacao->delete();
        return redirect()->back()->with('success', 'Lançamento excluído com sucesso!');
    }
}
