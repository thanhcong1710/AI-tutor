<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use App\Models\LearningSession;

class QuizCommand extends Command
{
    protected string $name = 'quiz';
    protected string $description = 'Take a quiz for the current lesson topic';

    public function handle()
    {
        $telegramId = $this->getUpdate()->getMessage()->getFrom()->getId();
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $this->replyWithMessage(['text' => "Please verify your account first. Type /start."]);
            return;
        }

        // Find active session
        $session = LearningSession::where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->latest('updated_at')
            ->first();

        if (!$session) {
            $this->replyWithMessage(['text' => "You don't have an active lesson. Type /lessons to start learning."]);
            return;
        }

        if (!$session->current_segment_id) {
            $this->replyWithMessage(['text' => "No active topic found in the current lesson."]);
            return;
        }

        $segment = \App\Models\LessonSegment::with('questions')->find($session->current_segment_id);

        if (!$segment || $segment->questions->isEmpty()) {
            $this->replyWithMessage(['text' => "This topic doesn't have any quiz questions.\nFeel free to ask me to generate some practice exercises for you!"]);
            return;
        }

        $text = "📝 **Quiz: {$segment->title}**\n\n";

        foreach ($segment->questions as $index => $q) {
            $num = $index + 1;
            $text .= "**Q{$num}:** {$q->question_text}\n";

            if ($q->question_type === 'multiple_choice' && !empty($q->options)) {
                $options = is_string($q->options) ? json_decode($q->options, true) : $q->options;
                if (is_array($options)) {
                    foreach ($options as $opt) {
                        $text .= "- {$opt}\n";
                    }
                }
            }
            $text .= "\n";
        }

        $text .= "👉 *Reply with your answers (e.g., '1A, 2B') so I can check them!*";

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}
