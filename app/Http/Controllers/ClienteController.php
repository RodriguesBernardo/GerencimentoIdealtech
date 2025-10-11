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
                return $query->where('nome', 'like', '%' . $search . '%');
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
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'observacoes' => 'nullable|string'
        ]);

        Cliente::create($request->all());

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
            'cpf_cnpj' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
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

    
}