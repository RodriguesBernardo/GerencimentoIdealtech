<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $clientes = Cliente::withCount('servicos')
            ->when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', '%' . $search . '%')
                      ->orWhere('cpf_cnpj', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('celular', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('nome')
            ->paginate(10);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20|unique:clientes,cpf_cnpj',
            'email' => 'nullable|email|max:255',
            'celular' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'uf' => 'nullable|string|max:2',
            'observacoes' => 'nullable|string',
        ]);

        Cliente::create($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $servicos = $cliente->servicos()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('clientes.show', compact('cliente', 'servicos'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20|unique:clientes,cpf_cnpj,' . $cliente->id,
            'email' => 'nullable|email|max:255',
            'celular' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'uf' => 'nullable|string|max:2',
            'observacoes' => 'nullable|string',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->servicos()->count() > 0) {
            return redirect()->route('clientes.index')
                ->with('error', 'Não é possível excluir o cliente pois existem serviços vinculados a ele.');
        }

        $cliente->delete();
        
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }

    public function searchAjax(Request $request)
    {
        $search = $request->get('search');
        
        $clientes = Cliente::when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', '%' . $search . '%')
                      ->orWhere('cpf_cnpj', 'like', '%' . $search . '%')
                      ->orWhere('celular', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('nome')
            ->paginate(10);

        // Formatar a resposta para o Select2
        $formattedClientes = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'cpf_cnpj' => $cliente->cpf_cnpj,
                'celular' => $cliente->celular,
                'email' => $cliente->email,
                'text' => $cliente->nome . ($cliente->cpf_cnpj ? ' - ' . $cliente->cpf_cnpj : '') . ($cliente->email ? ' (' . $cliente->email . ')' : '')
            ];
        });

        return response()->json([
            'data' => $formattedClientes,
            'total' => $clientes->total(),
            'more' => $clientes->hasMorePages()
        ]);
    }

    /**
     * Buscar dados por CNPJ via API
     */
    public function buscarCnpj($cnpj)
    {
        try {
            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
            
            if (strlen($cnpj) !== 14) {
                return response()->json(['error' => 'CNPJ inválido'], 400);
            }

            $response = Http::timeout(30)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json(['error' => 'CNPJ não encontrado'], 404);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar CNPJ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar dados por CEP via API
     */
    public function buscarCep($cep)
    {
        try {
            $cep = preg_replace('/[^0-9]/', '', $cep);
            
            if (strlen($cep) !== 8) {
                return response()->json(['error' => 'CEP inválido'], 400);
            }

            $response = Http::timeout(30)->get("https://viacep.com.br/ws/{$cep}/json/");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['erro'])) {
                    return response()->json(['error' => 'CEP não encontrado'], 404);
                }
                
                return response()->json($data);
            }
            
            return response()->json(['error' => 'CEP não encontrado'], 404);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar CEP: ' . $e->getMessage()], 500);
        }
    }
}