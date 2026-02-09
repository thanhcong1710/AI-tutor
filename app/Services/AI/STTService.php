<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class STTService
{
    protected string $model;

    public function __construct()
    {
        $this->model = config('ai_tutor.whisper.model', 'whisper-1');
    }

    /**
     * Transcribe audio to text
     * 
     * @param string $audioPath Path to audio file (local or S3)
     * @param string $language Language code (e.g., 'vi', 'en')
     * @return array ['text' => transcribed_text, 'confidence' => score]
     */
    public function transcribe(string $audioPath, string $language = 'vi'): array
    {
        try {
            // Download from S3 if needed
            $localPath = $this->ensureLocalFile($audioPath);

            // Call Whisper API
            $response = OpenAI::audio()->transcribe([
                'model' => $this->model,
                'file' => fopen($localPath, 'r'),
                'language' => $language,
                'response_format' => 'verbose_json',
            ]);

            // Clean up temp file if downloaded from S3
            if ($localPath !== $audioPath) {
                unlink($localPath);
            }

            return [
                'text' => $response->text ?? '',
                'language' => $response->language ?? $language,
                'duration' => $response->duration ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Whisper STT error', [
                'audio_path' => $audioPath,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Transcribe student answer audio
     */
    public function transcribeStudentAnswer(string $audioUrl): string
    {
        $result = $this->transcribe($audioUrl);
        return $result['text'] ?? '';
    }

    /**
     * Ensure file is available locally
     */
    protected function ensureLocalFile(string $path): string
    {
        // If it's a URL, download it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $tempPath = sys_get_temp_dir() . '/' . uniqid('audio_') . '.mp3';
            file_put_contents($tempPath, file_get_contents($path));
            return $tempPath;
        }

        // If it's an S3 path
        if (str_starts_with($path, 's3://') || str_starts_with($path, 'audio/')) {
            $tempPath = sys_get_temp_dir() . '/' . uniqid('audio_') . '.mp3';
            $content = Storage::disk('s3')->get($path);
            file_put_contents($tempPath, $content);
            return $tempPath;
        }

        // Already a local file
        return $path;
    }
}
