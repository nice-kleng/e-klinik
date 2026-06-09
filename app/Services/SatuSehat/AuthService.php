<?php

namespace App\Services\SatuSehat;

use App\Exceptions\SatuSehat\SatuSehatException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected Client $client;
    protected string $authUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->authUrl = config('satusehat.auth_url');
        $this->clientId = config('satusehat.client_id');
        $this->clientSecret = config('satusehat.client_secret');

        $this->client = new Client([
            'base_uri' => $this->authUrl,
            'timeout' => config('satusehat.timeout', 30),
            'verify' => false,
        ]);
    }

    public function getAccessToken(): string
    {
        $cacheKey = 'satusehat_access_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        return $this->authenticate();
    }

    public function refreshToken(): string
    {
        Cache::forget('satusehat_access_token');

        return $this->authenticate();
    }

    protected function authenticate(): string
    {
        try {
            $response = $this->client->post('accesstoken', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (!isset($body['access_token'])) {
                throw new SatuSehatException(
                    'Failed to retrieve access token from Satu Sehat',
                    401,
                    null,
                    ['response' => $body]
                );
            }

            $expiresIn = $body['expires_in'] ?? 3600;

            Cache::put('satusehat_access_token', $body['access_token'], now()->addSeconds($expiresIn - 60));

            Log::info('Satu Sehat access token retrieved successfully', [
                'expires_in' => $expiresIn,
            ]);

            return $body['access_token'];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            Log::error('Satu Sehat auth client error: ' . $e->getMessage(), [
                'response' => $responseBody,
            ]);
            throw new SatuSehatException(
                'Satu Sehat authentication failed: ' . $e->getMessage(),
                $e->getCode(),
                $e,
                ['response' => json_decode($responseBody, true)]
            );
        } catch (\Exception $e) {
            Log::error('Satu Sehat auth error: ' . $e->getMessage());
            throw new SatuSehatException(
                'Satu Sehat authentication error: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
