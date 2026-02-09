<?php

namespace App\Services\Learning;

use App\Models\User;
use App\Models\LearningSession;
use App\Models\LearningAnalytics;
use App\Models\LessonAssignment;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Generate teacher dashboard analytics
     */
    public function getTeacherDashboard(int $teacherId): array
    {
        // Get all students assigned to this teacher's lessons
        $studentIds = LessonAssignment::where('teacher_id', $teacherId)
            ->distinct('student_id')
            ->pluck('student_id');

        // Get completion stats
        $assignments = LessonAssignment::where('teacher_id', $teacherId)
            ->selectRaw('
                COUNT(*) as total_assignments,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = "overdue" THEN 1 ELSE 0 END) as overdue
            ')
            ->first();

        // Get average scores
        $avgScore = LearningSession::whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->avg('score_percentage');

        // Get top performers
        $topPerformers = LearningAnalytics::whereIn('student_id', $studentIds)
            ->with('student:id,name')
            ->orderByDesc('average_score')
            ->limit(5)
            ->get()
            ->map(function ($analytics) {
                return [
                    'student_name' => $analytics->student->name,
                    'average_score' => $analytics->average_score,
                    'completed_sessions' => $analytics->completed_sessions,
                ];
            });

        // Get struggling students
        $strugglingStudents = LearningAnalytics::whereIn('student_id', $studentIds)
            ->with('student:id,name')
            ->where('average_score', '<', 60)
            ->orderBy('average_score')
            ->limit(5)
            ->get()
            ->map(function ($analytics) {
                return [
                    'student_name' => $analytics->student->name,
                    'average_score' => $analytics->average_score,
                    'weaknesses' => $analytics->weaknesses ?? [],
                ];
            });

        return [
            'total_students' => $studentIds->count(),
            'total_assignments' => $assignments->total_assignments ?? 0,
            'completed_assignments' => $assignments->completed ?? 0,
            'in_progress_assignments' => $assignments->in_progress ?? 0,
            'overdue_assignments' => $assignments->overdue ?? 0,
            'average_score' => round($avgScore ?? 0, 1),
            'top_performers' => $topPerformers,
            'struggling_students' => $strugglingStudents,
        ];
    }

    /**
     * Generate student report for teacher
     */
    public function getStudentReport(int $studentId, ?int $teacherId = null): array
    {
        $student = User::findOrFail($studentId);

        // Get all analytics
        $analytics = LearningAnalytics::where('student_id', $studentId)->get();

        // Get recent sessions
        $recentSessions = LearningSession::where('student_id', $studentId)
            ->with('lesson:id,title,subject')
            ->latest()
            ->limit(10)
            ->get();

        // Get assignments (if teacher specified)
        $assignments = null;
        if ($teacherId) {
            $assignments = LessonAssignment::where('student_id', $studentId)
                ->where('teacher_id', $teacherId)
                ->with('lesson:id,title')
                ->get()
                ->map(function ($assignment) {
                    return [
                        'lesson_title' => $assignment->lesson->title,
                        'status' => $assignment->status,
                        'assigned_at' => $assignment->assigned_at,
                        'due_date' => $assignment->due_date,
                    ];
                });
        }

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
            ],
            'overall_stats' => [
                'total_sessions' => $analytics->sum('total_sessions'),
                'completed_sessions' => $analytics->sum('completed_sessions'),
                'average_score' => round($analytics->avg('average_score'), 1),
                'total_time_hours' => round($analytics->sum('total_time_spent_minutes') / 60, 1),
                'current_streak' => $analytics->max('current_streak_days'),
            ],
            'by_subject' => $analytics->map(function ($a) {
                return [
                    'subject' => $a->subject,
                    'level' => $a->level,
                    'average_score' => $a->average_score,
                    'sessions' => $a->completed_sessions,
                    'strengths' => $a->strengths ?? [],
                    'weaknesses' => $a->weaknesses ?? [],
                ];
            }),
            'recent_sessions' => $recentSessions->map(function ($session) {
                return [
                    'lesson_title' => $session->lesson->title,
                    'subject' => $session->lesson->subject,
                    'score' => $session->score_percentage,
                    'completion' => $session->completion_percentage,
                    'completed_at' => $session->completed_at,
                ];
            }),
            'assignments' => $assignments,
        ];
    }

    /**
     * Generate lesson performance report
     */
    public function getLessonPerformance(int $lessonId): array
    {
        $sessions = LearningSession::where('lesson_id', $lessonId)
            ->where('status', 'completed')
            ->with('student:id,name')
            ->get();

        if ($sessions->isEmpty()) {
            return [
                'total_students' => 0,
                'average_score' => 0,
                'average_completion_time' => 0,
                'students' => [],
            ];
        }

        return [
            'total_students' => $sessions->count(),
            'average_score' => round($sessions->avg('score_percentage'), 1),
            'average_completion_time' => round($sessions->avg('duration_seconds') / 60, 1),
            'completion_rate' => round(($sessions->where('completion_percentage', 100)->count() / $sessions->count()) * 100, 1),
            'students' => $sessions->map(function ($session) {
                return [
                    'student_name' => $session->student->name,
                    'score' => $session->score_percentage,
                    'completion' => $session->completion_percentage,
                    'time_spent_minutes' => round($session->duration_seconds / 60, 1),
                    'completed_at' => $session->completed_at,
                ];
            })->sortByDesc('score')->values(),
        ];
    }

    /**
     * Get system-wide statistics (for admin)
     */
    public function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_lessons' => \App\Models\Lesson::count(),
            'total_sessions' => LearningSession::count(),
            'completed_sessions' => LearningSession::where('status', 'completed')->count(),
            'average_score' => round(LearningSession::where('status', 'completed')->avg('score_percentage'), 1),
            'total_questions_answered' => \App\Models\StudentAnswer::count(),
            'overall_accuracy' => round(
                (\App\Models\StudentAnswer::where('is_correct', true)->count() /
                    max(\App\Models\StudentAnswer::count(), 1)) * 100,
                1
            ),
        ];
    }
}
