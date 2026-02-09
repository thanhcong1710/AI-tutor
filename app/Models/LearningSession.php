<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'lesson_id',
        'platform',
        'status',
        'current_segment_id',
        'total_segments',
        'completed_segments',
        'total_questions',
        'correct_answers',
        'total_points',
        'earned_points',
        'duration_seconds',
        'completion_percentage',
        'score_percentage',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'session_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function updateProgress(): void
    {
        $this->completion_percentage = $this->total_segments > 0
            ? ($this->completed_segments / $this->total_segments) * 100
            : 0;

        $this->score_percentage = $this->total_questions > 0
            ? ($this->correct_answers / $this->total_questions) * 100
            : 0;

        $this->save();
    }
}
