<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'order',
        'title',
        'content',
        'ai_explanation',
        'audio_url',
        'audio_duration',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class)->orderBy('order');
    }

    public function hasAudio(): bool
    {
        return !empty($this->audio_url);
    }
}
