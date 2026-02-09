<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LLMService
{
    public function __construct()
    {
        // Configs are loaded dynamically in chat methods to support runtime changes
    }

    public function explainConcept(string $concept, string $context = 'General', string $language = 'vi'): array
    {
        $explanation = $this->generateExplanation($concept, $context, 'Beginner', $language);
        return ['content' => $explanation];
    }

    public function chatWithContext(string $message, string $contextData, string $language = 'vi'): array
    {
        $langInstruction = $language === 'vi' ? 'Answer in Vietnamese.' : 'Answer in English.';

        $prompt = "You are an AI Tutor. The student is currently studying this material:\n\n"
            . "--- CONTEXT START ---\n{$contextData}\n--- CONTEXT END ---\n\n"
            . "User Question: {$message}\n\n"
            . "Please answer the question based on the context provided. If the question is unrelated, answer generally but mention the current lesson context if relevant.\n"
            . "{$langInstruction}";

        $response = $this->chat($prompt);
        return ['content' => $response];
    }

    public function generateExplanation(string $content, string $subject, string $level, string $language = 'vi'): string
    {
        $langInstruction = $language === 'vi' ? 'Answer in Vietnamese.' : 'Answer in English.';

        $prompt = "You are an expert {$subject} tutor teaching at {$level} level. 
        
Explain the following content in a clear, engaging way suitable for {$level} students.
{$langInstruction}

{$content}

Provide a detailed explanation that:
1. Breaks down complex concepts into simple terms
2. Uses relevant examples
3. Highlights key points
4. Is conversational and encouraging";

        return $this->chat($prompt);
    }

    public function generateQuestions(string $content, int $count = 5, string $difficulty = 'medium'): array
    {
        $prompt = "Based on the following content, generate {$count} {$difficulty} difficulty questions.

Content:
{$content}

For each question, provide:
1. Question text
2. Question type (multiple_choice, true_false, or short_answer)
3. Options (if multiple choice, provide 4 options labeled A, B, C, D)
4. Correct answer
5. Explanation of why this is the correct answer

Return the response as a JSON array of questions with this structure ONLY (no markdown code blocks):
[
  {
    \"question\": \"...\",
    \"type\": \"multiple_choice\",
    \"options\": [\"A. ...\", \"B. ...\", \"C. ...\", \"D. ...\"],
    \"correct_answer\": \"A\",
    \"explanation\": \"...\"
  }
]";

        $response = $this->chat($prompt);
        $response = $this->cleanJson($response);

        try {
            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to parse questions JSON', ['response' => $response, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function evaluateAnswer(string $question, string $correctAnswer, string $studentAnswer): array
    {
        $prompt = "You are evaluating a student's answer.

Question: {$question}
Correct Answer: {$correctAnswer}
Student's Answer: {$studentAnswer}

Evaluate the student's answer and provide:
1. is_correct (boolean): Whether the answer is correct
2. score (0-100): How close the answer is to being correct
3. feedback (string): Constructive feedback for the student
4. suggestions (string): What the student should focus on

Return as JSON ONLY (no markdown code blocks):
{
  \"is_correct\": true/false,
  \"score\": 0-100,
  \"feedback\": \"...\",
  \"suggestions\": \"...\"
}";

        $response = $this->chat($prompt);
        $response = $this->cleanJson($response);

        try {
            return json_decode($response, true) ?? [
                'is_correct' => false,
                'score' => 0,
                'feedback' => 'Unable to evaluate answer',
                'suggestions' => 'Please try again'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to parse evaluation JSON', ['response' => $response, 'error' => $e->getMessage()]);
            return [
                'is_correct' => false,
                'score' => 0,
                'feedback' => 'Unable to evaluate answer',
                'suggestions' => 'Please try again'
            ];
        }
    }

    public function analyzePerformance(array $sessionData): array
    {
        $prompt = "Analyze this student's learning performance:

" . json_encode($sessionData, JSON_PRETTY_PRINT) . "

Provide:
1. strengths: Array of topics/skills the student excels at
2. weaknesses: Array of topics/skills needing improvement
3. recommendations: Array of specific recommendations
4. learning_pace: 'fast', 'medium', or 'slow'

Return as JSON ONLY (no markdown code blocks):
{
  \"strengths\": [...],
  \"weaknesses\": [...],
  \"recommendations\": [...],
  \"learning_pace\": \"...\"
}";

        $response = $this->chat($prompt);
        $response = $this->cleanJson($response);

        try {
            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to parse analysis JSON', ['response' => $response, 'error' => $e->getMessage()]);
            return [];
        }
    }

    protected function cleanJson(string $response): string
    {
        return str_replace(['```json', '```'], '', $response);
    }

    /**
     * Hybrid Chat: Gemini Primary -> OpenAI Backup
     */
    protected function chat(string $prompt, array $options = []): string
    {
        // 1. Try Gemini (Primary)
        if (config('services.gemini.api_key')) {
            try {
                $response = $this->chatWithGemini($prompt, $options);
                if (!empty($response)) {
                    return $response;
                }
            } catch (\Exception $e) {
                Log::warning('Gemini Primary Failed', ['error' => $e->getMessage()]);
            }
        }

        // 2. Fallback to OpenAI (Backup)
        if (config('services.openai.api_key')) {
            Log::info('Switching to OpenAI Backup...');
            return $this->chatWithOpenAI($prompt, $options);
        }

        return "I'm having trouble thinking right now. Please check my AI configuration.";
    }

    protected function chatWithGemini(string $prompt, array $options = []): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::post($url, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("Gemini Error " . $response->status() . ": " . $response->body());
        }

        return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    protected function chatWithOpenAI(string $prompt, array $options = []): string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o');

        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI Backup Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return "Error from OpenAI Backup.";
        }

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }
}
