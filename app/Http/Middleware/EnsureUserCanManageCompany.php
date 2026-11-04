<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageCompany
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user has management authority over the active company context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // admin_alvora has global oversight authority
        if ($user->isAlvoraAdmin()) {
            return $next($request);
        }

        $companyId = $user->company_id;

        if (!$companyId || !$user->canManageCompany($companyId)) {
            abort(403, 'Acesso restrito ao gestor autorizado da organização.');
        }

        return $next($request);
    }
}

