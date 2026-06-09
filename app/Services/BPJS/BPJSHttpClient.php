<?php

namespace App\Services\BPJS;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BPJSHttpClient
{
    protected Client $client;
    protected string $consId;
    protected string $secretKey;
    protected string $baseUrl;
    protected string $userKey;

    public function __construct()
    {
        $this->consId = config('bpjs.cons_id');
        $this->secretKey = config('bpjs.secret_key');
        $this->baseUrl = config('bpjs.base_url');
        $this->userKey = config('bpjs.user_key');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => false,
        ]);
    }

    protected function generateSignature(): array
    {
        $timestamp = now()->timestamp * 1000;
        $signature = hash_hmac('sha256', $this->consId . '&' . $timestamp, $this->secretKey, true);
        $encodedSignature = base64_encode($signature);

        return [
            'X-cons-id' => $this->consId,
            'X-timestamp' => $timestamp,
            'X-signature' => $encodedSignature,
        ];
    }

    protected function decryptResponse(string $response, string $consId, string $timestamp, string $signature): ?string
    {
        $key = $this->consId . $this->secretKey . $timestamp;
        $decrypted = openssl_decrypt(
            base64_decode($response),
            'AES-256-CBC',
            substr(hash('sha256', $key, true), 0, 32),
            OPENSSL_RAW_DATA,
            substr(hash('sha256', $consId . $signature, true), 0, 16)
        );

        return $decrypted !== false ? $decrypted : null;
    }

    protected function buildHeaders(): array
    {
        $headers = $this->generateSignature();
        $headers['X-authorization'] = 'Bearer ' . base64_encode($this->userKey . ':' . $this->userKey);
        $headers['Content-Type'] = 'application/json';

        return $headers;
    }

    protected function processResponse($response): ?array
    {
        $body = json_decode($response->getBody()->getContents(), true);

        if (isset($body['response']) && is_string($body['response'])) {
            $headers = $this->generateSignature();
            $decrypted = $this->decryptResponse(
                $body['response'],
                $this->consId,
                $headers['X-timestamp'],
                $headers['X-signature']
            );
            if ($decrypted) {
                $body['response'] = json_decode($decrypted, true);
            }
        }

        return $body;
    }

    protected function logError(\Exception $e, string $endpoint, array $context = []): void
    {
        Log::error('BPJS HTTP Client Error: ' . $e->getMessage(), [
            'endpoint' => $endpoint,
            'context' => $context,
        ]);
    }

    public function get(string $endpoint, array $params = []): ?array
    {
        try {
            $response = $this->client->get($endpoint, [
                'headers' => $this->buildHeaders(),
                'query' => $params,
            ]);

            return $this->processResponse($response);
        } catch (\Exception $e) {
            $this->logError($e, $endpoint, ['params' => $params]);
            throw $e;
        }
    }

    public function post(string $endpoint, array $data = []): ?array
    {
        try {
            $response = $this->client->post($endpoint, [
                'headers' => $this->buildHeaders(),
                'json' => $data,
            ]);

            return $this->processResponse($response);
        } catch (\Exception $e) {
            $this->logError($e, $endpoint, ['data' => $data]);
            throw $e;
        }
    }

    public function put(string $endpoint, array $data = []): ?array
    {
        try {
            $response = $this->client->put($endpoint, [
                'headers' => $this->buildHeaders(),
                'json' => $data,
            ]);

            return $this->processResponse($response);
        } catch (\Exception $e) {
            $this->logError($e, $endpoint, ['data' => $data]);
            throw $e;
        }
    }

    public function delete(string $endpoint, array $data = []): ?array
    {
        try {
            $response = $this->client->delete($endpoint, [
                'headers' => $this->buildHeaders(),
                'json' => $data,
            ]);

            return $this->processResponse($response);
        } catch (\Exception $e) {
            $this->logError($e, $endpoint, ['data' => $data]);
            throw $e;
        }
    }
}
