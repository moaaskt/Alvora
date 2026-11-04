<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanTeach
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user has pedagogical authorization to create/edit courses in the given context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // admin_alvora has global pedagogical authority
        if ($user->isAlvoraAdmin()) {
            return $next($request);
        }

        $companyId = $user->company_id;

        if (!$user->canTeachInContext($companyId)) {
            abort(403, 'Acesso restrito ao perfil de instrutor no contexto ativo.');
        }

        return $next($request);
    }
}

