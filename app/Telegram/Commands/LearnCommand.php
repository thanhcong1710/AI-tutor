<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Lesson;
use App\Models\User;
use App\Models\LearningSession;
use Illuminate\Support\Carbon;

class LearnCommand extends Command
{
    protected string $name = 'learn';
    protected string $description = 'Start learning a lesson: /learn [lesson_id]';

    public function handle()
    {
        $text = $this->getUpdate()->getMessage()->getText();
        $parts = preg_split('/\s+/', trim($text));
        $lessonId = $parts[1] ?? null;

        if (!$lessonId || !is_numeric($lessonId)) {
            $this->replyWithMessage([
                'text' => "Please verify the lesson ID.\nExample: `/learn 1`\n\nType /lessons to see the list."
            ]);
            return;
        }

        $telegramId = $this->getUpdate()->getMessage()->getFrom()->getId();
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $this->replyWithMessage(['text' => "Please verify your account first. Type /start."]);
            return;
        }

        $lesson = Lesson::find($lessonId);

        if (!$lesson) {
            $this->replyWithMessage(['text' => "Lesson #{$lessonId} not found."]);
            return;
        }

        // Close any other active sessions
        LearningSession::where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->update(['status' => 'paused']);

        // Check if existing session for this lesson exists
        $session = LearningSession::where('student_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->latest()
            ->first();

        if (!$session) {
            // Create new session
            $session = LearningSession::create([
                'student_id' => $user->id,
                'lesson_id' => $lessonId,
                'platform' => 'telegram',
                'status' => 'in_progress',
                'current_segment_id' => $lesson->segments->first()->id ?? null,
                'total_segments' => $lesson->segments->count(),
                'started_at' => Carbon::now(),
            ]);
            $msg = "🚀 **Started Lesson:** {$lesson->title}\n\n";
        } else {
            // Resume session
            $session->status = 'in_progress';
            $session->updated_at = Carbon::now(); // Bump updated_at for latest check
            $session->save();
            $msg = "🔄 **Resumed Lesson:** {$lesson->title}\n\n";
        }

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
