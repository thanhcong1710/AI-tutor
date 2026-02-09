<?php

namespace App\Services\Lesson;

use App\Models\LessonSegment;
use App\Models\LessonQuestion;
use App\Services\AI\LLMService;

class QuestionGenerator
{
    public function __construct(
        protected LLMService $llmService
    ) {}

    /**
     * Generate questions for a lesson segment
     */
    public function generateForSegment(
        LessonSegment $segment,
        int $questionCount = 3,
        string $difficulty = 'medium'
    ): array {
        $content = $segment->content . "\n\n" . $segment->ai_explanation;

        // Generate questions using LLM
        $questions = $this->llmService->generateQuestions(
            $content,
            $questionCount,
            $difficulty
        );

        $createdQuestions = [];

        foreach ($questions as $index => $questionData) {
            $createdQuestions[] = $this->createQuestion($segment, $questionData, $index);
        }

        return $createdQuestions;
    }

    /**
     * Create a question record
     */
    protected function createQuestion(
        LessonSegment $segment,
        array $data,
        int $order
    ): LessonQuestion {
        return LessonQuestion::create([
            'lesson_segment_id' => $segment->id,
            'order' => $order,
            'type' => $data['type'] ?? 'multiple_choice',
            'question' => $data['question'],
            'options' => $data['options'] ?? null,
            'correct_answer' => $data['correct_answer'],
            'explanation' => $data['explanation'] ?? null,
            'points' => $this->calculatePoints($data['type'] ?? 'multiple_choice'),
            'difficulty' => $this->determineDifficulty($data),
        ]);
    }

    /**
     * Calculate points based on question type
     */
    protected function calculatePoints(string $type): int
    {
        return match($type) {
            'multiple_choice' => 1,
            'true_false' => 1,
            'short_answer' => 2,
            'essay' => 3,
            default => 1,
        };
    }

    /**
     * Determine difficulty from question data
     */
    protected function determineDifficulty(array $data): string
    {
        // If difficulty is provided, use it
        if (isset($data['difficulty'])) {
            return $data['difficulty'];
        }

        // Otherwise, determine from question length and complexity
        $questionLength = str_word_count($data['question']);
        
        if ($questionLength < 10) {
            return 'easy';
        } elseif ($questionLength < 20) {
            return 'medium';
        } else {
            return 'hard';
        }
    }

    /**
     * Generate adaptive questions based on student performance
     */
    public function generateAdaptiveQuestions(
        LessonSegment $segment,
        array $studentPerformance
    ): array {
        $averageScore = $studentPerformance['average_score'] ?? 50;

        // Adjust difficulty based on performance
        $difficulty = match(true) {
            $averageScore >= 80 => 'hard',
            $averageScore >= 60 => 'medium',
            default => 'easy',
        };

        return $this->generateForSegment($segment, 3, $difficulty);
    }
}
