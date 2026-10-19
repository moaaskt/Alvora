<?php

use App\Livewire\Company\EmployeeManager;
use App\Livewire\Instructor\CourseEditor;
use App\Livewire\Instructor\CourseList;
use App\Livewire\Instructor\CurriculumBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/planos', function () {
    return view('pricing');
})->name('pricing');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/empresa/colaboradores', EmployeeManager::class)
        ->middleware('role:company_admin')
        ->name('employees.index');

    // Rotas de Gestão de Conteúdo (Instrutor / RH)
    Route::middleware('role:instructor,company_admin')->prefix('instrutor')->name('instructor.')->group(function () {
        Route::get('/cursos', CourseList::class)->name('courses.index');
        Route::get('/cursos/novo', CourseEditor::class)->name('courses.create');
        Route::get('/cursos/{course}/editar', CourseEditor::class)->name('courses.edit');
        Route::get('/cursos/{course}/curriculo', CurriculumBuilder::class)->name('courses.curriculum');
    });
});

require __DIR__.'/auth.php';
