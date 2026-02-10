<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LessonController;

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Private Routes
Route::middleware('auth')->group(function () {
    // Default Home -> Dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Lessons (Student + Teacher)
    Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
    Route::get('/lessons/{lesson}/telegram', [LessonController::class, 'telegramLink'])->name('lessons.telegram');

    // Teacher/Admin Only - Create/Edit Lessons
    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
        Route::post('/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');

        // Content Management (Segments & Questions)
        Route::get('/lessons/{lesson}/manage', [LessonController::class, 'manage'])->name('lessons.manage');

        Route::post('/lessons/{lesson}/segments', [App\Http\Controllers\Web\SegmentController::class, 'store'])->name('segments.store');
        Route::put('/segments/{segment}', [App\Http\Controllers\Web\SegmentController::class, 'update'])->name('segments.update');
        Route::delete('/segments/{segment}', [App\Http\Controllers\Web\SegmentController::class, 'destroy'])->name('segments.destroy');

        Route::post('/segments/{segment}/questions', [App\Http\Controllers\Web\QuestionController::class, 'store'])->name('questions.store');
        Route::put('/questions/{question}', [App\Http\Controllers\Web\QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [App\Http\Controllers\Web\QuestionController::class, 'destroy'])->name('questions.destroy');
    });
});
