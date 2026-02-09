<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Lesson;

class ListLessonsCommand extends Command
{
    protected string $name = 'lessons';
    protected string $description = 'List available lessons';

    public function handle()
    {
        // Get latest 10 lessons
        $lessons = Lesson::where('status', 'ready')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        if ($lessons->isEmpty()) {
            $this->replyWithMessage(['text' => "No lessons available yet."]);
            return;
        }

        $text = "📚 **Available Lessons:**\n\n";
        foreach ($lessons as $lesson) {
            $text .= "🆔 `{$lesson->id}`: **{$lesson->title}**\n";
            $text .= "   Level: {$lesson->level} | Subject: {$lesson->subject}\n\n";
        }

        $text .= "👉 Type `/learn [id]` to start learning.\nExample: `/learn 1`";

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}
