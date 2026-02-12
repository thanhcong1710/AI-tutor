<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Learning\SessionService;
use App\Services\Learning\ProgressTracker;
use App\Models\LessonAssignment;
use App\Models\LearningSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function __construct(
        protected SessionService $sessionService,
        protected ProgressTracker $progressTracker
    ) {
    }

    /**
     * Get assigned lessons
     * GET /api/student/lessons/assigned
     */
    public function getAssignedLessons()
    {
        $assignments = LessonAssignment::where('student_id', Auth::id())
            ->with(['lesson', 'teacher:id,name'])
            ->latest()
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'lesson' => [
                        'id' => $assignment->lesson->id,
                        'title' => $assignment->lesson->title,
                        'subject' => $assignment->lesson->subject,
                        'level' => $assignment->lesson->level,
                        'estimated_duration' => $assignment->lesson->estimated_duration,
                    ],
                    'teacher_name' => $assignment->teacher->name,
                    'status' => $assignment->status,
                    'assigned_at' => $assignment->assigned_at,
                    'due_date' => $assignment->due_date,
                    'teacher_notes' => $assignment->teacher_notes,
                    'is_overdue' => $assignment->isOverdue(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    /**
     * Start learning session
     * POST /api/student/sessions/start
     */
    public function startSession(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'platform' => 'required|in:telegram,discord,web,mobile',
        ]);

        try {
            $session = $this->sessionService->startSession(
                Auth::id(),
                $request->lesson_id,
                $request->platform
            );

            // Get first segment
            $currentSegment = $this->sessionService->getCurrentSegment($session);

            return response()->json([
                'success' => true,
                'message' => 'Session started successfully',
                'data' => [
                    'session' => $session,
                    'current_segment' => $currentSegment,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get session details
     * GET /api/student/sessions/{id}
     */
    public function getSession(int $id)
    {
        $session = LearningSession::with(['lesson', 'answers'])->findOrFail($id);

        // Check ownership
        if ($session->student_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $currentSegment = $this->sessionService->getCurrentSegment($session);

        return response()->json([
            'success' => true,
            'data' => [
                'session' => $session,
                'current_segment' => $currentSegment,
            ],
        ]);
    }

    /**
     * Submit answer to question
     * POST /api/student/sessions/{id}/answer
     */
    public function submitAnswer(Request $request, int $id)
    {
        $session = LearningSession::findOrFail($id);

        // Check ownership
        if ($session->student_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'question_id' => 'required|exists:lesson_questions,id',
            'answer' => 'required|string',
            'audio_url' => 'nullable|url',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        try {
            $studentAnswer = $this->sessionService->submitAnswer(
                $session,
                $request->question_id,
                $request->answer,
                $request->audio_url,
                $request->time_spent ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
                'data' => [
                    'is_correct' => $studentAnswer->is_correct,
                    'points_earned' => $studentAnswer->points_earned,
                    'feedback' => $studentAnswer->ai_feedback,
                    'evaluation' => $studentAnswer->ai_evaluation,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move to next segment
     * POST /api/student/sessions/{id}/next
     */
    public function nextSegment(int $id)
    {
        $session = LearningSession::findOrFail($id);

        // Check ownership
        if ($session->student_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $nextSegmentId = $this->sessionService->nextSegment($session);

        if ($nextSegmentId) {
            $nextSegment = $this->sessionService->getCurrentSegment($session->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Moved to next segment',
                'data' => [
                    'session' => $session->fresh(),
                    'next_segment' => $nextSegment,
                ],
            ]);
        } else {
            // Session completed
            return response()->json([
                'success' => true,
                'message' => 'Session completed!',
                'data' => [
                    'session' => $session->fresh(),
                    'summary' => $this->sessionService->getSessionSummary($session->fresh()),
                ],
            ]);
        }
    }

    /**
     * Complete session
     * POST /api/student/sessions/{id}/complete
     */
    public function completeSession(int $id)
    {
        $session = LearningSession::findOrFail($id);

        // Check ownership
        if ($session->student_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $session = $this->sessionService->completeSession($session);
        $summary = $this->sessionService->getSessionSummary($session);

        return response()->json([
            'success' => true,
            'message' => 'Session completed successfully!',
            'data' => $summary,
        ]);
    }

    /**
     * Get my progress
     * GET /api/student/progress
     */
    public function getMyProgress()
    {
        $progress = $this->progressTracker->getProgressSummary(Auth::id());

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Get progress for specific subject
     * GET /api/student/progress/{subject}/{level}
     */
    public function getSubjectProgress(string $subject, string $level)
    {
        $progress = $this->progressTracker->getSubjectProgress(
            Auth::id(),
            $subject,
            $level
        );

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Get my learning sessions
     * GET /api/student/sessions
     */
    public function getMySessions(Request $request)
    {
        $query = LearningSession::where('student_id', Auth::id())
            ->with('lesson:id,title,subject,level');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sessions = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Get segment details
     * GET /api/student/segments/{id}
     */
    public function getSegment(int $id)
    {
        $segment = \App\Models\LessonSegment::with(['questions', 'lesson'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'segment' => [
                    'id' => $segment->id,
                    'order' => $segment->order,
                    'content' => $segment->content,
                    'ai_explanation' => $segment->ai_explanation,
                    'audio_url' => $segment->audio_url,
                    'questions' => $segment->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'question_text' => $q->question_text,
                            'type' => $q->type,
                            'options' => $q->options,
                            'difficulty' => $q->difficulty,
                        ];
                    }),
                ],
            ],
        ]);
    }

    /**
     * Chat with AI about lesson content
     * POST /api/student/chat
     */
    public function chatWithAI(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'lesson_id' => 'required|exists:lessons,id',
            'segment_id' => 'nullable|exists:lesson_segments,id',
        ]);

        try {
            $lesson = \App\Models\Lesson::findOrFail($request->lesson_id);
            $segment = $request->segment_id
                ? \App\Models\LessonSegment::findOrFail($request->segment_id)
                : null;

            // Build context for AI
            $context = "Bài học: {$lesson->title}\n";
            $context .= "Môn học: {$lesson->subject}\n";
            $context .= "Cấp độ: {$lesson->level}\n\n";

            if ($segment) {
                $context .= "Nội dung phần học hiện tại:\n";
                $context .= $segment->ai_explanation ?? $segment->content;
                $context .= "\n\n";
            }

            $context .= "Câu hỏi của học sinh: {$request->message}\n\n";
            $context .= "Hãy trả lời câu hỏi một cách rõ ràng, dễ hiểu và khuyến khích học sinh. Sử dụng ví dụ cụ thể nếu cần.";

            // Call LLM Service
            $llmService = app(\App\Services\AI\LLMService::class);
            $response = $llmService->chat($context);

            return response()->json([
                'success' => true,
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối với AI. Vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
