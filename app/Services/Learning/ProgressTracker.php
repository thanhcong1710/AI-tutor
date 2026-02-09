<?php

namespace App\Services\Learning;

use App\Models\LearningAnalytics;
use App\Models\LearningSession;
use App\Models\StudentAnswer;
use App\Models\User;

class ProgressTracker
{
    /**
     * Update analytics from student answer
     */
    public function updateFromAnswer(StudentAnswer $answer): void
    {
        $session = $answer->session;
        $lesson = $session->lesson;

        $analytics = $this->getOrCreateAnalytics(
            $session->student_id,
            $lesson->subject,
            $lesson->level
        );

        // Update question stats
        $analytics->increment('total_questions_answered');
        
        if ($answer->is_correct) {
            $analytics->increment('correct_answers');
        }

        // Recalculate average score
        $analytics->calculateAverageScore();

        // Update streak
        $analytics->updateStreak();

        $analytics->save();
    }

    /**
     * Update analytics from completed session
     */
    public function updateFromSession(LearningSession $session): void
    {
        $lesson = $session->lesson;

        $analytics = $this->getOrCreateAnalytics(
            $session->student_id,
            $lesson->subject,
            $lesson->level
        );

        // Update session stats
        $analytics->increment('total_sessions');
        
        if ($session->isCompleted()) {
            $analytics->increment('completed_sessions');
        }

        // Update time spent
        $analytics->increment('total_time_spent_minutes', 
            round($session->duration_seconds / 60)
        );

        // Update streak
        $analytics->updateStreak();

        $analytics->save();

        // Analyze performance and update strengths/weaknesses
        $this->analyzePerformance($session->student_id, $lesson->subject, $lesson->level);
    }

    /**
     * Get or create analytics record
     */
    protected function getOrCreateAnalytics(
        int $studentId,
        string $subject,
        string $level
    ): LearningAnalytics {
        return LearningAnalytics::firstOrCreate(
            [
                'student_id' => $studentId,
                'subject' => $subject,
                'level' => $level,
            ],
            [
                'total_sessions' => 0,
                'completed_sessions' => 0,
                'total_questions_answered' => 0,
                'correct_answers' => 0,
                'average_score' => 0,
                'total_time_spent_minutes' => 0,
                'current_streak_days' => 0,
                'longest_streak_days' => 0,
            ]
        );
    }

    /**
     * Analyze student performance and identify strengths/weaknesses
     */
    public function analyzePerformance(int $studentId, string $subject, string $level): void
    {
        // Get recent sessions
        $sessions = LearningSession::where('student_id', $studentId)
            ->whereHas('lesson', function($q) use ($subject, $level) {
                $q->where('subject', $subject)->where('level', $level);
            })
            ->with(['answers.question.segment'])
            ->latest()
            ->limit(10)
            ->get();

        if ($sessions->isEmpty()) {
            return;
        }

        // Analyze by topic (segment titles)
        $topicPerformance = [];

        foreach ($sessions as $session) {
            foreach ($session->answers as $answer) {
                $topic = $answer->question->segment->title;
                
                if (!isset($topicPerformance[$topic])) {
                    $topicPerformance[$topic] = [
                        'total' => 0,
                        'correct' => 0,
                    ];
                }

                $topicPerformance[$topic]['total']++;
                if ($answer->is_correct) {
                    $topicPerformance[$topic]['correct']++;
                }
            }
        }

        // Calculate scores and identify strengths/weaknesses
        $strengths = [];
        $weaknesses = [];

        foreach ($topicPerformance as $topic => $stats) {
            $score = ($stats['correct'] / $stats['total']) * 100;

            if ($score >= 80) {
                $strengths[] = $topic;
            } elseif ($score < 60) {
                $weaknesses[] = $topic;
            }
        }

        // Determine learning pace
        $avgTimePerQuestion = $sessions->avg(function($session) {
            return $session->total_questions > 0 
                ? $session->duration_seconds / $session->total_questions 
                : 0;
        });

        $learningPace = match(true) {
            $avgTimePerQuestion < 30 => 'fast',
            $avgTimePerQuestion < 60 => 'medium',
            default => 'slow',
        };

        // Update analytics
        $analytics = $this->getOrCreateAnalytics($studentId, $subject, $level);
        $analytics->update([
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'learning_pace' => ['overall' => $learningPace],
        ]);
    }

    /**
     * Get student progress summary
     */
    public function getProgressSummary(int $studentId): array
    {
        $analytics = LearningAnalytics::where('student_id', $studentId)->get();

        return [
            'total_subjects' => $analytics->count(),
            'total_sessions' => $analytics->sum('total_sessions'),
            'completed_sessions' => $analytics->sum('completed_sessions'),
            'total_questions_answered' => $analytics->sum('total_questions_answered'),
            'overall_accuracy' => $analytics->avg('average_score'),
            'total_time_spent_hours' => round($analytics->sum('total_time_spent_minutes') / 60, 1),
            'current_streak' => $analytics->max('current_streak_days'),
            'longest_streak' => $analytics->max('longest_streak_days'),
            'by_subject' => $analytics->map(function($a) {
                return [
                    'subject' => $a->subject,
                    'level' => $a->level,
                    'average_score' => $a->average_score,
                    'sessions' => $a->completed_sessions,
                    'strengths' => $a->strengths,
                    'weaknesses' => $a->weaknesses,
                ];
            }),
        ];
    }

    /**
     * Get detailed progress for a subject
     */
    public function getSubjectProgress(int $studentId, string $subject, string $level): array
    {
        $analytics = LearningAnalytics::where('student_id', $studentId)
            ->where('subject', $subject)
            ->where('level', $level)
            ->first();

        if (!$analytics) {
            return [];
        }

        // Get recent sessions
        $recentSessions = LearningSession::where('student_id', $studentId)
            ->whereHas('lesson', function($q) use ($subject, $level) {
                $q->where('subject', $subject)->where('level', $level);
            })
            ->with('lesson:id,title')
            ->latest()
            ->limit(5)
            ->get();

        return [
            'subject' => $analytics->subject,
            'level' => $analytics->level,
            'total_sessions' => $analytics->total_sessions,
            'completed_sessions' => $analytics->completed_sessions,
            'average_score' => $analytics->average_score,
            'total_time_hours' => round($analytics->total_time_spent_minutes / 60, 1),
            'current_streak' => $analytics->current_streak_days,
            'strengths' => $analytics->strengths ?? [],
            'weaknesses' => $analytics->weaknesses ?? [],
            'learning_pace' => $analytics->learning_pace ?? [],
            'recent_sessions' => $recentSessions->map(function($session) {
                return [
                    'lesson_title' => $session->lesson->title,
                    'score' => $session->score_percentage,
                    'completed_at' => $session->completed_at,
                ];
            }),
        ];
    }
}
