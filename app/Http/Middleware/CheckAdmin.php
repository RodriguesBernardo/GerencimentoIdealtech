<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next, $permissaoExigida = null): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->is_admin) {
            return $next($request);
        }

        $permissoes = $user->permissoes ?? [];
        if (is_string($permissoes)) {
            $permissoes = json_decode($permissoes, true) ?? [];
        }

        if ($permissaoExigida && in_array($permissaoExigida, $permissoes)) {
            return $next($request);
        }

        if (!$permissaoExigida && !empty($permissoes)) {
            return $next($request);
        }

        abort(403, 'Acesso não autorizado. Você não tem permissão para esta ação.');
    }
}