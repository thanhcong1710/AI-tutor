<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\StudentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);

// Telegram Webhook
Route::post('/telegram/webhook', [App\Http\Controllers\Api\TelegramController::class, 'handleWebhook']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);

    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Teacher routes
    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        // Lesson management
        Route::post('/lessons', [TeacherController::class, 'uploadLesson']);
        Route::get('/lessons', [TeacherController::class, 'getLessons']);
        Route::get('/lessons/{id}', [TeacherController::class, 'getLesson']);
        Route::put('/lessons/{id}', [TeacherController::class, 'updateLesson']);
        Route::delete('/lessons/{id}', [TeacherController::class, 'deleteLesson']);

        // Lesson assignment
        Route::post('/lessons/{id}/assign', [TeacherController::class, 'assignLesson']);

        // Analytics & Reports
        Route::get('/dashboard', [TeacherController::class, 'getDashboard']);
        Route::get('/students/{id}/progress', [TeacherController::class, 'getStudentProgress']);
        Route::get('/lessons/{id}/performance', [TeacherController::class, 'getLessonPerformance']);
    });

    // Student routes
    Route::prefix('student')->middleware('role:student')->group(function () {
        // Assigned lessons
        Route::get('/lessons/assigned', [StudentController::class, 'getAssignedLessons']);

        // Learning sessions
        Route::post('/sessions/start', [StudentController::class, 'startSession']);
        Route::get('/sessions', [StudentController::class, 'getMySessions']);
        Route::get('/sessions/{id}', [StudentController::class, 'getSession']);
        Route::post('/sessions/{id}/answer', [StudentController::class, 'submitAnswer']);
        Route::post('/sessions/{id}/next', [StudentController::class, 'nextSegment']);
        Route::post('/sessions/{id}/complete', [StudentController::class, 'completeSession']);

        // Progress & Analytics
        Route::get('/progress', [StudentController::class, 'getMyProgress']);
        Route::get('/progress/{subject}/{level}', [StudentController::class, 'getSubjectProgress']);
    });
});
