<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class LLMService
{
    protected string $model;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $this->model = config('ai_tutor.openai.model', 'gpt-4o');
        $this->maxTokens = config('ai_tutor.openai.max_tokens', 2000);
        $this->temperature = config('ai_tutor.openai.temperature', 0.7);
    }

    /**
     * Generate lesson explanation from content
     */
    public function generateExplanation(string $content, string $subject, string $level): string
    {
        $prompt = "You are an expert {$subject} tutor teaching at {$level} level. 
        
Explain the following content in a clear, engaging way suitable for {$level} students:

{$content}

Provide a detailed explanation that:
1. Breaks down complex concepts into simple terms
2. Uses relevant examples
3. Highlights key points
4. Is conversational and encouraging";

        return $this->chat($prompt);
    }

    /**
     * Generate questions from content
     */
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

Return the response as a JSON array of questions with this structure:
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

        try {
            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to parse questions JSON', ['response' => $response, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Evaluate student answer
     */
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

Return as JSON:
{
  \"is_correct\": true/false,
  \"score\": 0-100,
  \"feedback\": \"...\",
  \"suggestions\": \"...\"
}";

        $response = $this->chat($prompt);

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

    /**
     * Analyze student performance and identify strengths/weaknesses
     */
    public function analyzePerformance(array $sessionData): array
    {
        $prompt = "Analyze this student's learning performance:

" . json_encode($sessionData, JSON_PRETTY_PRINT) . "

Provide:
1. strengths: Array of topics/skills the student excels at
2. weaknesses: Array of topics/skills needing improvement
3. recommendations: Array of specific recommendations
4. learning_pace: 'fast', 'medium', or 'slow'

Return as JSON:
{
  \"strengths\": [...],
  \"weaknesses\": [...],
  \"recommendations\": [...],
  \"learning_pace\": \"...\"
}";

        $response = $this->chat($prompt);

        try {
            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to parse analysis JSON', ['response' => $response, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Base chat method
     */
    protected function chat(string $prompt, array $options = []): string
    {
        try {
            $result = OpenAI::chat()->create([
                'model' => $options['model'] ?? $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                'temperature' => $options['temperature'] ?? $this->temperature,
            ]);

            return $result->choices[0]->message->content ?? '';
        } catch (\Exception $e) {
            Log::error('OpenAI API error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
