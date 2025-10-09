<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct()
    {
        /* $this->middleware('check.admin'); */
    }

    public function index()
    {
        $usuarios = User::withTrashed()
            ->orderBy('name')
            ->paginate(10);
            
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $permissoes = $this->getPermissoesOptions();
        return view('admin.usuarios.create', compact('permissoes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'is_admin' => 'boolean',
            'permissoes' => 'nullable|array',
            'permissoes.*' => Rule::in(array_keys($this->getPermissoesOptions())),
            'telefone' => 'nullable|string|max:20'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->is_admin ?? false,
            'permissoes' => $request->permissoes ?? [],
            'telefone' => $request->telefone
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $usuario)
    {
        // Impedir que usuários não admin editem outros usuários
        if (!$usuario->is_admin && !auth()->user()->is_admin) {
            abort(403, 'Acesso não autorizado.');
        }

        $permissoes = $this->getPermissoesOptions();
        return view('admin.usuarios.edit', compact('usuario', 'permissoes'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|min:6|confirmed',
            'is_admin' => 'boolean',
            'permissoes' => 'nullable|array',
            'permissoes.*' => Rule::in(array_keys($this->getPermissoesOptions())),
            'telefone' => 'nullable|string|max:20'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $request->is_admin ?? false,
            'permissoes' => $request->permissoes ?? [],
            'telefone' => $request->telefone
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $usuario)
    {
        // Não permitir excluir o próprio usuário
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    public function restore($id)
    {
        $usuario = User::withTrashed()->findOrFail($id);
        $usuario->restore();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário restaurado com sucesso!');
    }

    public function forceDelete($id)
    {
        $usuario = User::withTrashed()->findOrFail($id);

        // Não permitir excluir permanentemente o próprio usuário
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'Você não pode excluir permanentemente seu próprio usuário.');
        }

        $usuario->forceDelete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário excluído permanentemente com sucesso!');
    }

    /**
     * Opções de permissões disponíveis
     */
    private function getPermissoesOptions()
    {
        return [
            'clientes.view' => 'Visualizar Clientes',
            'clientes.create' => 'Criar Clientes',
            'clientes.edit' => 'Editar Clientes',
            'clientes.delete' => 'Excluir Clientes',
            'servicos.view' => 'Visualizar Serviços',
            'servicos.create' => 'Criar Serviços',
            'servicos.edit' => 'Editar Serviços',
            'servicos.delete' => 'Excluir Serviços',
            'parcelas.view' => 'Visualizar Parcelas',
            'parcelas.edit' => 'Editar Parcelas',
            'relatorios.view' => 'Visualizar Relatórios',
            'usuarios.view' => 'Visualizar Usuários',
            'usuarios.manage' => 'Gerenciar Usuários',
        ];
    }
}