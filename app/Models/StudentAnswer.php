<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'question_id',
        'student_answer',
        'answer_audio_url',
        'is_correct',
        'points_earned',
        'ai_feedback',
        'ai_evaluation',
        'attempt_number',
        'time_spent_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'ai_evaluation' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class, 'session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(LessonQuestion::class, 'question_id');
    }

    public function hasAudioAnswer(): bool
    {
        return !empty($this->answer_audio_url);
    }

    public function hasFeedback(): bool
    {
        return !empty($this->ai_feedback);
    }
}
