<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BpjsSignature
{
    public static function generate(string $method, string $path, string $consumerId, string $secretKey, ?string $timestamp = null): string
    {
        $timestamp = $timestamp ?? self::timestamp();
        $signature = hash_hmac('sha256', $consumerId . '&' . $timestamp, $secretKey, true);

        return base64_encode($signature);
    }

    public static function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function headers(string $method, string $path, string $consumerId, string $secretKey, string $userKey): array
    {
        $timestamp = self::timestamp();
        $signature = self::generate($method, $path, $consumerId, $secretKey, $timestamp);

        return [
            'X-cons-id' => $consumerId,
            'X-timestamp' => $timestamp,
            'X-signature' => $signature,
            'X-authorization' => "Bearer {$userKey}",
            'Content-Type' => 'application/json',
        ];
    }

    public static function decryptResponse(string $encryptedData, string $consumerId, string $secretKey, string $timestamp): ?string
    {
        try {
            $key = md5($consumerId . $secretKey . $timestamp);
            $decrypted = openssl_decrypt(
                base64_decode($encryptedData),
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                substr($key, 0, 16)
            );

            return $decrypted ? json_decode($decrypted, true) : null;
        } catch (\Exception $e) {
            Log::error('BPJS decrypt failed: ' . $e->getMessage());
            return null;
        }
    }
}
