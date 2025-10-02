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
        
        // Calcular valores - considerar permissões
        $usuario = auth()->user();
        $valorTotalMes = $usuario->podeVerValoresCompletos() 
            ? Servico::whereMonth('created_at', now()->month)->sum('valor') 
            : null;
            
        $valorPendente = $usuario->podeVerValoresCompletos()
            ? Servico::where('status', '!=', 'Pago')->sum('valor')
            : null;

        // Gráfico de serviços por status
        $servicosPorStatus = Servico::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Serviços recentes
        $servicosRecentes = Servico::with('cliente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalClientes',
            'totalServicos',
            'servicosMes',
            'valorTotalMes',
            'valorPendente',
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