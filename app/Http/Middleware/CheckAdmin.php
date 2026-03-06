<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Request  $request
     * @param  \Closure  $next
     * @param  string|null  $permissao  <- Adicionamos este parâmetro
     */
    public function handle(Request $request, Closure $next, string $permissao = null): Response
    {
        // 1. Verifica se está logado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->is_admin) {
            return $next($request);
        }

        if ($permissao) {
            $minhasPermissoes = $user->permissoes ?? [];
            
            if (in_array($permissao, $minhasPermissoes)) {
                return $next($request);
            }
        }

        abort(403, 'Acesso não autorizado. Você não tem permissão para esta ação.');
    }
}