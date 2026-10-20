<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Usuário não autenticado ou Super Admin possui bypass global (D-03)
        if (!$user || $user->isAlvoraAdmin()) {
            return $next($request);
        }

        $company = $user->company;

        if (!$company) {
            return $next($request);
        }

        // Verifica se a empresa está em carência e se o prazo de 5 dias expirou
        if ($company->isOverdue()) {
            $subscription = $company->activeSubscription();
            if ($subscription && $subscription->isGracePeriodExpired()) {
                $company->update(['status' => 'suspended']);
                $subscription->update(['status' => 'suspended']);
            }
        }

        // Caso a empresa esteja suspensa por inadimplência
        if ($company->isSuspended()) {
            // Gestor de RH pode acessar exclusivamente as telas de faturamento para regularização
            if ($user->isCompanyAdmin()) {
                if ($request->is('empresa/faturamento*') || $request->is('logout') || $request->routeIs('company.billing*') || $request->routeIs('logout')) {
                    return $next($request);
                }

                return redirect()->route('company.billing')->with('warning', 'Sua assinatura corporativa está suspensa por pendência financeira. Regularize o plano para restabelecer o acesso total da sua equipe.');
            }

            // Alunos e instrutores têm o acesso aos cursos bloqueado
            if ($request->is('aluno/cursos*') || $request->is('aluno/quizzes*')) {
                return redirect()->route('student.dashboard')->with('error', 'O acesso aos cursos e trilhas da sua organização está temporariamente suspenso para renovação contratual. Contate o setor de RH.');
            }
        }

        return $next($request);
    }
}
