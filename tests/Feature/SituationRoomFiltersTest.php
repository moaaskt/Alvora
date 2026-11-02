<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Company\SituationRoom;
use App\Models\Company;
use App\Models\LearningTrack;
use App\Models\RiskScore;
use App\Models\TrackEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SituationRoomFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_situation_room_filters_by_risk_level(): void
    {
        $company = Company::create(['name' => 'Empresa Teste Risco ' . uniqid(), 'status' => 'active']);
        $admin = User::create([
            'name' => 'RH Gestor',
            'email' => 'rh_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $studentHigh = User::create([
            'name' => 'Aluno Critico',
            'email' => 'critico_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $studentLow = User::create([
            'name' => 'Aluno Saudavel',
            'email' => 'saudavel_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $studentHigh->id,
            'score' => 85,
            'level' => 'high',
            'breakdown' => ['inactivity' => ['score' => 90]],
            'calculated_at' => now(),
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $studentLow->id,
            'score' => 15,
            'level' => 'low',
            'breakdown' => ['inactivity' => ['score' => 10]],
            'calculated_at' => now(),
        ]);

        $this->actingAs($admin);

        // When all, both are in the table students list
        Livewire::test(SituationRoom::class)
            ->assertViewHas('students', fn($list) => $list->contains($studentHigh) && $list->contains($studentLow))
            ->set('filterLevel', 'high')
            ->assertViewHas('students', fn($list) => $list->contains($studentHigh) && !$list->contains($studentLow))
            ->set('filterLevel', 'low')
            ->assertViewHas('students', fn($list) => $list->contains($studentLow) && !$list->contains($studentHigh));
    }

    public function test_situation_room_filters_by_department(): void
    {
        $company = Company::create(['name' => 'Empresa Departamentos ' . uniqid(), 'status' => 'active']);
        $admin = User::create([
            'name' => 'RH Gestor',
            'email' => 'rh_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $dev = User::create([
            'name' => 'Ana Engenharia',
            'email' => 'ana_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
            'department' => 'Engenharia de Software',
        ]);

        $sales = User::create([
            'name' => 'Bruno Vendas',
            'email' => 'bruno_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
            'department' => 'Comercial',
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $dev->id,
            'score' => 60,
            'level' => 'medium',
            'breakdown' => ['inactivity' => ['score' => 50]],
            'calculated_at' => now(),
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $sales->id,
            'score' => 30,
            'level' => 'low',
            'breakdown' => ['inactivity' => ['score' => 20]],
            'calculated_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(SituationRoom::class)
            ->assertSee('Ana Engenharia')
            ->assertSee('Bruno Vendas')
            ->assertSee('Engenharia de Software')
            ->assertSee('Comercial')
            ->set('department', 'Engenharia de Software')
            ->assertViewHas('students', fn($list) => $list->contains($dev) && !$list->contains($sales))
            ->set('department', 'Comercial')
            ->assertViewHas('students', fn($list) => $list->contains($sales) && !$list->contains($dev));
    }

    public function test_situation_room_filters_by_learning_track(): void
    {
        $company = Company::create(['name' => 'Empresa Trilhas ' . uniqid(), 'status' => 'active']);
        $admin = User::create([
            'name' => 'RH Gestor',
            'email' => 'rh_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $trackAI = LearningTrack::create([
            'company_id' => $company->id,
            'title' => 'Formação IA Corporativa',
            'slug' => 'formacao-ia-' . uniqid(),
            'description' => 'Trilha de IA',
        ]);

        $trackLeadership = LearningTrack::create([
            'company_id' => $company->id,
            'title' => 'Liderança Ágil',
            'slug' => 'lideranca-agil-' . uniqid(),
            'description' => 'Trilha de Liderança',
        ]);

        $studentAI = User::create([
            'name' => 'Carlos IA',
            'email' => 'carlos_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $studentLeader = User::create([
            'name' => 'Daniel Lider',
            'email' => 'daniel_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $studentAI->id,
            'track_id' => $trackAI->id,
            'status' => 'active',
            'progress_percent' => 25,
        ]);

        TrackEnrollment::create([
            'company_id' => $company->id,
            'user_id' => $studentLeader->id,
            'track_id' => $trackLeadership->id,
            'status' => 'active',
            'progress_percent' => 50,
        ]);

        $this->actingAs($admin);

        Livewire::test(SituationRoom::class)
            ->assertSee('Carlos IA')
            ->assertSee('Daniel Lider')
            ->set('trackId', $trackAI->id)
            ->assertViewHas('students', fn($list) => $list->contains($studentAI) && !$list->contains($studentLeader))
            ->set('trackId', $trackLeadership->id)
            ->assertViewHas('students', fn($list) => $list->contains($studentLeader) && !$list->contains($studentAI));
    }

    public function test_situation_room_sorts_collaborators(): void
    {
        $company = Company::create(['name' => 'Empresa Ordenacao ' . uniqid(), 'status' => 'active']);
        $admin = User::create([
            'name' => 'RH Gestor',
            'email' => 'rh_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $studentA = User::create([
            'name' => 'Alice Estudante',
            'email' => 'alice_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        $studentZ = User::create([
            'name' => 'Zeca Estudante',
            'email' => 'zeca_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::STUDENT,
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $studentA->id,
            'score' => 20,
            'level' => 'low',
            'breakdown' => ['inactivity' => ['score' => 10]],
            'calculated_at' => now(),
        ]);

        RiskScore::create([
            'company_id' => $company->id,
            'user_id' => $studentZ->id,
            'score' => 95,
            'level' => 'high',
            'breakdown' => ['inactivity' => ['score' => 80]],
            'calculated_at' => now(),
        ]);

        $this->actingAs($admin);

        // By default (risk_desc), Zeca (95) comes before Alice (20)
        Livewire::test(SituationRoom::class)
            ->assertSet('sortBy', 'risk_desc')
            ->assertViewHas('students', function ($list) {
                return $list->first()->name === 'Zeca Estudante' && $list->last()->name === 'Alice Estudante';
            })
            ->set('sortBy', 'risk_asc')
            ->assertViewHas('students', function ($list) {
                return $list->first()->name === 'Alice Estudante' && $list->last()->name === 'Zeca Estudante';
            })
            ->set('sortBy', 'name_asc')
            ->assertViewHas('students', function ($list) {
                return $list->first()->name === 'Alice Estudante' && $list->last()->name === 'Zeca Estudante';
            })
            ->set('sortBy', 'name_desc')
            ->assertViewHas('students', function ($list) {
                return $list->first()->name === 'Zeca Estudante' && $list->last()->name === 'Alice Estudante';
            });
    }

    public function test_clear_filters_resets_all_criteria(): void
    {
        $company = Company::create(['name' => 'Empresa Limpar ' . uniqid(), 'status' => 'active']);
        $admin = User::create([
            'name' => 'RH Gestor',
            'email' => 'rh_' . uniqid() . '@teste.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role' => UserRole::COMPANY_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(SituationRoom::class)
            ->set('filterLevel', 'high')
            ->set('search', 'Maria')
            ->set('department', 'TI')
            ->set('trackId', 42)
            ->set('sortBy', 'name_desc')
            ->assertSet('filterLevel', 'high')
            ->assertSet('search', 'Maria')
            ->assertSet('department', 'TI')
            ->assertSet('trackId', 42)
            ->assertSet('sortBy', 'name_desc')
            ->call('clearFilters')
            ->assertSet('filterLevel', 'all')
            ->assertSet('search', '')
            ->assertSet('department', null)
            ->assertSet('trackId', null)
            ->assertSet('sortBy', 'risk_desc');
    }
}
