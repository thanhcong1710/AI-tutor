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
