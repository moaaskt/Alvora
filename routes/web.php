<?php

use App\Livewire\Company\EmployeeManager;
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
});

require __DIR__.'/auth.php';
