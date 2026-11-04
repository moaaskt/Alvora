<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserContextualPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_manage_company_enforces_company_users_active_membership(): void
    {
        $companyA = Company::create([
            'name' => 'Empresa Alfa',
            'cnpj' => '11.111.111/0001-11',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        $companyB = Company::create([
            'name' => 'Empresa Beta',
            'cnpj' => '22.222.222/0001-22',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        // 1. Usuário com papel global company_admin, sem vínculo ativo em company_users: NÃO pode gerenciar a empresa
        $userWithoutPivot = User::create([
            'name' => 'Gestor Sem Pivot',
            'email' => 'sem-pivot@teste.com',
            'password' => bcrypt('password'),
            'role' => UserRole::COMPANY_ADMIN,
            'company_id' => $companyA->id,
        ]);

        $this->assertFalse(
            $userWithoutPivot->canManageCompany($companyA->id),
            'Papel global company_admin sem vínculo ativo em company_users não pode gerenciar a empresa'
        );

        // Usuário com vínculo ativo em company_users para Empresa A
        $userWithPivot = User::create([
            'name' => 'Gestor Alfa',
            'email' => 'gestor-alfa@teste.com',
            'password' => bcrypt('password'),
            'role' => UserRole::COMPANY_ADMIN,
            'company_id' => $companyA->id,
        ]);

        $userWithPivot->companies()->attach($companyA->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->assertTrue($userWithPivot->canManageCompany($companyA->id));
        $this->assertFalse(
            $userWithPivot->canManageCompany($companyB->id),
            'Gestor da Empresa A não pode ter autorização de gestão na Empresa B'
        );

        // Vínculo inativo não concede autorização
        $userWithPivot->companies()->updateExistingPivot($companyA->id, [
            'status' => 'inactive',
        ]);

        $this->assertFalse(
            $userWithPivot->canManageCompany($companyA->id),
            'Vínculo inativo em company_users deve barrar autorização'
        );
    }

    public function test_can_teach_in_context_differentiates_global_and_corporate_contexts(): void
    {
        $company = Company::create([
            'name' => 'Empresa Didática',
            'cnpj' => '33.333.333/0001-33',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        // 1. Instrutor Global Independente
        $independentInstructor = User::create([
            'name' => 'Instrutor Autônomo',
            'email' => 'autonomo@teste.com',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
            'company_id' => null,
        ]);

        $this->assertTrue($independentInstructor->canTeachInContext(null));
        $this->assertFalse(
            $independentInstructor->canTeachInContext($company->id),
            'Instrutor independente não possui permissão automática em empresa sem vínculo ativo'
        );

        // 2. Aluno global promovido a instrutor dentro da empresa
        $companyInstructor = User::create([
            'name' => 'Instrutor Interno Empresa',
            'email' => 'interno@teste.com',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT, // Papel global de student
            'company_id' => $company->id,
        ]);

        $companyInstructor->companies()->attach($company->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->assertTrue(
            $companyInstructor->canTeachInContext($company->id),
            'Usuário com papel de instrutor em company_users deve poder lecionar no contexto da empresa'
        );
        $this->assertFalse(
            $companyInstructor->canTeachInContext(null),
            'Instrutor interno da empresa não tem autorização para lecionar no contexto independente global'
        );

        // 3. Super Admin possui bypass global explícito nas duas capacidades
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@alvora.test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN_ALVORA,
            'company_id' => null,
        ]);

        $this->assertTrue(
            $admin->canTeachInContext(null),
            'admin_alvora deve possuir bypass global para lecionar no contexto independente'
        );
        $this->assertTrue(
            $admin->canTeachInContext($company->id),
            'admin_alvora deve possuir bypass global para lecionar no contexto corporativo'
        );
        $this->assertTrue(
            $admin->canManageCompany($company->id),
            'admin_alvora deve possuir bypass global para gerenciar qualquer organização'
        );
    }

    public function test_contextual_roles_do_not_bleed_privileges_between_management_and_teaching(): void
    {
        $company = Company::create([
            'name' => 'Empresa Granular',
            'cnpj' => '44.444.444/0001-44',
            'status' => 'active',
            'plan' => 'pro',
            'max_users' => 50,
        ]);

        // 1. Papel contextual company_admin: NÃO pode lecionar automaticamente no contexto corporativo
        $pureManager = User::create([
            'name' => 'Gestor Puro de RH',
            'email' => 'gestor-puro@teste.com',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $company->id,
        ]);

        $pureManager->companies()->attach($company->id, [
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $this->assertTrue(
            $pureManager->canManageCompany($company->id),
            'Usuário com papel company_admin em company_users deve poder gerenciar a empresa'
        );
        $this->assertFalse(
            $pureManager->canTeachInContext($company->id),
            'Papel contextual company_admin NÃO concede permissão automática para lecionar na empresa'
        );
        $this->assertFalse(
            $pureManager->canTeachInContext(null),
            'Papel contextual company_admin NÃO concede permissão para lecionar no contexto global'
        );

        // 2. Papel contextual instructor: NÃO pode gerenciar a empresa automaticamente
        $pureInstructor = User::create([
            'name' => 'Professor Didático Puro',
            'email' => 'professor-puro@teste.com',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
            'company_id' => $company->id,
        ]);

        $pureInstructor->companies()->attach($company->id, [
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->assertTrue(
            $pureInstructor->canTeachInContext($company->id),
            'Usuário com papel instructor em company_users deve poder lecionar na empresa'
        );
        $this->assertFalse(
            $pureInstructor->canManageCompany($company->id),
            'Papel contextual instructor NÃO concede permissão para gerenciar a empresa'
        );
    }
}
