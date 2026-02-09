<?php

namespace App\Jobs;

use App\Models\Lesson;
use App\Services\Lesson\LessonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessLessonContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Lesson $lesson
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(LessonService $lessonService): void
    {
        try {
            Log::info('Processing lesson content', ['lesson_id' => $this->lesson->id]);

            $lessonService->processContent($this->lesson);

            Log::info('Lesson processed successfully', ['lesson_id' => $this->lesson->id]);

        } catch (\Exception $e) {
            Log::error('Failed to process lesson', [
                'lesson_id' => $this->lesson->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->lesson->update([
                'status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Lesson processing job failed permanently', [
            'lesson_id' => $this->lesson->id,
            'error' => $exception->getMessage(),
        ]);

        $this->lesson->update([
            'status' => 'failed',
            'processing_error' => 'Processing failed after multiple attempts: ' . $exception->getMessage(),
        ]);
    }
}
