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
        Log::info('--- Telegram Webhook HIT ---');
        Log::info('Payload:', $request->all());

        try {
            // 1. Get Update Object manually to ensure we have it
            $update = Telegram::getWebhookUpdate();

            // 2. Let SDK handle commands (if any)
            Telegram::commandsHandler(true);

            // 3. Check if it's a normal text message (not a command)
            // Note: commandsHandler might have already responded if it was a command.
            // But we check here to process AI chat.
            $message = $update->getMessage();

            if ($message && $message->getText()) {
                $text = $message->getText();
                Log::info('Payload text:', ['text' => $text]);
                // Explicitly skip commands (starts with /)
                if (strpos($text, '/') === 0) {
                    return response()->json(['status' => 'ok']);
                }

                // Process User Chat
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

        Log::info('Payload handleTextMessage:', ['text' => $text]);
        // Check if it's a command (starts with /)
        if (strpos($text, '/') === 0) {
            return;
        }

        // 1. Find User
        $user = User::where('telegram_id', $telegramUserId)->first();
        Log::info("Telegram User ID: $telegramUserId found linked User ID: " . ($user ? $user->id : 'None'));

        // 2. Log User Message
        try {
            Log::info("Logging User Message: " . substr($text, 0, 50));
            AiChatLog::create([
                'user_id' => $user ? $user->id : null,
                'platform' => 'telegram',
                'platform_chat_id' => (string) $chatId,
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
            Log::info("Generating AI Response...");
            $startTime = microtime(true);
            $language = $user->language ?? 'vi';

            // Find active session
            $activeSession = \App\Models\LearningSession::with('lesson.segments')
                ->where('student_id', $user ? $user->id : 0)
                ->where('status', 'in_progress')
                ->latest('updated_at')
                ->first();

            if ($activeSession && $activeSession->lesson) {
                Log::info("Active Session Found: Lesson ID " . $activeSession->lesson->id);
                // Prepare Context from Lesson
                $lesson = $activeSession->lesson;

                // Build Lesson Outline Context
                $contextData = "Current Lesson: {$lesson->title} ({$lesson->subject} - {$lesson->level})\n";
                $contextData .= "Lesson Outline (Segments):\n";

                $segments = $lesson->segments; // Already ordered by model
                $currentSegment = null;

                foreach ($segments as $seg) {
                    $marker = ($seg->id == $activeSession->current_segment_id) ? "👉 [CURRENT STEP] " : "- ";
                    $contextData .= "{$marker}{$seg->title}\n";
                    if ($seg->id == $activeSession->current_segment_id) {
                        $currentSegment = $seg;
                    }
                }
                $contextData .= "\n";

                if ($currentSegment) {
                    // Detailed Content of CURRENT Segment
                    $contextData .= "--- CURRENT SEGMENT CONTENT ---\n";
                    $contextData .= "Title: {$currentSegment->title}\n";
                    $contextData .= "Content:\n" . substr($currentSegment->content, 0, 2500) . "...\n";
                    $contextData .= "--- END CURRENT SEGMENT CONTENT ---\n\n";

                    // AI Instruction
                    $contextData .= "INSTRUCTION for AI Tutor:\n";
                    $contextData .= "1. You are strictly teaching the Current Segment: '{$currentSegment->title}'. Do not explain future segments yet.\n";
                    $contextData .= "2. Explain concepts based on the provided content.\n";
                    $contextData .= "3. Be interactive. Ask the student if they understand before moving on.\n";
                    $contextData .= "4. Stick to the lesson plan.\n";
                } else {
                    $contextData .= "Overview:\n" . substr($lesson->content ?? $lesson->description, 0, 1500) . "...";
                    $contextData .= "\nINSTRUCTION: Provide an overview and guide the user to start the first segment.\n";
                }

                $aiResponse = $this->llmService->chatWithContext($text, $contextData, $language);
            } else {
                Log::info("No Active Session. Promoting General Chat.");
                // General Chat (No active lesson)
                $aiResponse = $this->llmService->explainConcept($text, 'General Chat', $language);
            }

            $replyText = $aiResponse['content'] ?? "I'm thinking...";
            $duration = microtime(true) - $startTime;

            // Extract token usage
            $tokensInput = $aiResponse['tokens_input'] ?? 0;
            $tokensOutput = $aiResponse['tokens_output'] ?? 0;
            $tokensTotal = $aiResponse['tokens_total'] ?? 0;

            Log::info("AI Generated Response (Length: " . strlen($replyText) . ") in " . round($duration, 2) . "s | Tokens: {$tokensTotal}");

            // 4. Log AI Response with Token Metadata
            try {
                AiChatLog::create([
                    'user_id' => $user ? $user->id : null,
                    'platform' => 'telegram',
                    'platform_chat_id' => (string) $chatId,
                    'role' => 'assistant',
                    'message' => $replyText,
                    'prompt_tokens' => $tokensInput,
                    'completion_tokens' => $tokensOutput,
                    'total_tokens' => $tokensTotal,
                    'metadata' => [
                        'processing_time' => round($duration, 2),
                        'context_type' => ($activeSession && $activeSession->lesson) ? 'lesson' : 'general',
                        'lesson_id' => ($activeSession && $activeSession->lesson) ? $activeSession->lesson->id : null,
                        'language' => $language,
                        'model' => config('services.gemini.model', 'unknown'),
                    ]
                ]);
                Log::info("AI Response Logged to Database with Token Metadata.");
            } catch (\Exception $e) {
                Log::error('Failed to log AI message: ' . $e->getMessage());
            }

            // 5. Update User Token Usage
            if ($user && $tokensTotal > 0) {
                try {
                    $user->increment('total_tokens_used', $tokensTotal);
                    $user->increment('tokens_used_this_month', $tokensTotal);

                    // Reset monthly counter if needed
                    $now = now();
                    if (!$user->tokens_reset_date || $user->tokens_reset_date->month != $now->month) {
                        $user->tokens_used_this_month = $tokensTotal;
                        $user->tokens_reset_date = $now->startOfMonth();
                        $user->save();
                    }

                    Log::info("User Token Usage Updated: Total={$user->total_tokens_used}, Month={$user->tokens_used_this_month}");
                } catch (\Exception $e) {
                    Log::error('Failed to update user token usage: ' . $e->getMessage());
                }
            }

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $replyText,
                'parse_mode' => 'Markdown'
            ]);

        } catch (\Exception $e) {
            Log::error('AI Response Error or Telegram Send Error: ' . $e->getMessage());
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "Sorry, I encountered an error creating a response. Please try again."
            ]);
        }
    }
}
