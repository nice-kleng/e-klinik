<?php

namespace App\Services;

interface TtsProvider
{
    public function synthesize(string $text, string $language = 'id'): ?string;
}
