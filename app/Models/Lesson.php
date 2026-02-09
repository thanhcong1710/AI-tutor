<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'subject',
        'level',
        'estimated_duration',
        'original_file_path',
        'original_file_type',
        'content',
        'metadata',
        'status',
        'processing_error',
        'published_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'published_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(LessonSegment::class)->orderBy('order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LearningSession::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LessonAssignment::class);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}
