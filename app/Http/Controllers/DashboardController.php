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
        return view('admin.relatorios.index');
    }

    public function relatoriosDados(Request $request)
    {
        $dataInicio = $request->data_inicio ?: date('Y-m-01');
        $dataFim = $request->data_fim ?: date('Y-m-t');
        $tipoRelatorio = $request->tipo_relatorio ?: 'financeiro';
        $tipoGrafico = $request->tipo_grafico ?: 'bar';
        $agrupamento = $request->agrupamento ?: 'mes';

        // Dados baseados no tipo de relatório selecionado
        switch ($tipoRelatorio) {
            case 'financeiro':
                $dados = $this->getDadosFinanceiros($dataInicio, $dataFim, $agrupamento);
                break;
            case 'servicos':
                $dados = $this->getDadosServicos($dataInicio, $dataFim, $agrupamento);
                break;
            case 'clientes':
                $dados = $this->getDadosClientes($dataInicio, $dataFim);
                break;
            case 'parcelas':
                $dados = $this->getDadosParcelas($dataInicio, $dataFim, $agrupamento);
                break;
            default:
                $dados = $this->getDadosFinanceiros($dataInicio, $dataFim, $agrupamento);
        }

        return response()->json([
            'resumo' => $dados['resumo'],
            'graficos' => $dados['graficos'],
            'tabelas' => $dados['tabelas'],
            'config' => [
                'tipo_grafico' => $tipoGrafico,
                'agrupamento' => $agrupamento,
                'tipo_relatorio' => $tipoRelatorio
            ]
        ]);
    }

    private function getDadosFinanceiros($dataInicio, $dataFim, $agrupamento)
    {
        // Resumo Financeiro
        $resumo = $this->getResumoFinanceiro($dataInicio, $dataFim);

        // Gráficos Financeiros
        $graficos = [
            'status_pagamento' => $this->getGraficoStatusPagamento($dataInicio, $dataFim),
            'evolucao_mensal' => $this->getGraficoEvolucaoMensal($dataInicio, $dataFim, $agrupamento),
            'comparativo_tipos' => $this->getGraficoTiposPagamento($dataInicio, $dataFim)
        ];

        // Tabelas
        $tabelas = [
            'top_clientes' => $this->getTopClientes($dataInicio, $dataFim),
            'servicos_detalhados' => $this->getServicosDetalhados($dataInicio, $dataFim),
            'parcelas_vencidas' => $this->getParcelasVencidasRelatorio($dataInicio, $dataFim)
        ];

        return compact('resumo', 'graficos', 'tabelas');
    }

    private function getDadosServicos($dataInicio, $dataFim, $agrupamento)
    {
        $resumo = [
            'total_servicos' => Servico::whereBetween('data_servico', [$dataInicio, $dataFim])->count(),
            'servicos_concluidos' => Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
                ->where('status_pagamento', 'pago')->count(),
            'servicos_pendentes' => Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
                ->where('status_pagamento', 'pendente')->count(),
            'ticket_medio' => Servico::whereBetween('data_servico', [$dataInicio, $dataFim])->avg('valor')
        ];

        $graficos = [
            'servicos_mensal' => $this->getGraficoServicosMensal($dataInicio, $dataFim, $agrupamento),
            'categorias_servicos' => $this->getGraficoCategoriasServicos($dataInicio, $dataFim),
            'status_servicos' => $this->getGraficoStatusServicos($dataInicio, $dataFim)
        ];

        $tabelas = [
            'servicos_recentes' => $this->getServicosRecentes($dataInicio, $dataFim),
            'clientes_ativos' => $this->getClientesAtivos($dataInicio, $dataFim)
        ];

        return compact('resumo', 'graficos', 'tabelas');
    }

    private function getDadosClientes($dataInicio, $dataFim)
    {
        $resumo = [
            'total_clientes' => Cliente::count(),
            'clientes_ativos' => Cliente::whereHas('servicos', function($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
            })->count(),
            'novos_clientes' => Cliente::whereBetween('created_at', [$dataInicio, $dataFim])->count(),
            'valor_medio_cliente' => $this->getValorMedioPorCliente($dataInicio, $dataFim)
        ];

        $graficos = [
            'clientes_mensal' => $this->getGraficoClientesMensal($dataInicio, $dataFim),
            'top_clientes' => $this->getGraficoTopClientes($dataInicio, $dataFim),
            'localizacao_clientes' => $this->getGraficoLocalizacaoClientes()
        ];

        $tabelas = [
            'lista_clientes' => $this->getListaClientesCompleta(),
            'clientes_inativos' => $this->getClientesInativos($dataInicio, $dataFim)
        ];

        return compact('resumo', 'graficos', 'tabelas');
    }

    private function getDadosParcelas($dataInicio, $dataFim, $agrupamento)
    {
        $resumo = [
            'total_parcelas' => Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])->count(),
            'parcelas_pagas' => Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
                ->where('status', 'paga')->count(),
            'parcelas_vencidas' => Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
                ->where('status', 'pendente')
                ->where('data_vencimento', '<', now())->count(),
            'valor_total_parcelas' => Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])->sum('valor_parcela')
        ];

        $graficos = [
            'parcelas_status' => $this->getGraficoParcelasStatus($dataInicio, $dataFim),
            'parcelas_mensal' => $this->getGraficoParcelasMensal($dataInicio, $dataFim, $agrupamento),
            'vencimento_parcelas' => $this->getGraficoVencimentoParcelas($dataInicio, $dataFim)
        ];

        $tabelas = [
            'parcelas_vencidas' => $this->getParcelasVencidasDetalhadas($dataInicio, $dataFim),
            'parcelas_a_vencer' => $this->getParcelasAVencerDetalhadas($dataInicio, $dataFim)
        ];

        return compact('resumo', 'graficos', 'tabelas');
    }

    // Métodos auxiliares para gráficos
    private function getGraficoStatusPagamento($dataInicio, $dataFim)
    {
        $dados = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->select('status_pagamento', DB::raw('COUNT(*) as total, SUM(valor) as valor_total'))
            ->groupBy('status_pagamento')
            ->get();

        return [
            'labels' => $dados->pluck('status_pagamento')->map(fn($s) => ucfirst($s)),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('valor_total')
        ];
    }

    private function getGraficoEvolucaoMensal($dataInicio, $dataFim, $agrupamento)
    {
        if ($agrupamento === 'semana') {
            $dados = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
                ->select(
                    DB::raw('YEAR(data_servico) as ano'),
                    DB::raw('WEEK(data_servico) as semana'),
                    DB::raw('SUM(valor) as total'),
                    DB::raw('COUNT(*) as quantidade')
                )
                ->groupBy('ano', 'semana')
                ->orderBy('ano')
                ->orderBy('semana')
                ->get();

            $labels = $dados->map(fn($item) => "Sem {$item->semana}/{$item->ano}");
        } else {
            $dados = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
                ->select(
                    DB::raw('YEAR(data_servico) as ano'),
                    DB::raw('MONTH(data_servico) as mes'),
                    DB::raw('SUM(valor) as total'),
                    DB::raw('COUNT(*) as quantidade')
                )
                ->groupBy('ano', 'mes')
                ->orderBy('ano')
                ->orderBy('mes')
                ->get();

            $labels = $dados->map(fn($item) => date('M/Y', mktime(0, 0, 0, $item->mes, 1, $item->ano)));
        }

        return [
            'labels' => $labels,
            'valores' => $dados->pluck('total'),
            'quantidades' => $dados->pluck('quantidade')
        ];
    }

    private function getGraficoTiposPagamento($dataInicio, $dataFim)
    {
        $dados = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->select('tipo_pagamento', DB::raw('COUNT(*) as total, SUM(valor) as valor_total'))
            ->groupBy('tipo_pagamento')
            ->get();

        return [
            'labels' => $dados->pluck('tipo_pagamento')->map(fn($t) => $t === 'avista' ? 'À Vista' : 'Parcelado'),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('valor_total')
        ];
    }

    private function getGraficoServicosMensal($dataInicio, $dataFim, $agrupamento)
    {
        // Similar ao evolucao_mensal mas focado em quantidade de serviços
        return $this->getGraficoEvolucaoMensal($dataInicio, $dataFim, $agrupamento);
    }

    private function getGraficoCategoriasServicos($dataInicio, $dataFim)
    {
        // Agrupa serviços por "categoria" baseada no nome
        $dados = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])
            ->select('nome', DB::raw('COUNT(*) as total, SUM(valor) as valor_total'))
            ->groupBy('nome')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return [
            'labels' => $dados->pluck('nome'),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('valor_total')
        ];
    }

    private function getGraficoStatusServicos($dataInicio, $dataFim)
    {
        return $this->getGraficoStatusPagamento($dataInicio, $dataFim);
    }

    private function getGraficoClientesMensal($dataInicio, $dataFim)
    {
        $dados = Cliente::whereBetween('created_at', [$dataInicio, $dataFim])
            ->select(
                DB::raw('YEAR(created_at) as ano'),
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('ano', 'mes')
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $dados->map(fn($item) => date('M/Y', mktime(0, 0, 0, $item->mes, 1, $item->ano))),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('total')
        ];
    }

    private function getGraficoTopClientes($dataInicio, $dataFim)
    {
        $clientes = Cliente::withSum(['servicos' => function($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
        }], 'valor')
        ->orderBy('servicos_sum_valor', 'desc')
        ->limit(8)
        ->get();

        return [
            'labels' => $clientes->pluck('nome'),
            'quantidades' => $clientes->pluck('servicos_count'),
            'valores' => $clientes->pluck('servicos_sum_valor')
        ];
    }

    private function getGraficoLocalizacaoClientes()
    {
        // Para um sistema real, você teria um campo de cidade/estado
        // Aqui é um exemplo simplificado
        $dados = Cliente::select('celular', DB::raw('COUNT(*) as total'))
            ->groupBy('celular')
            ->orderBy('total', 'desc')
            ->limit(6)
            ->get();

        return [
            'labels' => $dados->pluck('celular')->map(fn($c) => $c ?: 'Não informado'),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('total')
        ];
    }

    private function getGraficoParcelasStatus($dataInicio, $dataFim)
    {
        $dados = Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->select('status', DB::raw('COUNT(*) as total, SUM(valor_parcela) as valor_total'))
            ->groupBy('status')
            ->get();

        return [
            'labels' => $dados->pluck('status')->map(fn($s) => ucfirst($s)),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('valor_total')
        ];
    }

    private function getGraficoParcelasMensal($dataInicio, $dataFim, $agrupamento)
    {
        $dados = Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->select(
                DB::raw('YEAR(data_vencimento) as ano'),
                DB::raw('MONTH(data_vencimento) as mes'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(valor_parcela) as valor_total')
            )
            ->groupBy('ano', 'mes')
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $dados->map(fn($item) => date('M/Y', mktime(0, 0, 0, $item->mes, 1, $item->ano))),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('valor_total')
        ];
    }

    private function getGraficoVencimentoParcelas($dataInicio, $dataFim)
    {
        $dados = Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->where('status', 'pendente')
            ->select(
                DB::raw('DATE(data_vencimento) as data'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(valor_parcela) as valor_total')
            )
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        return [
            'labels' => $dados->map(fn($item) => date('d/m', strtotime($item->data))),
            'quantidades' => $dados->pluck('total'),
            'valores' => $dados->pluck('valor_total')
        ];
    }

    // Métodos auxiliares para resumo
    private function getResumoFinanceiro($dataInicio, $dataFim)
    {
        $servicos = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])->get();

        return [
            'total_pago' => $servicos->where('status_pagamento', 'pago')->sum('valor'),
            'total_pendente' => $servicos->where('status_pagamento', 'pendente')->sum('valor'),
            'total_nao_pago' => $servicos->where('status_pagamento', 'nao_pago')->sum('valor'),
            'total_servicos' => $servicos->count(),
            'ticket_medio' => $servicos->avg('valor'),
            'valor_total' => $servicos->sum('valor')
        ];
    }

    private function getValorMedioPorCliente($dataInicio, $dataFim)
    {
        $totalClientes = Cliente::whereHas('servicos', function($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('data_servico', [$dataInicio, $dataFim]);
        })->count();

        $valorTotal = Servico::whereBetween('data_servico', [$dataInicio, $dataFim])->sum('valor');

        return $totalClientes > 0 ? $valorTotal / $totalClientes : 0;
    }

    // Métodos auxiliares para tabelas
    private function getTopClientes($dataInicio, $dataFim)
    {
        return Cliente::withCount(['servicos' => function($q) use ($dataInicio, $dataFim) {
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

    private function getServicosDetalhados($dataInicio, $dataFim)
    {
        return Servico::with('cliente')
            ->whereBetween('data_servico', [$dataInicio, $dataFim])
            ->orderBy('data_servico', 'desc')
            ->get()
            ->map(function($servico) {
                return [
                    'data_servico' => $servico->data_servico,
                    'cliente_nome' => $servico->cliente->nome,
                    'servico_nome' => $servico->nome,
                    'valor' => $servico->valor,
                    'status_pagamento' => $servico->status_pagamento,
                    'tipo_pagamento' => $servico->tipo_pagamento,
                    'vencimento' => $servico->parcelasServico->first()->data_vencimento ?? null
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
                    'servico' => $parcela->servico->nome,
                    'valor' => $parcela->valor_parcela,
                    'vencimento' => $parcela->data_vencimento,
                    'dias_atraso' => now()->diffInDays($parcela->data_vencimento)
                ];
            });
    }

    private function getServicosRecentes($dataInicio, $dataFim)
    {
        return Servico::with('cliente')
            ->whereBetween('data_servico', [$dataInicio, $dataFim])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function($servico) {
                return [
                    'data' => $servico->data_servico,
                    'cliente' => $servico->cliente->nome,
                    'servico' => $servico->nome,
                    'valor' => $servico->valor,
                    'status' => $servico->status_pagamento
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
            ->orderBy('servicos_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($cliente) {
                return [
                    'nome' => $cliente->nome,
                    'total_servicos' => $cliente->servicos_count,
                    'telefone' => $cliente->celular
                ];
            });
    }

    private function getListaClientesCompleta()
    {
        return Cliente::withCount('servicos')
            ->withSum('servicos', 'valor')
            ->orderBy('nome')
            ->get()
            ->map(function($cliente) {
                return [
                    'nome' => $cliente->nome,
                    'total_servicos' => $cliente->servicos_count,
                    'valor_total' => $cliente->servicos_sum_valor ?? 0,
                    'telefone' => $cliente->celular,
                    'ultimo_servico' => $cliente->servicos->max('data_servico')
                ];
            });
    }

    private function getClientesInativos($dataInicio, $dataFim)
    {
        $limiteInatividade = now()->subMonths(3); // 3 meses sem serviços

        return Cliente::whereDoesntHave('servicos', function($q) use ($limiteInatividade) {
                $q->where('data_servico', '>=', $limiteInatividade);
            })
            ->withCount('servicos')
            ->withMax('servicos', 'data_servico')
            ->get()
            ->map(function($cliente) {
                return [
                    'nome' => $cliente->nome,
                    'total_servicos' => $cliente->servicos_count,
                    'ultimo_servico' => $cliente->servicos_max_data_servico,
                    'telefone' => $cliente->celular
                ];
            });
    }

    private function getParcelasVencidasDetalhadas($dataInicio, $dataFim)
    {
        return $this->getParcelasVencidasRelatorio($dataInicio, $dataFim);
    }

    private function getParcelasAVencerDetalhadas($dataInicio, $dataFim)
    {
        return Parcela::whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->where('status', 'pendente')
            ->where('data_vencimento', '>=', now())
            ->with(['servico.cliente'])
            ->orderBy('data_vencimento')
            ->get()
            ->map(function($parcela) {
                return [
                    'cliente' => $parcela->servico->cliente->nome,
                    'servico' => $parcela->servico->nome,
                    'valor' => $parcela->valor_parcela,
                    'vencimento' => $parcela->data_vencimento,
                    'dias_para_vencer' => now()->diffInDays($parcela->data_vencimento)
                ];
            });
    }

    public function exportarRelatorio(Request $request)
    {
        // Por enquanto, retorna os dados em JSON
        // Em produção, você implementaria geração de PDF/Excel aqui
        return $this->relatoriosDados($request);
    }
}