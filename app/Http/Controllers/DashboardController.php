<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servico;
use App\Models\Cliente;
use App\Models\Parcela;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Foco em cobranças - removendo totais gerais
        $clientesParaCobrar = $this->getClientesParaCobrar();
        $parcelasVencidas = $this->getParcelasVencidas();
        $parcelasAVencer = $this->getParcelasAVencer();
        $servicosPendentes = $this->getServicosPendentes();

        // Estatísticas focadas em cobranças
        $estatisticasCobranca = [
            'clientes_pendentes' => $this->getTotalClientesComPendencia(),
            'parcelas_vencidas' => $parcelasVencidas->count(),
            'parcelas_a_vencer' => $parcelasAVencer->count(),
            'valor_total_vencido' => $this->getValorTotalVencido(),
            'valor_total_a_vencer' => $this->getValorTotalAVencer(),
        ];

        // Progressão mensal de serviços
        $progressaoMensal = $this->getProgressaoMensal();

        return view('dashboard', compact(
            'clientesParaCobrar',
            'parcelasVencidas',
            'parcelasAVencer',
            'servicosPendentes',
            'estatisticasCobranca',
            'progressaoMensal'
        ));
    }

    /**
     * Clientes que possuem pendências financeiras
     */
    private function getClientesParaCobrar()
    {
        return Cliente::whereHas('servicos', function($query) {
                $query->where('status_pagamento', 'pendente')
                      ->orWhere('status_pagamento', 'nao_pago');
            })
            ->with(['servicos' => function($query) {
                $query->whereIn('status_pagamento', ['pendente', 'nao_pago'])
                      ->with('parcelasServico');
            }])
            ->orderBy('nome')
            ->take(10)
            ->get()
            ->map(function($cliente) {
                $cliente->total_pendente = $cliente->servicos
                    ->whereIn('status_pagamento', ['pendente', 'nao_pago'])
                    ->sum('valor');
                
                $cliente->parcelas_vencidas = $cliente->servicos
                    ->flatMap(function($servico) {
                        return $servico->parcelasServico->where('status', 'pendente')
                            ->where('data_vencimento', '<', now());
                    })
                    ->count();

                return $cliente;
            });
    }

    /**
     * Parcelas vencidas (com detalhes para cobrança)
     */
    private function getParcelasVencidas()
    {
        return Parcela::where('status', 'pendente')
            ->where('data_vencimento', '<', now())
            ->with(['servico.cliente'])
            ->orderBy('data_vencimento')
            ->take(15)
            ->get();
    }

    /**
     * Parcelas que vencerão nos próximos 7 dias
     */
    private function getParcelasAVencer()
    {
        return Parcela::where('status', 'pendente')
            ->whereBetween('data_vencimento', [now(), now()->addDays(7)])
            ->with(['servico.cliente'])
            ->orderBy('data_vencimento')
            ->take(10)
            ->get();
    }

    /**
     * Serviços com pagamento pendente ou não pago
     */
    private function getServicosPendentes()
    {
        return Servico::whereIn('status_pagamento', ['pendente', 'nao_pago'])
            ->with('cliente')
            ->orderBy('data_servico')
            ->take(10)
            ->get();
    }

    /**
     * Total de clientes com pendências
     */
    private function getTotalClientesComPendencia()
    {
        return Cliente::whereHas('servicos', function($query) {
            $query->whereIn('status_pagamento', ['pendente', 'nao_pago']);
        })->count();
    }

    /**
     * Valor total vencido
     */
    private function getValorTotalVencido()
    {
        return Parcela::where('status', 'pendente')
            ->where('data_vencimento', '<', now())
            ->sum('valor_parcela');
    }

    /**
     * Valor total a vencer nos próximos 7 dias
     */
    private function getValorTotalAVencer()
    {
        return Parcela::where('status', 'pendente')
            ->whereBetween('data_vencimento', [now(), now()->addDays(7)])
            ->sum('valor_parcela');
    }

    /**
     * Progressão mensal de serviços cadastrados (últimos 6 meses)
     */
    private function getProgressaoMensal()
    {
        $data = [];
        $meses = [];
        $quantidades = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $mesFormatado = $mes->translatedFormat('M/Y'); // Formato em português
            
            $total = Servico::whereMonth('created_at', $mes->month)
                ->whereYear('created_at', $mes->year)
                ->count();

            $meses[] = $mesFormatado;
            $quantidades[] = $total;
        }

        return [
            'meses' => $meses,
            'quantidades' => $quantidades
        ];
    }

    public function relatorios()
    {
        // Dados padrão para o período atual
        $periodo = request('periodo', 'mes_atual');
        $dataInicio = $this->getDataInicioPorPeriodo($periodo);
        $dataFim = now()->format('Y-m-d');
        
        // Dados para os gráficos prontos
        $dadosRelatorios = $this->getDadosRelatorios($dataInicio, $dataFim);
        
        return view('admin.relatorios.index', compact('dadosRelatorios', 'periodo'));
    }

    private function getDataInicioPorPeriodo($periodo)
    {
        switch ($periodo) {
            case 'semana_atual':
                return now()->startOfWeek()->format('Y-m-d');
            case 'mes_anterior':
                return now()->subMonth()->startOfMonth()->format('Y-m-d');
            case 'trimestre_atual':
                return now()->startOfQuarter()->format('Y-m-d');
            case 'semestre_atual':
                $trimestre = ceil(now()->month / 6);
                return now()->month(($trimestre - 1) * 6 + 1)->startOfMonth()->format('Y-m-d');
            case 'ano_atual':
                return now()->startOfYear()->format('Y-m-d');
            case 'mes_atual':
            default:
                return now()->startOfMonth()->format('Y-m-d');
        }
    }

    private function getDadosRelatorios($dataInicio, $dataFim)
    {
        return [
            'resumo' => $this->getResumoGeral($dataInicio, $dataFim),
            'graficos' => $this->getGraficosProntos($dataInicio, $dataFim),
            'insights' => $this->getInsights($dataInicio, $dataFim),
            'tabelas' => $this->getTabelasPrincipais($dataInicio, $dataFim)
        ];
    }

    private function getResumoGeral($dataInicio, $dataFim)
    {
        $servicos = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])->get();
        
        return [
            'valor_total_arrecadado' => $servicos->where('status_pagamento', 'pago')->sum('valor'),
            'valor_total_pendente' => $servicos->where('status_pagamento', 'pendente')->sum('valor'),
            'total_servicos' => $servicos->count(),
            'ticket_medio' => $servicos->avg('valor'),
            'novos_clientes' => Cliente::whereBetween('created_at', [$dataInicio, $dataFim])->count(),
            'clientes_ativos' => Cliente::whereHas('servicos', function($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
            })->count(),
            'valor_ano_atual' => $this->getValorAnoAtual(),
            'crescimento_mensal' => $this->getCrescimentoMensal()
        ];
    }

    private function getValorAnoAtual()
    {
        return Servico::whereYear('data_servico', now()->year)
            ->where('status_pagamento', 'pago')
            ->sum('valor');
    }

    private function getCrescimentoMensal()
    {
        $mesAtual = Servico::whereMonth('data_servico', now()->month)
            ->whereYear('data_servico', now()->year)
            ->where('status_pagamento', 'pago')
            ->sum('valor');
            
        $mesAnterior = Servico::whereMonth('data_servico', now()->subMonth()->month)
            ->whereYear('data_servico', now()->subMonth()->year)
            ->where('status_pagamento', 'pago')
            ->sum('valor');
            
        if ($mesAnterior == 0) return 100;
        
        return (($mesAtual - $mesAnterior) / $mesAnterior) * 100;
    }

    private function getGraficosProntos($dataInicio, $dataFim)
    {
        return [
            'faturamento_mensal' => $this->getFaturamentoMensal(),
            'status_pagamento' => $this->getStatusPagamento($dataInicio, $dataFim),
            'top_clientes' => $this->getTopClientes($dataInicio, $dataFim),
            'servicos_mais_comuns' => $this->getServicosMaisComuns($dataInicio, $dataFim),
            'evolucao_parcelas' => $this->getEvolucaoParcelas($dataInicio, $dataFim)
        ];
    }

    private function getFaturamentoMensal()
    {
        $dados = [];
        for ($i = 11; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $total = Servico::whereMonth('data_servico', $mes->month)
                ->whereYear('data_servico', $mes->year)
                ->where('status_pagamento', 'pago')
                ->sum('valor');
                
            $dados['labels'][] = $mes->translatedFormat('M/Y');
            $dados['valores'][] = $total;
        }
        
        return $dados;
    }

    private function getStatusPagamento($dataInicio, $dataFim)
    {
        $dados = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->select('status_pagamento', DB::raw('COUNT(*) as total, SUM(valor) as valor_total'))
            ->groupBy('status_pagamento')
            ->get();

        return [
            'labels' => $dados->pluck('status_pagamento')->map(fn($s) => ucfirst(str_replace('_', ' ', $s))),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('valor_total')
        ];
    }

    private function getTopClientes($dataInicio, $dataFim)
    {
        $clientes = Cliente::withSum(['servicos' => function($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
        }], 'valor')
        ->withCount(['servicos' => function($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
        }])
        ->orderBy('servicos_sum_valor', 'desc')
        ->limit(8)
        ->get();

        return [
            'labels' => $clientes->pluck('nome'),
            'valores' => $clientes->pluck('servicos_sum_valor'),
            'quantidades' => $clientes->pluck('servicos_count')
        ];
    }

    private function getServicosMaisComuns($dataInicio, $dataFim)
    {
        $servicos = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->select('descricao', DB::raw('COUNT(*) as total, SUM(valor) as valor_total'))
            ->groupBy('descricao')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return [
            'labels' => $servicos->pluck('descricao'),
            'quantidades' => $servicos->pluck('total'),
            'valores' => $servicos->pluck('valor_total')
        ];
    }

    private function getEvolucaoParcelas($dataInicio, $dataFim)
    {
        $parcelasPagas = Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->where('status', 'paga')
            ->select(
                DB::raw('YEAR(data_vencimento) as ano'),
                DB::raw('MONTH(data_vencimento) as mes'),
                DB::raw('SUM(valor_parcela) as total')
            )
            ->groupBy('ano', 'mes')
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();

        $parcelasPendentes = Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->where('status', 'pendente')
            ->select(
                DB::raw('YEAR(data_vencimento) as ano'),
                DB::raw('MONTH(data_vencimento) as mes'),
                DB::raw('SUM(valor_parcela) as total')
            )
            ->groupBy('ano', 'mes')
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $parcelasPagas->map(fn($item) => date('M/Y', mktime(0, 0, 0, $item->mes, 1, $item->ano))),
            'pagas' => $parcelasPagas->pluck('total'),
            'pendentes' => $parcelasPendentes->pluck('total')
        ];
    }

    private function getInsights($dataInicio, $dataFim)
    {
        return [
            'melhor_cliente' => $this->getMelhorCliente($dataInicio, $dataFim),
            'servico_mais_lucrativo' => $this->getServicoMaisLucrativo($dataInicio, $dataFim),
            'dia_semana_mais_produtivo' => $this->getDiaSemanaMaisProdutivo($dataInicio, $dataFim),
            'taxa_inadimplencia' => $this->getTaxaInadimplencia($dataInicio, $dataFim),
            'previsao_faturamento' => $this->getPrevisaoFaturamento()
        ];
    }

    private function getMelhorCliente($dataInicio, $dataFim)
    {
        return Cliente::withSum(['servicos' => function($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
        }], 'valor')
        ->orderBy('servicos_sum_valor', 'desc')
        ->first();
    }

    private function getServicoMaisLucrativo($dataInicio, $dataFim)
    {
        return Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->select('descricao', DB::raw('SUM(valor) as total, COUNT(*) as quantidade'))
            ->groupBy('descricao')
            ->orderBy('total', 'desc')
            ->first();
    }

    private function getDiaSemanaMaisProdutivo($dataInicio, $dataFim)
    {
        $dias = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        $dia = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->select(DB::raw('DAYOFWEEK(data_servico) as dia_semana, COUNT(*) as total'))
            ->groupBy('dia_semana')
            ->orderBy('total', 'desc')
            ->first();

        return $dia ? $dias[$dia->dia_semana] : 'N/A';
    }

    private function getTaxaInadimplencia($dataInicio, $dataFim)
    {
        $totalServicos = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])->count();
        $servicosInadimplentes = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->where('status_pagamento', 'nao_pago')
            ->count();

        return $totalServicos > 0 ? ($servicosInadimplentes / $totalServicos) * 100 : 0;
    }

    private function getPrevisaoFaturamento()
    {
        $mediaMensal = Servico::whereYear('data_servico', now()->year)
            ->where('status_pagamento', 'pago')
            ->avg('valor');

        return $mediaMensal * 12;
    }

    private function getTabelasPrincipais($dataInicio, $dataFim)
    {
        return [
            'servicos_recentes' => $this->getServicosRecentes($dataInicio, $dataFim),
            'parcelas_vencidas' => $this->getParcelasVencidasRelatorio($dataInicio, $dataFim),
            'clientes_ativos' => $this->getClientesAtivos($dataInicio, $dataFim)
        ];
    }

    private function getServicosRecentes($dataInicio, $dataFim)
    {
        return Servico::with('cliente')
            ->whereBetween('data_servico', [$dataInicio, $dataFim])
            ->orderBy('data_servico', 'desc')
            ->limit(10)
            ->get()
            ->map(function($servico) {
                return [
                    'data' => $servico->data_servico,
                    'cliente' => $servico->cliente->nome,
                    'servico' => $servico->descricao,
                    'valor' => $servico->valor,
                    'status' => $servico->status_pagamento
                ];
            });
    }

    private function getParcelasVencidasRelatorio($dataInicio, $dataFim)
    {
        return Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->where('status', 'pendente')
            ->where('data_vencimento', '<', now())
            ->with(['servico.cliente'])
            ->orderBy('data_vencimento')
            ->get()
            ->map(function($parcela) {
                return [
                    'cliente' => $parcela->servico->cliente->nome,
                    'servico' => $parcela->servico->descricao,
                    'valor' => $parcela->valor_parcela,
                    'vencimento' => $parcela->data_vencimento,
                    'dias_atraso' => now()->diffInDays($parcela->data_vencimento)
                ];
            });
    }

    private function getClientesAtivos($dataInicio, $dataFim)
    {
        return Cliente::whereHas('servicos', function($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
            })
            ->withCount(['servicos' => function($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
            }])
            ->withSum(['servicos' => function($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
            }], 'valor')
            ->orderBy('servicos_sum_valor', 'desc')
            ->limit(10)
            ->get()
            ->map(function($cliente) {
                return [
                    'nome' => $cliente->nome,
                    'total_servicos' => $cliente->servicos_count,
                    'valor_total' => $cliente->servicos_sum_valor ?? 0,
                    'telefone' => $cliente->celular
                ];
            });
    }

    public function exportarRelatorio(Request $request)
    {
        $tipo = $request->tipo ?? 'pdf';
        $periodo = $request->periodo ?? 'mes_atual';
        $dataInicio = $this->getDataInicioPorPeriodo($periodo);
        $dataFim = now()->format('Y-m-d');
        
        $dados = $this->getDadosRelatorios($dataInicio, $dataFim);
        
        if ($tipo === 'excel') {
            return $this->exportarExcel($dados, $periodo);
        }
        
        return $this->exportarPDF($dados, $periodo);
    }

    private function exportarPDF($dados, $periodo)
    {
        // Implementação básica de exportação PDF
        // Em produção, use uma biblioteca como DomPDF
        $html = view('admin.relatorios.export.pdf', compact('dados', 'periodo'))->render();
        
        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="relatorio_' . $periodo . '.pdf"');
    }

    private function exportarExcel($dados, $periodo)
    {
        // Implementação básica de exportação Excel
        // Em produção, use uma biblioteca como PhpSpreadsheet
        $csv = $this->gerarCSV($dados);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="relatorio_' . $periodo . '.csv"');
    }

    private function gerarCSV($dados)
    {
        $lines = [];
        $lines[] = "Relatório de Serviços - " . now()->format('d/m/Y');
        $lines[] = "";
        
        // Resumo
        $lines[] = "RESUMO GERAL";
        foreach ($dados['resumo'] as $chave => $valor) {
            $lines[] = ucfirst(str_replace('_', ' ', $chave)) . ";" . $valor;
        }
        
        $lines[] = "";
        $lines[] = "INSIGHTS";
        foreach ($dados['insights'] as $chave => $valor) {
            if (is_object($valor)) {
                $lines[] = ucfirst(str_replace('_', ' ', $chave)) . ";" . $valor->nome . " (R$ " . number_format($valor->total ?? $valor->servicos_sum_valor, 2, ',', '.') . ")";
            } else {
                $lines[] = ucfirst(str_replace('_', ' ', $chave)) . ";" . $valor;
            }
        }
        
        return implode("\n", $lines);
    }
}