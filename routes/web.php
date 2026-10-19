<?php

use App\Http\Controllers\CertificateController;
use App\Livewire\Company\EmployeeManager;
use App\Livewire\Instructor\CourseEditor;
use App\Livewire\Instructor\CourseList;
use App\Livewire\Instructor\CurriculumBuilder;
use App\Livewire\Instructor\QuizEditor;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Livewire\Student\LessonPlayer;
use App\Livewire\Student\QuizRunner;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/planos', function () {
    return view('pricing');
})->name('pricing');

// Validação pública de autenticidade de certificados (sem login)
Route::get('/validar-certificado/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->isStudent()) {
            return redirect()->route('student.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');

    // Portal do Aluno / Colaborador
    Route::get('/aluno/dashboard', StudentDashboard::class)->name('student.dashboard');
    Route::get('/aluno/cursos/{course:slug}/aulas/{lesson?}', LessonPlayer::class)->name('student.player');
    Route::get('/aluno/cursos/{course:slug}/certificado', [CertificateController::class, 'download'])->name('student.certificate');
    Route::get('/aluno/quizzes/{quiz}', QuizRunner::class)->name('student.quiz');

    Route::get('/empresa/colaboradores', EmployeeManager::class)
        ->middleware('role:company_admin')
        ->name('employees.index');

    Route::get('/empresa/trilhas', \App\Livewire\Company\TrackManager::class)
        ->middleware('role:company_admin')
        ->name('company.tracks.index');

    // Rotas de Gestão de Conteúdo (Instrutor / RH)
    Route::middleware('role:instructor,company_admin')->prefix('instrutor')->name('instructor.')->group(function () {
        Route::get('/cursos', CourseList::class)->name('courses.index');
        Route::get('/cursos/novo', CourseEditor::class)->name('courses.create');
        Route::get('/cursos/{course}/editar', CourseEditor::class)->name('courses.edit');
        Route::get('/cursos/{course}/curriculo', CurriculumBuilder::class)->name('courses.curriculum');
        Route::get('/cursos/{course}/quizzes/novo', QuizEditor::class)->name('courses.quizzes.create');
        Route::get('/quizzes/{quiz}/editar', QuizEditor::class)->name('quizzes.edit');
    });
});

require __DIR__.'/auth.php';
