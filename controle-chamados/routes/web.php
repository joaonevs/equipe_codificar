<?php

use App\Http\Controllers\ChamadoController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/chamados');

Route::get('/painel', DashboardController::class)->name('dashboard');

Route::resource('chamados', ChamadoController::class);
Route::post('chamados/{chamado}/redistribuir', [ChamadoController::class, 'redistribuir'])
    ->name('chamados.redistribuir');
