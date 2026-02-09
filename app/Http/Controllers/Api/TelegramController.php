<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
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
        $username = $message->getFrom()->getUsername();

        // Check if it's a command (starts with /) - handled by SDK automatically usually, 
        // but we add a check just in case commandsHandler didn't catch it or for custom logic.
        if (strpos($text, '/') === 0) {
            return;
        }

        // Find or create user based on Telegram ID (optional logic)
        // For now, we just reply using AI.

        Telegram::sendChatAction([
            'chat_id' => $chatId,
            'action' => 'typing'
        ]);

        try {
            // Use LLM Service to generate response
            // We can provide some context about the user or previous conversation here if needed.
            $systemPrompt = "You are an AI Tutor helper on Telegram. Keep your answers concise, helpful, and friendly. The user is asking via a chat interface.";

            // Simulating AI response for now if LLMService is complex, but calling it directly:
            // $aiResponse = $this->llmService->generateResponse($text, $systemPrompt); 
            // For stability in this step, let's use a direct mock or real call:

            $aiResponse = $this->llmService->explainConcept($text, 'General Knowledge');

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $aiResponse['content'] ?? "I'm thinking...",
                'parse_mode' => 'Markdown'
            ]);

        } catch (\Exception $e) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "Sorry, I encountered an error creating a response. Please try again."
            ]);
        }
    }
}
