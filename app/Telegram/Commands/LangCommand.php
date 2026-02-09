<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;

class LangCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'lang';

    /**
     * @var string Command Description
     */
    protected string $description = 'Change language: /lang vi or /lang en';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        // Get arguments directly if supported, or parse text
        // SDK might parse args, but lets use simple parsing
        $text = $this->getUpdate()->getMessage()->getText();
        // /lang vi -> ["/lang", "vi"]
        $parts = preg_split('/\s+/', trim($text));
        $lang = strtolower($parts[1] ?? '');

        if (!in_array($lang, ['vi', 'en'])) {
            $this->replyWithMessage([
                'text' => "Please specific language: 'vi' (Vietnamese) or 'en' (English).\nExample: /lang vi"
            ]);
            return;
        }

        $telegramId = $this->getUpdate()->getMessage()->getFrom()->getId();
        $user = User::where('telegram_id', $telegramId)->first();

        if ($user) {
            $user->update(['language' => $lang]);

            $msg = ($lang === 'vi')
                ? "Đã chuyển ngôn ngữ sang Tiếng Việt! 🇻🇳\nTừ giờ tôi sẽ trả lời bằng tiếng Việt."
                : "Language switched to English! 🇺🇸\nI will answer in English from now on.";

            $this->replyWithMessage(['text' => $msg]);
        } else {
            $this->replyWithMessage(['text' => "Please start the bot first using /start."]);
        }
    }
}
