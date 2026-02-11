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
        Log::info('Payload: ' . json_encode($request->all()));

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

                    // Add Questions Context
                    $questions = $currentSegment->questions;
                    if ($questions->count() > 0) {
                        $contextData .= "\n--- QUIZ QUESTIONS & ANSWERS (For AI Grading Only) ---\n";
                        foreach ($questions as $q) {
                            $contextData .= "Question [ID:{$q->id}]: {$q->question}\nType: {$q->type}\n";
                            if ($q->options) {
                                $contextData .= "Options: " . json_encode($q->options) . "\n";
                            }
                            $contextData .= "Correct Answer: {$q->correct_answer}\nExplanation: {$q->explanation}\n\n";
                        }
                        $contextData .= "INSTRUCTION: \n";
                        $contextData .= "1. If the user asks for a quiz, present questions.\n";
                        $contextData .= "2. CRITICAL: If the user sends a short response like 'A', 'B', 'C', 'D' (or '1A', '2. B'), ASSUME they are answering the quiz questions above.\n";
                        $contextData .= "3. Check their answer against the 'Correct Answer' provided.\n";
                        $contextData .= "4. Reply with whether they are Correct or Incorrect, and explain why using the 'Explanation'.\n";
                        $contextData .= "5. SUPER IMPORTANT: For every question you grade, append this HIDDEN MARKER at the very end of your response: ||GRADE:QuestionID|IsCorrect(1/0)|StudentAnswer||\n";
                        $contextData .= "   Example: 'Correct! The answer is A.' ||GRADE:15|1|A|| 'Incorrect. The answer is B.' ||GRADE:16|0|C||\n";
                    }
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

            // --- PARSE QUIZ GRADES ---
            // Pattern: ||GRADE:QuestionID|IsCorrect(1/0)|StudentAnswer||
            $pattern = '/\|\|GRADE:(\d+)\|([01])\|(.*?)\|\|/s';
            if (preg_match_all($pattern, $replyText, $matches, PREG_SET_ORDER)) {
                Log::info("Found " . count($matches) . " quiz grades in AI response.");
                foreach ($matches as $match) {
                    $qId = $match[1];
                    $isCorrect = (bool) $match[2];
                    $studentAns = trim($match[3]);

                    if ($activeSession) {
                        try {
                            // Deduplicate? Or allow multiple attempts? Let's create new attempt.
                            \App\Models\StudentAnswer::create([
                                'session_id' => $activeSession->id,
                                'question_id' => $qId,
                                'student_answer' => $studentAns,
                                'is_correct' => $isCorrect,
                                'points_earned' => $isCorrect ? 10 : 0,
                                'attempt_number' => 1, // Simple default for now
                            ]);
                            Log::info("Saved Student Answer: Q:{$qId} Correct:{$isCorrect}");
                        } catch (\Exception $e) {
                            Log::error("Failed to save student answer: " . $e->getMessage());
                        }
                    }
                }
                // Remove markers from reply
                $replyText = preg_replace($pattern, '', $replyText);
            }
            // -------------------------

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
