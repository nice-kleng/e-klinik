<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCloudTtsProvider implements TtsProvider
{
    protected string $apiKey;
    protected string $apiUrl = 'https://texttospeech.googleapis.com/v1/text:synthesize';

    public function __construct()
    {
        $this->apiKey = config('services.google_tts.api_key', '');
    }

    public function synthesize(string $text, string $language = 'id'): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Cloud TTS API key not configured');
            return null;
        }

        $payload = [
            'input' => ['text' => $text],
            'voice' => [
                'languageCode' => $language === 'id' ? 'id-ID' : $language,
                'name' => $language === 'id' ? 'id-ID-Wavenet-A' : 'en-US-Wavenet-D',
                'ssmlGender' => 'FEMALE',
            ],
            'audioConfig' => [
                'audioEncoding' => 'MP3',
                'speakingRate' => 1.0,
                'pitch' => 0.0,
                'volumeGainDb' => 0.0,
            ],
        ];

        try {
            $response = Http::timeout(15)
                ->withHeaders(['X-Goog-User-Project' => config('services.google_tts.project_id', '')])
                ->post("{$this->apiUrl}?key={$this->apiKey}", $payload);

            if ($response->successful()) {
                return $response->json('audioContent');
            }

            Log::error('Google Cloud TTS synthesis failed', [
                'status' => $response->status(),
                'error' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Google Cloud TTS exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
