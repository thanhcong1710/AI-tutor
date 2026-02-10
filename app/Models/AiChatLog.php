<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'platform_chat_id',
        'role',
        'message',
        'metadata',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
