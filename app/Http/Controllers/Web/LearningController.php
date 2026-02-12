<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LessonSegment;
use App\Models\Lesson;
use App\Services\AI\LLMService;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    public function __construct(
        protected LLMService $llmService
    ) {
    }

    /**
     * Get segment details (for AJAX)
     * GET /learning/segments/{id}
     */
    public function getSegment(int $id)
    {
        $segment = LessonSegment::with(['questions', 'lesson'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'segment' => [
                    'id' => $segment->id,
                    'order' => $segment->order,
                    'content' => $segment->content,
                    'ai_explanation' => $segment->ai_explanation,
                    'audio_url' => $segment->audio_url,
                    'questions' => $segment->questions->map(function ($q) {
                        return [
                            'id' => $q->id,
                            'question_text' => $q->question,
                            'type' => $q->type,
                            'options' => $q->options,
                            'difficulty' => $q->difficulty,
                        ];
                    }),
                ],
            ],
        ]);
    }

    /**
     * Chat with AI (for AJAX)
     * POST /learning/chat
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'lesson_id' => 'required|exists:lessons,id',
            'segment_id' => 'nullable|exists:lesson_segments,id',
        ]);

        try {
            $lesson = Lesson::findOrFail($request->lesson_id);
            $segment = $request->segment_id
                ? LessonSegment::findOrFail($request->segment_id)
                : null;

            // Build context for AI
            $context = "Bài học: {$lesson->title}\n";
            $context .= "Môn học: {$lesson->subject}\n";
            $context .= "Cấp độ: {$lesson->level}\n\n";

            if ($segment) {
                $context .= "Nội dung phần học hiện tại:\n";
                $context .= $segment->ai_explanation ?? $segment->content;
                $context .= "\n\n";
            }

            $context .= "Câu hỏi của học sinh: {$request->message}\n\n";
            $context .= "Hãy trả lời câu hỏi một cách rõ ràng, dễ hiểu và khuyến khích học sinh. Sử dụng ví dụ cụ thể nếu cần.";

            // Call LLM Service
            $response = $this->llmService->chat($context);

            return response()->json([
                'success' => true,
                'response' => $response['content'] ?? $response,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kết nối với AI. Vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit answer (for AJAX)
     * POST /learning/answer
     */
    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:lesson_questions,id',
            'answer' => 'required|string',
            'segment_id' => 'required|exists:lesson_segments,id',
        ]);

        try {
            $question = \App\Models\LessonQuestion::findOrFail($request->question_id);

            // Simple answer checking
            $isCorrect = false;
            $feedback = '';

            if ($question->type === 'multiple_choice') {
                $isCorrect = trim($request->answer) === trim($question->correct_answer);
                $feedback = $isCorrect
                    ? "Chính xác! " . ($question->explanation ?? "Bạn đã trả lời đúng.")
                    : "Chưa chính xác. " . ($question->explanation ?? "Hãy thử lại.");
            } else {
                // Use AI to evaluate open-ended answers
                $evaluation = $this->llmService->evaluateAnswer(
                    $question->question,
                    $question->correct_answer,
                    $request->answer
                );

                $isCorrect = $evaluation['is_correct'] ?? false;
                $feedback = $evaluation['feedback'] ?? 'Đã nhận câu trả lời của bạn.';
            }

            return response()->json([
                'success' => true,
                'is_correct' => $isCorrect,
                'feedback' => $feedback,
                'points_earned' => $isCorrect ? ($question->points ?? 1) : 0,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xử lý câu trả lời. Vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
