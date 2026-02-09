<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'teacher_id',
        'student_id',
        'assigned_at',
        'due_date',
        'status',
        'teacher_notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date && now()->isAfter($this->due_date) && $this->status !== 'completed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }
}
