<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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

        // 1. Check if Telegram ID is already linked
        $user = User::where('telegram_id', $telegramId)->first();

        // --- SCENARIO A: USER ALREADY LINKED ---
        if ($user) {
            // Check for deep link parameters like /start learn_123
            $parts = preg_split('/\s+/', trim($text));
            $param = isset($parts[1]) ? trim($parts[1]) : null;

            if ($param && strpos($param, 'learn_') === 0) {
                $lessonId = str_replace('learn_', '', $param);
                $lesson = \App\Models\Lesson::find($lessonId);

                if ($lesson) {
                    $this->startLessonSession($user, $lesson);
                    return;
                }
            }

            $this->replyWithMessage([
                'text' => "👋 Welcome back, **{$user->name}**!\nRole: `{$user->role}`\n\nType /lessons to see available lessons.\n\n" . $this->getCommandList(),
                'parse_mode' => 'Markdown',
            ]);
            return;
        }

        // --- SCENARIO B: USER NOT LINKED ---
        $parts = preg_split('/\s+/', trim($text));
        $param = isset($parts[1]) ? trim($parts[1]) : null;

        // B1. Handling Deep Link for Unlinked User
        if ($param && strpos($param, 'learn_') === 0) {
            $pendingLessonId = str_replace('learn_', '', $param);
            // Cache the intention to learn this lesson for 1 hour
            Cache::put("pending_lesson_{$telegramId}", $pendingLessonId, 3600);

            $this->replyWithMessage([
                'text' => "🔒 **Authentication Required**\n\nYou requested to learn Lesson #{$pendingLessonId}.\nPLEASE LINK YOUR ACCOUNT FIRST to access this content.\n\nType:\n`/start your_email@example.com`\n\nExample:\n`/start student@gmail.com`",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        // B2. Handling Login Attempt (Email)
        $email = $param;

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
        if ($user->telegram_id && $user->telegram_id != $telegramId) {
            $this->replyWithMessage([
                'text' => "❌ This email is already linked to another Telegram account.\nPlease contact support if this is an error."
            ]);
            return;
        }

        // Update User
        $user->telegram_id = $telegramId;
        $user->save();

        $this->replyWithMessage([
            'text' => "✅ **Account Linked Successfully!**\n\nHello **{$user->name}** ({$user->role})!\nYou can now use the AI Tutor Bot.\n",
            'parse_mode' => 'Markdown',
        ]);

        $this->replyWithChatAction(['action' => 'typing']);

        // --- SCENARIO C: POST-LOGIN ACTION Check ---
        $pendingLessonId = Cache::pull("pending_lesson_{$telegramId}");
        if ($pendingLessonId) {
            $lesson = \App\Models\Lesson::find($pendingLessonId);
            if ($lesson) {
                // Auto-start the pending lesson
                $this->startLessonSession($user, $lesson);
                return;
            }
        }

        $this->replyWithMessage([
            'text' => "Type /lessons to view available lessons.\n\n" . $this->getCommandList(),
            'parse_mode' => 'Markdown',
        ]);
    }

    /**
     * Helper to get command list string
     */
    protected function getCommandList(): string
    {
        return "🛠 **Available Commands:**\n" .
            "/next - Go to next topic\n" .
            "/quiz - Take a quiz\n" .
            "/lessons - View all lessons\n" .
            "/lang vi - Switch to Vietnamese\n" .
            "/lang en - Switch to English";
    }

    /**
     * Helper to start/resume session and reply to user
     */
    protected function startLessonSession($user, $lesson)
    {
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
        $msg .= "💡 *Ask me anything about this lesson!*\n\n";
        $msg .= $this->getCommandList();

        $this->replyWithMessage([
            'text' => $msg,
            'parse_mode' => 'Markdown',
        ]);
    }
}
