<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Verificação manual de autorização
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado. Apenas administradores podem visualizar logs.');
        }

        $logs = SystemLog::with('user')
            ->when($request->action, function ($query, $action) {
                return $query->where('action', $action);
            })
            ->when($request->model_type, function ($query, $modelType) {
                return $query->where('model_type', 'like', "%{$modelType}%");
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($request->date_start, function ($query, $dateStart) use ($request) {
                $dateEnd = $request->date_end ?? now()->format('Y-m-d');
                return $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $users = User::all();
        $modelTypes = SystemLog::distinct('model_type')->pluck('model_type');
        $actions = ['created', 'updated', 'deleted', 'restored'];

        return view('admin.logs.index', compact('logs', 'users', 'modelTypes', 'actions'));
    }

    public function show(SystemLog $log)
    {
        // Verificação manual de autorização
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado. Apenas administradores podem visualizar logs.');
        }

        return view('admin.logs.show', compact('log'));
    }

    public function export(Request $request)
    {
        // Verificação manual de autorização
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado. Apenas administradores podem exportar logs.');
        }

        $logs = SystemLog::with('user')
            ->when($request->action, function ($query, $action) {
                return $query->where('action', $action);
            })
            ->when($request->model_type, function ($query, $modelType) {
                return $query->where('model_type', 'like', "%{$modelType}%");
            })
            ->when($request->date_start, function ($query, $dateStart) use ($request) {
                $dateEnd = $request->date_end ?? now()->format('Y-m-d');
                return $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            
            // Cabeçalho
            fputcsv($handle, ['Data', 'Usuário', 'Ação', 'Modelo', 'ID', 'Descrição', 'IP']);
            
            // Dados
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->user->name,
                    $log->action_formatted, // Use o acessor formatado
                    $log->model_name, // Use o acessor do nome do modelo
                    $log->model_id,
                    $log->description,
                    $log->ip_address,
                ]);
            }
            
            fclose($handle);
        }, 'logs-sistema-' . now()->format('d-m-Y') . '.csv');
    }
}