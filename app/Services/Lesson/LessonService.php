<?php

namespace App\Services\Lesson;

use App\Models\Lesson;
use App\Models\LessonSegment;
use App\Services\AI\LLMService;
use App\Services\AI\TTSService;
use App\Jobs\ProcessLessonContent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class LessonService
{
    public function __construct(
        protected LLMService $llmService,
        protected TTSService $ttsService,
        protected ContentProcessor $contentProcessor,
        protected QuestionGenerator $questionGenerator
    ) {
    }

    /**
     * Create lesson from uploaded file
     */
    public function createFromFile(
        int $teacherId,
        UploadedFile $file,
        array $data
    ): Lesson {
        // Upload file to S3
        $path = $file->store('lessons', 's3');

        // Create lesson record
        $lesson = Lesson::create([
            'teacher_id' => $teacherId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'subject' => $data['subject'],
            'level' => $data['level'],
            'estimated_duration' => $data['estimated_duration'] ?? 30,
            'original_file_path' => $path,
            'original_file_type' => $file->getClientOriginalExtension(),
            'status' => 'processing',
        ]);

        // Dispatch job to process content
        ProcessLessonContent::dispatch($lesson);

        return $lesson;
    }

    /**
     * Process lesson content (called by job)
     */
    public function processContent(Lesson $lesson): void
    {
        try {
            // Extract text from file
            $content = $this->contentProcessor->extractText(
                $lesson->original_file_path,
                $lesson->original_file_type
            );

            // Update lesson with extracted content
            $lesson->update([
                'content' => $content,
                'metadata' => [
                    'word_count' => str_word_count($content),
                    'processed_at' => now()->toISOString(),
                ],
            ]);

            // Break content into segments
            $segments = $this->contentProcessor->breakIntoSegments($content);

            // Create segments with AI explanations
            foreach ($segments as $index => $segmentData) {
                $this->createSegment($lesson, $segmentData, $index);
            }

            // Mark as ready
            $lesson->update([
                'status' => 'ready',
                'published_at' => now(),
            ]);

        } catch (\Exception $e) {
            $lesson->update([
                'status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create lesson segment with AI explanation and audio
     */
    protected function createSegment(Lesson $lesson, array $data, int $order): LessonSegment
    {
        // Generate AI explanation
        $explanation = $this->llmService->generateExplanation(
            $data['content'],
            $lesson->subject,
            $lesson->level
        );

        // Create segment
        $segment = LessonSegment::create([
            'lesson_id' => $lesson->id,
            'order' => $order,
            'title' => $data['title'],
            'content' => $data['content'],
            'ai_explanation' => $explanation,
        ]);

        // Generate audio (async via job would be better for production)
        try {
            $audioData = $this->ttsService->generateSegmentAudio(
                $data['content'],
                $explanation
            );

            $segment->update([
                'audio_url' => $audioData['url'],
                'audio_duration' => $audioData['duration'],
            ]);
        } catch (\Exception $e) {
            // Audio generation failed, but segment is still usable
            \Log::warning('Failed to generate audio for segment', [
                'segment_id' => $segment->id,
                'error' => $e->getMessage()
            ]);
        }

        // Generate questions for this segment
        $this->questionGenerator->generateForSegment($segment);

        return $segment;
    }

    /**
     * Get lesson with all segments and questions
     */
    public function getFullLesson(int $lessonId): Lesson
    {
        return Lesson::with([
            'segments.questions',
            'teacher:id,name,email'
        ])->findOrFail($lessonId);
    }

    /**
     * Update lesson
     */
    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);
        return $lesson->fresh();
    }

    /**
     * Delete lesson
     */
    public function delete(Lesson $lesson): bool
    {
        // Delete file from S3
        if ($lesson->original_file_path) {
            Storage::disk('s3')->delete($lesson->original_file_path);
        }

        // Delete segment audios
        foreach ($lesson->segments as $segment) {
            if ($segment->audio_url) {
                $this->ttsService->deleteAudio($segment->audio_url);
            }
        }

        return $lesson->delete();
    }

    /**
     * Get lessons for teacher
     */
    public function getTeacherLessons(int $teacherId, array $filters = [])
    {
        $query = Lesson::where('teacher_id', $teacherId);

        if (isset($filters['subject'])) {
            $query->where('subject', $filters['subject']);
        }

        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with('segments')->latest()->paginate(20);
    }
}
