<?php

namespace App\Services\Learning;

use App\Models\LearningSession;
use App\Models\Lesson;
use App\Models\User;
use App\Models\StudentAnswer;
use App\Services\AI\LLMService;
use App\Services\AI\STTService;

class SessionService
{
    public function __construct(
        protected LLMService $llmService,
        protected STTService $sttService,
        protected ProgressTracker $progressTracker
    ) {}

    /**
     * Start a new learning session
     */
    public function startSession(
        int $studentId,
        int $lessonId,
        string $platform = 'web'
    ): LearningSession {
        $lesson = Lesson::with('segments.questions')->findOrFail($lessonId);

        if (!$lesson->isReady()) {
            throw new \Exception('Lesson is not ready yet');
        }

        // Count total segments and questions
        $totalSegments = $lesson->segments->count();
        $totalQuestions = $lesson->segments->sum(fn($s) => $s->questions->count());
        $totalPoints = $lesson->segments->sum(fn($s) => 
            $s->questions->sum('points')
        );

        return LearningSession::create([
            'student_id' => $studentId,
            'lesson_id' => $lessonId,
            'platform' => $platform,
            'status' => 'in_progress',
            'current_segment_id' => $lesson->segments->first()->id ?? null,
            'total_segments' => $totalSegments,
            'completed_segments' => 0,
            'total_questions' => $totalQuestions,
            'correct_answers' => 0,
            'total_points' => $totalPoints,
            'earned_points' => 0,
            'duration_seconds' => 0,
            'started_at' => now(),
        ]);
    }

    /**
     * Get current segment for session
     */
    public function getCurrentSegment(LearningSession $session)
    {
        return $session->lesson
            ->segments()
            ->with('questions')
            ->find($session->current_segment_id);
    }

    /**
     * Submit answer to question
     */
    public function submitAnswer(
        LearningSession $session,
        int $questionId,
        string $answer,
        ?string $audioUrl = null,
        int $timeSpent = 0
    ): StudentAnswer {
        $question = \App\Models\LessonQuestion::findOrFail($questionId);

        // Transcribe audio if provided
        if ($audioUrl) {
            $transcribed = $this->sttService->transcribeStudentAnswer($audioUrl);
            $answer = $transcribed ?: $answer;
        }

        // Evaluate answer using AI
        $evaluation = $this->llmService->evaluateAnswer(
            $question->question,
            $question->correct_answer,
            $answer
        );

        // Create answer record
        $studentAnswer = StudentAnswer::create([
            'session_id' => $session->id,
            'question_id' => $questionId,
            'student_answer' => $answer,
            'answer_audio_url' => $audioUrl,
            'is_correct' => $evaluation['is_correct'],
            'points_earned' => $evaluation['is_correct'] ? $question->points : 0,
            'ai_feedback' => $evaluation['feedback'],
            'ai_evaluation' => $evaluation,
            'attempt_number' => 1,
            'time_spent_seconds' => $timeSpent,
        ]);

        // Update session stats
        if ($evaluation['is_correct']) {
            $session->increment('correct_answers');
            $session->increment('earned_points', $question->points);
        }

        $session->increment('duration_seconds', $timeSpent);
        $session->updateProgress();

        // Update progress tracker
        $this->progressTracker->updateFromAnswer($studentAnswer);

        return $studentAnswer;
    }

    /**
     * Move to next segment
     */
    public function nextSegment(LearningSession $session): ?int
    {
        $currentSegment = $this->getCurrentSegment($session);
        
        if (!$currentSegment) {
            return null;
        }

        // Mark current segment as completed
        $session->increment('completed_segments');

        // Get next segment
        $nextSegment = $session->lesson
            ->segments()
            ->where('order', '>', $currentSegment->order)
            ->orderBy('order')
            ->first();

        if ($nextSegment) {
            $session->update(['current_segment_id' => $nextSegment->id]);
            $session->updateProgress();
            return $nextSegment->id;
        }

        // No more segments, complete session
        $this->completeSession($session);
        return null;
    }

    /**
     * Complete learning session
     */
    public function completeSession(LearningSession $session): LearningSession
    {
        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_percentage' => 100,
        ]);

        $session->updateProgress();

        // Update analytics
        $this->progressTracker->updateFromSession($session);

        return $session->fresh();
    }

    /**
     * Abandon session
     */
    public function abandonSession(LearningSession $session): LearningSession
    {
        $session->update([
            'status' => 'abandoned',
        ]);

        return $session->fresh();
    }

    /**
     * Get session summary
     */
    public function getSessionSummary(LearningSession $session): array
    {
        $session->load(['lesson', 'answers.question']);

        return [
            'session_id' => $session->id,
            'lesson_title' => $session->lesson->title,
            'status' => $session->status,
            'completion_percentage' => $session->completion_percentage,
            'score_percentage' => $session->score_percentage,
            'total_questions' => $session->total_questions,
            'correct_answers' => $session->correct_answers,
            'earned_points' => $session->earned_points,
            'total_points' => $session->total_points,
            'duration_minutes' => round($session->duration_seconds / 60, 1),
            'started_at' => $session->started_at,
            'completed_at' => $session->completed_at,
            'answers' => $session->answers->map(function($answer) {
                return [
                    'question' => $answer->question->question,
                    'student_answer' => $answer->student_answer,
                    'is_correct' => $answer->is_correct,
                    'feedback' => $answer->ai_feedback,
                    'points_earned' => $answer->points_earned,
                ];
            }),
        ];
    }
}
