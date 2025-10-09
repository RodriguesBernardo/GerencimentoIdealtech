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

    // Mantém o método relatorios para admin...
    public function relatorios()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado.');
        }

        $servicosPorMes = Servico::select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(valor) as valor_total')
            )
            ->whereYear('created_at', now()->year)
            ->groupBy('mes')
            ->get();

        $topClientes = Cliente::withCount('servicos')
            ->orderBy('servicos_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.relatorios', compact('servicosPorMes', 'topClientes'));
    }
}