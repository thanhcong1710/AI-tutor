<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LessonController;
use App\Http\Controllers\Web\LearningController;
use App\Http\Controllers\Web\StudentController;
use App\Http\Controllers\Web\AiLogController;
use App\Http\Controllers\Web\SegmentController;
use App\Http\Controllers\Web\QuestionController;

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Private Routes
Route::middleware('auth')->group(function () {
    // Default Home -> Dashboard (role-based)
    Route::get('/', function () {
        if (auth()->user()->role === 'student') {
            return redirect()->route('student.dashboard');
        }
        return redirect()->route('dashboard');
    });

    // General Dashboard (Admin/Teacher)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Student Dashboard
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])
        ->name('student.dashboard')
        ->middleware('role:student');

    // Lessons (Student + Teacher)
    Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
    Route::get('/lessons/{lesson}/telegram', [LessonController::class, 'telegramLink'])->name('lessons.telegram');

    // Learning Interface (Student)
    Route::get('/lessons/{lesson}/learn', [LessonController::class, 'learn'])->name('lessons.learn');

    // Learning AJAX Endpoints (Session-based auth)
    Route::get('/learning/segments/{id}', [LearningController::class, 'getSegment'])->name('learning.segment');
    Route::post('/learning/chat', [LearningController::class, 'chat'])->name('learning.chat');
    Route::post('/learning/answer', [LearningController::class, 'submitAnswer'])->name('learning.answer');

    // Teacher/Admin Only - Create/Edit Lessons
    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
        Route::post('/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');

        // Content Management (Segments & Questions)
        Route::get('/lessons/{lesson}/manage', [LessonController::class, 'manage'])->name('lessons.manage');

        Route::post('/lessons/{lesson}/segments', [SegmentController::class, 'store'])->name('segments.store');
        Route::put('/segments/{segment}', [SegmentController::class, 'update'])->name('segments.update');
        Route::delete('/segments/{segment}', [SegmentController::class, 'destroy'])->name('segments.destroy');

        Route::post('/segments/{segment}/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

        // AI Logs & Analytics
        Route::get('/ai-logs', [AiLogController::class, 'index'])->name('ai_logs.index');
    });
});
