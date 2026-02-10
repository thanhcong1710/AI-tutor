<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use Illuminate\Support\Str;

class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'start';

    /**
     * @var string Command Description
     */
    protected string $description = 'Start using AI Tutor Bot. Usage: /start [email]';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $text = $message->getText();
        $telegramId = $message->getFrom()->getId();
        $firstName = $message->getFrom()->getFirstName();

        // 1. Check if Telegram ID is already linked
        $user = User::where('telegram_id', $telegramId)->first();

        if ($user) {
            // Check for deep link parameters like /start learn_123
            $parts = preg_split('/\s+/', trim($text));
            $param = $parts[1] ?? null;

            if ($param && strpos($param, 'learn_') === 0) {
                $lessonId = str_replace('learn_', '', $param);
                $lesson = \App\Models\Lesson::find($lessonId);

                if ($lesson) {
                    // Close other sessions
                    \App\Models\LearningSession::where('student_id', $user->id)
                        ->where('status', 'in_progress')
                        ->update(['status' => 'paused']);

                    // Find or Create Session
                    $session = \App\Models\LearningSession::where('student_id', $user->id)
                        ->where('lesson_id', $lesson->id)
                        ->latest()
                        ->first();

                    if (!$session) {
                        $session = \App\Models\LearningSession::create([
                            'student_id' => $user->id,
                            'lesson_id' => $lesson->id,
                            'platform' => 'telegram',
                            'status' => 'in_progress',
                            'current_segment_id' => $lesson->segments->sortBy('order')->first()->id ?? null,
                            'total_segments' => $lesson->segments->count(),
                            'started_at' => now(),
                        ]);
                        $msg = "🚀 **Started Lesson via Link:** {$lesson->title}\n\n";
                    } else {
                        $session->status = 'in_progress';
                        $session->updated_at = now();
                        $session->save();
                        $msg = "🔄 **Resumed Lesson via Link:** {$lesson->title}\n\n";
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
                        'parse_mode' => 'Markdown',
                    ]);
                    return;
                }
            }

            $this->replyWithMessage([
                'text' => "👋 Welcome back, **{$user->name}**!\nRole: `{$user->role}`\n\nType /lessons to see available lessons.",
                'parse_mode' => 'Markdown',
            ]);
            return;
        }

        // 2. Parse Email from command: /start email@example.com
        // /start or /start email
        $parts = preg_split('/\s+/', trim($text));
        $email = $parts[1] ?? null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->replyWithMessage([
                'text' => "⚠️ **Account Not Linked!**\n\nTo use this bot, please link your website account by typing:\n`/start your_email@example.com`\n\nExample:\n`/start student@example.com`",
                'parse_mode' => 'Markdown',
            ]);
            return;
        }

        // 3. Find User by Email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->replyWithMessage([
                'text' => "❌ Email `{$email}` not found.\nPlease register on the website first or check your spelling.",
                'parse_mode' => 'Markdown',
            ]);
            return;
        }

        // 4. Verification / Linking
        // Check if this user is already linked to another Telegram ID?
        if ($user->telegram_id && $user->telegram_id != $telegramId) {
            $this->replyWithMessage([
                'text' => "❌ This email is already linked to another Telegram account.\nPlease contact support if this is an error."
            ]);
            return;
        }

        // Update User
        $user->telegram_id = $telegramId;
        // Keep original platform or update? Maybe just add telegram_id is enough.
        // But we might want to know they are active on telegram.
        // $user->platform = 'telegram'; // Don't overwrite if they are web user primarily
        $user->save();

        $this->replyWithMessage([
            'text' => "✅ **Account Linked Successfully!**\n\nHello **{$user->name}** ({$user->role})!\nYou can now use the AI Tutor Bot.\n\nType /lessons to view lessons.",
            'parse_mode' => 'Markdown',
        ]);

        // Optional: Send typing action
        $this->replyWithChatAction(['action' => 'typing']);
    }
}
