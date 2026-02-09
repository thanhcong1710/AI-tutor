<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use App\Models\AiChatLog;
use App\Services\AI\LLMService;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $llmService;

    public function __construct(LLMService $llmService)
    {
        $this->llmService = $llmService;
    }

    /**
     * Handle incoming Telegram updates (Webhook)
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Returns Update object
            $update = Telegram::commandsHandler(true);

            // If it's a command, it's already handled. If not, handle as text message.
            if ($update->getMessage() && $update->getMessage()->getType() === 'text') {
                $this->handleTextMessage($update);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Telegram Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 200); // Always return 200 to Telegram
        }
    }

    /**
     * Handle text messages with AI
     */
    protected function handleTextMessage($update)
    {
        $message = $update->getMessage();
        $text = $message->getText();
        $chatId = $message->getChat()->getId();
        $telegramUserId = $message->getFrom()->getId();

        // Check if it's a command (starts with /)
        if (strpos($text, '/') === 0) {
            return;
        }

        // 1. Find User
        $user = User::where('telegram_id', $telegramUserId)->first();

        // 2. Log User Message
        try {
            AiChatLog::create([
                'user_id' => $user ? $user->id : null,
                'platform' => 'telegram',
                'platform_chat_id' => $chatId,
                'role' => 'user',
                'message' => $text,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log user message: ' . $e->getMessage());
        }

        Telegram::sendChatAction([
            'chat_id' => $chatId,
            'action' => 'typing'
        ]);

        try {
            // 3. Determine Context & Generate AI Response
            $language = $user->language ?? 'vi';

            // Find active session
            $activeSession = \App\Models\LearningSession::with('lesson.segments')
                ->where('student_id', $user ? $user->id : 0)
                ->where('status', 'in_progress')
                ->latest('updated_at')
                ->first();

            if ($activeSession && $activeSession->lesson) {
                // Prepare Context from Lesson
                $lesson = $activeSession->lesson;
                $contextData = "Current Lesson: {$lesson->title} ({$lesson->subject} - {$lesson->level})\n";

                $segment = $activeSession->current_segment_id
                    ? $lesson->segments->where('id', $activeSession->current_segment_id)->first()
                    : null;

                if ($segment) {
                    $contextData .= "Topic: {$segment->title}\n";
                    $contextData .= "Content:\n" . substr($segment->content, 0, 1500) . "...";
                } else {
                    $contextData .= "Overview:\n" . substr($lesson->content ?? $lesson->description, 0, 1500) . "...";
                }

                $aiResponse = $this->llmService->chatWithContext($text, $contextData, $language);
            } else {
                // General Chat (No active lesson)
                $aiResponse = $this->llmService->explainConcept($text, 'General Chat', $language);
            }

            $replyText = $aiResponse['content'] ?? "I'm thinking...";

            // 4. Log AI Response
            try {
                AiChatLog::create([
                    'user_id' => $user ? $user->id : null,
                    'platform' => 'telegram',
                    'platform_chat_id' => $chatId,
                    'role' => 'assistant',
                    'message' => $replyText,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log AI message: ' . $e->getMessage());
            }

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $replyText,
                'parse_mode' => 'Markdown'
            ]);

        } catch (\Exception $e) {
            Log::error('AI Response Error: ' . $e->getMessage());
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "Sorry, I encountered an error creating a response. Please try again."
            ]);
        }
    }
}
