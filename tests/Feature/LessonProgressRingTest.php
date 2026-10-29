<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Student\LessonPlayer;
use App\Models\Company;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class LessonProgressRingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_lesson_player_tracks_and_persists_progress_percentage(): void
    {
        $company = Company::create(['name' => 'Acme Test Corp', 'status' => 'active']);
        $student = User::create([
            'company_id' => $company->id,
            'name' => 'Aluno Player',
            'email' => 'alunoplayer_' . uniqid() . '@acme.com',
            'password' => bcrypt('password'),
            'role' => UserRole::STUDENT,
        ]);

        $instructor = User::create([
            'company_id' => $company->id,
            'name' => 'Instrutor Teste',
            'email' => 'instructor_' . uniqid() . '@acme.com',
            'password' => bcrypt('password'),
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::create([
            'company_id' => $company->id,
            'instructor_id' => $instructor->id,
            'title' => 'Curso de Player Dinâmico',
            'slug' => 'curso-player-dinamico-' . uniqid(),
            'status' => 'published',
        ]);

        $module = Module::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'title' => 'Módulo 1: Introdução',
            'order' => 1,
        ]);

        $lesson = Lesson::create([
            'company_id' => $company->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Aula 01: O Papel do Líder',
            'duration_minutes' => 10,
            'video_url' => 'https://example.com/video.mp4',
            'order' => 1,
        ]);

        Enrollment::create([
            'company_id' => $company->id,
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 0,
            'enrolled_at' => now(),
        ]);

        // 1. Initial player has 0% progress
        $player = Livewire::actingAs($student)
            ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson]);

        $player->assertSet('currentLessonPercent', 0);

        // 2. Report 50% watched (300 seconds)
        $player->call('updateLessonProgress', 300, 50);
        $player->assertSet('currentLessonPercent', 50);

        $progressRecord = LessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson->id)->first();
        $this->assertNotNull($progressRecord);
        $this->assertEquals(300, $progressRecord->last_watched_second);
        $this->assertNull($progressRecord->completed_at);

        // 3. Report 95% watched -> auto completion
        $player->call('updateLessonProgress', 570, 95);
        $player->assertSet('isCompleted', true);

        $progressRecord->refresh();
        $this->assertNotNull($progressRecord->completed_at);
    }
}

