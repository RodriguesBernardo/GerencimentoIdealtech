<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servico;
use App\Models\Cliente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalClientes = Cliente::count();
        $totalServicos = Servico::count();
        $servicosMes = Servico::whereMonth('created_at', now()->month)->count();
        $valorTotalMes = Servico::whereMonth('created_at', now()->month)->sum('valor');
        
        $servicosPorStatus = [
            'pago' => Servico::where('status_pagamento', 'pago')->count(),
            'pendente' => Servico::where('status_pagamento', 'pendente')->count(),
            'nao_pago' => Servico::where('status_pagamento', 'nao_pago')->count()
        ];
        
        $servicosRecentes = Servico::with('cliente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalClientes',
            'totalServicos', 
            'servicosMes',
            'valorTotalMes',
            'servicosPorStatus',
            'servicosRecentes'
        ));
    }

    public function relatorios()
    {
        // Apenas administradores podem acessar
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado.');
        }

        // Dados para relatórios
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