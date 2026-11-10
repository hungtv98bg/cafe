<?php

use App\Http\Controllers\CafeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CafeController::class, 'index'])->name('cafes.index');
Route::get('/api/cafes/nearby', [CafeController::class, 'nearby'])
    ->name('cafes.nearby');
