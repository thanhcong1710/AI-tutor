<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_segment_id',
        'order',
        'type',
        'question',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'difficulty',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(LessonSegment::class, 'lesson_segment_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'question_id');
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    public function checkAnswer(string $answer): bool
    {
        return trim(strtolower($answer)) === trim(strtolower($this->correct_answer));
    }
}
