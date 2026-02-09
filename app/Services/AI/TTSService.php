<?php

namespace App\Services\AI;

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TTSService
{
    protected TextToSpeechClient $client;
    protected string $languageCode;
    protected string $voiceName;
    protected float $speakingRate;
    protected float $pitch;

    public function __construct()
    {
        $this->client = new TextToSpeechClient([
            'credentials' => config('ai_tutor.google_tts.credentials_path')
        ]);

        $this->languageCode = config('ai_tutor.google_tts.language_code', 'vi-VN');
        $this->voiceName = config('ai_tutor.google_tts.voice_name', 'vi-VN-Wavenet-A');
        $this->speakingRate = config('ai_tutor.google_tts.speaking_rate', 1.0);
        $this->pitch = config('ai_tutor.google_tts.pitch', 0.0);
    }

    /**
     * Generate audio from text
     * 
     * @param string $text Text to convert to speech
     * @param array $options Override default options
     * @return array ['url' => 's3_url', 'duration' => seconds]
     */
    public function generateAudio(string $text, array $options = []): array
    {
        // Prepare synthesis input
        $input = new SynthesisInput();
        $input->setText($text);

        // Configure voice
        $voice = new VoiceSelectionParams();
        $voice->setLanguageCode($options['language_code'] ?? $this->languageCode);
        $voice->setName($options['voice_name'] ?? $this->voiceName);

        // Configure audio
        $audioConfig = new AudioConfig();
        $audioConfig->setAudioEncoding(AudioEncoding::MP3);
        $audioConfig->setSpeakingRate($options['speaking_rate'] ?? $this->speakingRate);
        $audioConfig->setPitch($options['pitch'] ?? $this->pitch);

        // Perform text-to-speech
        $response = $this->client->synthesizeSpeech($input, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();

        // Generate unique filename
        $filename = 'audio/' . Str::uuid() . '.mp3';

        // Upload to S3
        Storage::disk('s3')->put($filename, $audioContent, 'public');
        $url = Storage::disk('s3')->url($filename);

        // Estimate duration (rough estimate: ~150 words per minute)
        $wordCount = str_word_count($text);
        $duration = ceil(($wordCount / 150) * 60); // seconds

        return [
            'url' => $url,
            'duration' => $duration,
            'filename' => $filename,
        ];
    }

    /**
     * Generate audio for lesson segment
     */
    public function generateSegmentAudio(string $content, string $explanation): array
    {
        $fullText = $content . "\n\n" . $explanation;
        return $this->generateAudio($fullText);
    }

    /**
     * Generate audio for question
     */
    public function generateQuestionAudio(string $question, array $options = []): array
    {
        return $this->generateAudio($question, $options);
    }

    /**
     * Generate audio for feedback
     */
    public function generateFeedbackAudio(string $feedback): array
    {
        return $this->generateAudio($feedback);
    }

    /**
     * Delete audio file from S3
     */
    public function deleteAudio(string $filename): bool
    {
        return Storage::disk('s3')->delete($filename);
    }
}
