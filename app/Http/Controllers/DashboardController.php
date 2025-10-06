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
        // Estatísticas principais
        $totalClientes = Cliente::count();
        $totalServicos = Servico::count();
        
        // Serviços do mês atual
        $servicosMes = Servico::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $valorTotalMes = Servico::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('valor');

        // Valores pendentes e recebidos
        $valorPendente = Servico::where('status_pagamento', 'pendente')->sum('valor');
        $valorRecebido = Servico::where('status_pagamento', 'pago')->sum('valor');
        
        // Serviços por status
        $servicosPorStatus = [
            'pago' => Servico::where('status_pagamento', 'pago')->count(),
            'pendente' => Servico::where('status_pagamento', 'pendente')->count(),
            'nao_pago' => Servico::where('status_pagamento', 'nao_pago')->count()
        ];

        // Serviços recentes
        $servicosRecentes = Servico::with('cliente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Evolução mensal dos serviços (últimos 6 meses)
        $evolucaoMensal = $this->getEvolucaoMensal();

        // Distribuição por tipo de pagamento
        $distribuicaoTipoPagamento = $this->getDistribuicaoTipoPagamento();

        // Top clientes
        $topClientes = $this->getTopClientes();

        // Parcelas pendentes
        $parcelasPendentes = $this->getParcelasPendentes();

        return view('dashboard', compact(
            'totalClientes',
            'totalServicos', 
            'servicosMes',
            'valorTotalMes',
            'valorPendente',
            'valorRecebido',
            'servicosPorStatus',
            'servicosRecentes',
            'evolucaoMensal',
            'distribuicaoTipoPagamento',
            'topClientes',
            'parcelasPendentes'
        ));
    }

    private function getEvolucaoMensal()
    {
        $data = [];
        $meses = [];
        $valores = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $mesFormatado = $mes->format('M/Y');
            
            $total = Servico::whereMonth('created_at', $mes->month)
                ->whereYear('created_at', $mes->year)
                ->count();

            $meses[] = $mesFormatado;
            $valores[] = $total;
        }

        return [
            'meses' => $meses,
            'valores' => $valores
        ];
    }

    private function getDistribuicaoTipoPagamento()
    {
        return [
            'avista' => Servico::where('tipo_pagamento', 'avista')->count(),
            'parcelado' => Servico::where('tipo_pagamento', 'parcelado')->count()
        ];
    }

    private function getTopClientes()
    {
        return Cliente::withCount(['servicos as total_servicos'])
            ->withSum('servicos', 'valor')
            ->orderBy('total_servicos', 'desc')
            ->take(5)
            ->get();
    }

    private function getParcelasPendentes()
    {
        return Parcela::where('status', 'pendente')
            ->where('data_vencimento', '<=', now()->addDays(7))
            ->with('servico.cliente')
            ->orderBy('data_vencimento')
            ->take(5)
            ->get();
    }

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