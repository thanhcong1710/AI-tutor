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
    protected $name = 'start';

    /**
     * @var string Command Description
     */
    protected $description = 'Start using AI Tutor Bot';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $message = $this->getUpdate()->getMessage();
        $chatId = $message->getChat()->getId();
        $username = $message->getFrom()->getUsername();
        $firstName = $message->getFrom()->getFirstName();
        $telegramId = $message->getFrom()->getId();

        // 1. Check if user exists
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            // Create user for Telegram platform
            $user = User::create([
                'name' => $firstName . ' ' . $message->getFrom()->getLastName(),
                'email' => strtolower($username ?? Str::random(8)) . '@telegram.user', // Placeholder email
                'password' => bcrypt(Str::random(16)),
                'telegram_id' => $telegramId,
                'role' => 'student',
                'platform' => 'telegram',
                'subscription_type' => 'free',
            ]);

            $firstTime = true;
        } else {
            $firstTime = false;
        }

        // 2. Reply to user
        $replyText = "👋 Hello, {$firstName}!\n\n";

        if ($firstTime) {
            $replyText .= "Welcome to **AI Tutor**! 🎓\n";
            $replyText .= "I have created a student account for you.\n\n";
        } else {
            $replyText .= "Welcome back to **AI Tutor**! 🎓\n";
        }

        $replyText .= "Here's what I can do:\n";
        $replyText .= "📚 Send me study topics, and I'll explain them.\n";
        $replyText .= "📝 Ask for quizzes or practice questions.\n";
        $replyText .= "🗣️ Send voice messages, and I'll transcribe and answer!\n\n";
        $replyText .= "Type anything to start learning!";

        $this->replyWithMessage([
            'text' => $replyText,
            'parse_mode' => 'Markdown',
        ]);

        // Trigger a simple menu or typing action
        $this->replyWithChatAction(['action' => 'typing']);
    }
}
