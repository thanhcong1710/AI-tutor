<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject',
        'level',
        'total_sessions',
        'completed_sessions',
        'total_questions_answered',
        'correct_answers',
        'average_score',
        'total_time_spent_minutes',
        'current_streak_days',
        'longest_streak_days',
        'last_activity_date',
        'strengths',
        'weaknesses',
        'learning_pace',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'learning_pace' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function updateStreak(): void
    {
        $today = now()->toDateString();
        $lastActivity = $this->last_activity_date?->toDateString();

        if ($lastActivity === $today) {
            return; // Already updated today
        }

        if ($lastActivity === now()->subDay()->toDateString()) {
            // Consecutive day
            $this->current_streak_days++;
        } else {
            // Streak broken
            $this->current_streak_days = 1;
        }

        if ($this->current_streak_days > $this->longest_streak_days) {
            $this->longest_streak_days = $this->current_streak_days;
        }

        $this->last_activity_date = $today;
        $this->save();
    }

    public function calculateAverageScore(): void
    {
        $this->average_score = $this->total_questions_answered > 0
            ? ($this->correct_answers / $this->total_questions_answered) * 100
            : 0;

        $this->save();
    }
}
