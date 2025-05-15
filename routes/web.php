<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CategoryController;


Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::resource('tasks', TaskController::class);
    Route::resource('categories', CategoryController::class);
    Route::patch('tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus'])
        ->name('tasks.toggle-status');
});
