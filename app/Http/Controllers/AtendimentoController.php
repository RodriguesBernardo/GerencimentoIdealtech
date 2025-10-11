<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class AtendimentoController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        $funcionarios = User::all();
        
        return view('calendario.index', compact('clientes', 'funcionarios'));
    }

    public function getEvents(Request $request)
    {
        \Log::info('=== getEvents METHOD CALLED ===');
        \Log::info('Request URL: ' . $request->fullUrl());
        \Log::info('Request Data:', $request->all());
        
        try {
            $query = Atendimento::with(['cliente', 'user']);
            
            $totalAtendimentos = Atendimento::count();
            \Log::info('Total de atendimentos no banco: ' . $totalAtendimentos);
            
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
                \Log::info('Filtrando por status: ' . $request->status);
            }
            
            if ($request->has('user_id') && $request->user_id != '') {
                $query->where('user_id', $request->user_id);
                \Log::info('Filtrando por user_id: ' . $request->user_id);
            }
            
            $atendimentos = $query->get();
            \Log::info('Atendimentos encontrados após filtros: ' . $atendimentos->count());
            
            $events = [];
            foreach ($atendimentos as $atendimento) {
                // Debug dos relacionamentos
                $clienteNome = $atendimento->cliente ? $atendimento->cliente->nome : 'CLIENTE_NULL';
                $userName = $atendimento->user ? $atendimento->user->name : 'USER_NULL';
                
                \Log::info("Atendimento ID {$atendimento->id}: {$atendimento->titulo} - Cliente: {$clienteNome} - User: {$userName}");
                
                $events[] = [
                    'id' => $atendimento->id,
                    'title' => $atendimento->titulo . ' - ' . $clienteNome,
                    'start' => $atendimento->data_inicio,
                    'end' => $atendimento->data_fim,
                    'color' => $atendimento->cor,
                    'extendedProps' => [
                        'status' => $atendimento->status,
                        'cliente' => $clienteNome,
                        'responsavel' => $userName
                    ]
                ];
            }
            
            \Log::info('Total de eventos formatados: ' . count($events));
            \Log::info('Events JSON: ' . json_encode($events));
            
            return response()->json($events);
            
        } catch (\Exception $e) {
            \Log::error('ERRO em getEvents: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        
    public function edit(Atendimento $atendimento)
    {
        $atendimento->load(['cliente', 'user']);
        
        return response()->json([
            'atendimento' => $atendimento
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after:data_inicio'
        ]);

        $atendimento = Atendimento::create($request->all());
        
        // Carregar os relacionamentos
        $atendimento->load(['cliente', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Atendimento agendado com sucesso!',
            'atendimento' => $atendimento,
            'cliente' => $atendimento->cliente,
            'responsavel' => $atendimento->user
        ]);
    }

    public function show(Atendimento $atendimento)
    {
        $atendimento->load(['cliente', 'user']);
        
        return response()->json([
            'atendimento' => $atendimento,
            'cliente' => $atendimento->cliente,
            'responsavel' => $atendimento->user
        ]);
    }

    public function update(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after:data_inicio'
        ]);

        $atendimento->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Atendimento atualizado com sucesso!'
        ]);
    }

    public function destroy(Atendimento $atendimento)
    {
        $atendimento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Atendimento excluído com sucesso!'
        ]);
    }
}