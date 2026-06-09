<?php

namespace App\Services;

class BrowserTtsProvider implements TtsProvider
{
    public function synthesize(string $text, string $language = 'id'): ?string
    {
        return $text;
    }
}
