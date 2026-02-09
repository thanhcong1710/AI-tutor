<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Lesson\LessonService;
use App\Services\Learning\AnalyticsService;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function __construct(
        protected LessonService $lessonService,
        protected AnalyticsService $analyticsService
    ) {
    }

    /**
     * Upload and create new lesson
     * POST /api/teacher/lessons
     */
    public function uploadLesson(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|in:english,math,logic,science,history',
            'level' => 'required|in:beginner,intermediate,advanced',
            'estimated_duration' => 'nullable|integer|min:5|max:180',
        ]);

        try {
            $lesson = $this->lessonService->createFromFile(
                Auth::id(),
                $request->file('file'),
                $request->only(['title', 'description', 'subject', 'level', 'estimated_duration'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Lesson uploaded successfully. Processing in background...',
                'data' => $lesson,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload lesson',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all lessons for teacher
     * GET /api/teacher/lessons
     */
    public function getLessons(Request $request)
    {
        $lessons = $this->lessonService->getTeacherLessons(
            Auth::id(),
            $request->only(['subject', 'level', 'status'])
        );

        return response()->json([
            'success' => true,
            'data' => $lessons,
        ]);
    }

    /**
     * Get single lesson with details
     * GET /api/teacher/lessons/{id}
     */
    public function getLesson(int $id)
    {
        try {
            $lesson = $this->lessonService->getFullLesson($id);

            // Check ownership
            if ($lesson->teacher_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $lesson,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson not found',
            ], 404);
        }
    }

    /**
     * Update lesson
     * PUT /api/teacher/lessons/{id}
     */
    public function updateLesson(Request $request, int $id)
    {
        $lesson = Lesson::findOrFail($id);

        // Check ownership
        if ($lesson->teacher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'sometimes|in:english,math,logic,science,history',
            'level' => 'sometimes|in:beginner,intermediate,advanced',
        ]);

        $lesson = $this->lessonService->update($lesson, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully',
            'data' => $lesson,
        ]);
    }

    /**
     * Delete lesson
     * DELETE /api/teacher/lessons/{id}
     */
    public function deleteLesson(int $id)
    {
        $lesson = Lesson::findOrFail($id);

        // Check ownership
        if ($lesson->teacher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->lessonService->delete($lesson);

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
        ]);
    }

    /**
     * Assign lesson to student
     * POST /api/teacher/lessons/{id}/assign
     */
    public function assignLesson(Request $request, int $id)
    {
        $lesson = Lesson::findOrFail($id);

        // Check ownership
        if ($lesson->teacher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:users,id',
            'due_date' => 'nullable|date|after:now',
            'teacher_notes' => 'nullable|string',
        ]);

        $assignment = LessonAssignment::create([
            'lesson_id' => $id,
            'teacher_id' => Auth::id(),
            'student_id' => $request->student_id,
            'assigned_at' => now(),
            'due_date' => $request->due_date,
            'teacher_notes' => $request->teacher_notes,
            'status' => 'assigned',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lesson assigned successfully',
            'data' => $assignment,
        ], 201);
    }

    /**
     * Get teacher dashboard
     * GET /api/teacher/dashboard
     */
    public function getDashboard()
    {
        $dashboard = $this->analyticsService->getTeacherDashboard(Auth::id());

        return response()->json([
            'success' => true,
            'data' => $dashboard,
        ]);
    }

    /**
     * Get student progress report
     * GET /api/teacher/students/{id}/progress
     */
    public function getStudentProgress(int $studentId)
    {
        $report = $this->analyticsService->getStudentReport($studentId, Auth::id());

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get lesson performance
     * GET /api/teacher/lessons/{id}/performance
     */
    public function getLessonPerformance(int $id)
    {
        $lesson = Lesson::findOrFail($id);

        // Check ownership
        if ($lesson->teacher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $performance = $this->analyticsService->getLessonPerformance($id);

        return response()->json([
            'success' => true,
            'data' => $performance,
        ]);
    }
}
