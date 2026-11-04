<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super Admin (admin_alvora) has full global governance over all tenants.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAlvoraAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can manage company employees / invitations.
     */
    public function manageEmployees(User $user, Company $company): bool
    {
        return $user->canManageCompany($company->id);
    }

    /**
     * Determine whether the user can manage corporate learning tracks.
     */
    public function manageTracks(User $user, Company $company): bool
    {
        return $user->canManageCompany($company->id);
    }

    /**
     * Determine whether the user can view/manage corporate billing & plans.
     */
    public function manageBilling(User $user, Company $company): bool
    {
        return $user->canManageCompany($company->id);
    }

    /**
     * Determine whether the user can access the Situation Room (Sala de Situação).
     */
    public function viewSituationRoom(User $user, Company $company): bool
    {
        return $user->canManageCompany($company->id);
    }

    /**
     * Determine whether the user can view AI Executive Insights.
     */
    public function viewAiInsights(User $user, Company $company): bool
    {
        return $user->canManageCompany($company->id);
    }
}
