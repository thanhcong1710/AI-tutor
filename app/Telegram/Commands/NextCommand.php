<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use App\Models\LearningSession;
use Illuminate\Support\Carbon;

class NextCommand extends Command
{
    protected string $name = 'next';
    protected string $description = 'Move to the next lesson segment';

    public function handle()
    {
        $telegramId = $this->getUpdate()->getMessage()->getFrom()->getId();
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $this->replyWithMessage(['text' => "Please verify your account first."]);
            return;
        }

        // Find active session
        $session = LearningSession::with(['lesson', 'lesson.segments'])
            ->where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->latest('updated_at')
            ->first();

        if (!$session || !$session->lesson) {
            $this->replyWithMessage(['text' => "You don't have an active lesson. Use /lessons to start one."]);
            return;
        }

        $segments = $session->lesson->segments; // Assumes ordered by 'order' in Model
        if ($segments->isEmpty()) {
            $this->replyWithMessage(['text' => "This lesson has no content segments."]);
            return;
        }

        // Find current position
        $currentIndex = -1;
        foreach ($segments as $index => $seg) {
            if ($seg->id == $session->current_segment_id) {
                $currentIndex = $index;
                break;
            }
        }

        // If not found (maybe started with null), start at 0
        if ($currentIndex === -1) {
            $nextIndex = 0;
        } else {
            $nextIndex = $currentIndex + 1;
        }

        if ($nextIndex < $segments->count()) {
            $nextSegment = $segments[$nextIndex];

            // Update Session
            $session->current_segment_id = $nextSegment->id;
            $session->updated_at = Carbon::now();
            $session->save();

            $msg = "👉 **Next Topic:** {$nextSegment->title}\n\n";
            $msg .= substr($nextSegment->content, 0, 800) . "...\n\n";
            $msg .= "💡 *Ready to learn? Ask me questions regarding this topic!*";

            $this->replyWithMessage([
                'text' => $msg,
                'parse_mode' => 'Markdown'
            ]);
        } else {
            // Finished
            $session->status = 'completed';
            $session->completed_at = Carbon::now();
            $session->save();

            $this->replyWithMessage([
                'text' => "🎉 **Congratulations!** You have completed the lesson: **{$session->lesson->title}**.\n\nType /lessons to start a new journey!",
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}
