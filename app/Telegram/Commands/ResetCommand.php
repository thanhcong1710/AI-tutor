<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use App\Models\LearningSession;

class ResetCommand extends Command
{
    protected string $name = 'reset';
    protected string $description = 'Restart the current active lesson';

    public function handle()
    {
        $telegramId = $this->getUpdate()->getMessage()->getFrom()->getId();
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $this->replyWithMessage(['text' => "Please verify your account first. Type /start."]);
            return;
        }

        // Find active session (in_progress or paused)
        // If completed, we might want to find the latest completed one too to restart?
        // But user request says "restart current active lesson".
        // Let's find latest session regardless of status.

        $session = LearningSession::where('student_id', $user->id)
            ->latest('updated_at')
            ->first();

        if (!$session) {
            $this->replyWithMessage(['text' => "You don't have any lesson history to reset. Type /lessons to start learning."]);
            return;
        }

        $lesson = $session->lesson;

        // Reset Logic
        $session->status = 'in_progress';
        $session->current_segment_id = $lesson->segments->sortBy('order')->first()->id ?? null;
        $session->completed_segments = 0;
        $session->score_percentage = 0;
        $session->completed_at = null;
        $session->updated_at = now();
        $session->save();

        $msg = "🔄 **Lesson Restarted:** {$lesson->title}\n\n";

        // Get content preview
        $currentSegment = $session->current_segment_id
            ? $lesson->segments->where('id', $session->current_segment_id)->first()
            : null;

        if ($currentSegment) {
            $msg .= "📖 **Topic:** {$currentSegment->title}\n";
            $msg .= substr($currentSegment->content, 0, 500) . "...\n\n";
        } else {
            $msg .= "📖 **Overview:** " . substr($lesson->content ?? $lesson->description, 0, 500) . "...\n\n";
        }
        $msg .= "💡 *Ask me anything about this lesson!*";

        $this->replyWithMessage([
            'text' => $msg,
            'parse_mode' => 'Markdown'
        ]);
    }
}
