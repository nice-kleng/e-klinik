<?php

namespace App\Services\SatuSehat;

use App\Exceptions\SatuSehat\SatuSehatException;
use App\Models\SatusehatLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SatuSehatClient
{
    protected Client $client;
    protected AuthService $authService;
    protected string $baseUrl;
    protected string $organizationId;

    public function __construct()
    {
        $this->baseUrl = config('satusehat.base_url');
        $this->organizationId = config('satusehat.organization_id');
        $this->authService = app(AuthService::class);

        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => config('satusehat.timeout', 30),
            'verify' => false,
        ]);
    }

    protected function buildHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-organization-id' => $this->organizationId,
            'Authorization' => 'Bearer ' . $this->authService->getAccessToken(),
        ];
    }

    protected function logRequest(string $resource, string $action, ?array $requestData, ?array $responseData, string $status, ?string $errorMessage = null): void
    {
        try {
            SatusehatLog::create([
                'resource_type' => $resource,
                'action' => $action,
                'request' => $requestData,
                'response' => $responseData,
                'status' => $status,
                'error_message' => $errorMessage,
                'synced_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to save SatusehatLog: ' . $e->getMessage());
        }
    }

    public function get(string $resource, array $params = []): ?array
    {
        try {
            $response = $this->client->get($resource, [
                'headers' => $this->buildHeaders(),
                'query' => $params,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $this->logRequest($resource, 'GET', $params, $body, 'success');

            return $body;
        } catch (\Exception $e) {
            return $this->handleError($e, $resource, 'GET');
        }
    }

    public function post(string $resource, array $data): ?array
    {
        try {
            $response = $this->client->post($resource, [
                'headers' => $this->buildHeaders(),
                'json' => $data,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $this->logRequest($resource, 'POST', $data, $body, 'success');

            return $body;
        } catch (\Exception $e) {
            return $this->handleError($e, $resource, 'POST', $data);
        }
    }

    public function put(string $resource, array $data): ?array
    {
        try {
            $response = $this->client->put($resource, [
                'headers' => $this->buildHeaders(),
                'json' => $data,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $this->logRequest($resource, 'PUT', $data, $body, 'success');

            return $body;
        } catch (\Exception $e) {
            return $this->handleError($e, $resource, 'PUT', $data);
        }
    }

    public function delete(string $resource): ?array
    {
        try {
            $response = $this->client->delete($resource, [
                'headers' => $this->buildHeaders(),
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $this->logRequest($resource, 'DELETE', null, $body, 'success');

            return $body;
        } catch (\Exception $e) {
            return $this->handleError($e, $resource, 'DELETE');
        }
    }

    public function search(string $resource, array $params): ?array
    {
        return $this->get($resource . '/_search', $params);
    }

    public function getResourceById(string $resource, string $id): ?array
    {
        return $this->get($resource . '/' . $id);
    }

    protected function handleError(\Exception $e, string $resource, string $method, ?array $data = null): ?array
    {
        $responseBody = null;
        $statusCode = $e->getCode();
        $errorMessage = $e->getMessage();

        if ($e instanceof \GuzzleHttp\Exception\ClientException || $e instanceof \GuzzleHttp\Exception\ServerException) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $statusCode = $e->getResponse()->getStatusCode();
            $decodedResponse = json_decode($responseBody, true);

            // Satu Sehat returns 400 with resource id when validation warnings exist but resource is created
            if ($statusCode === 400 && isset($decodedResponse['id'])) {
                Log::warning('Satu Sehat API returned 400 but resource was created', [
                    'resource' => $resource,
                    'method' => $method,
                    'id' => $decodedResponse['id'],
                ]);
                $this->logRequest($resource, $method, $data, $decodedResponse, 'warning', 'Resource created with validation warnings');
                return $decodedResponse;
            }

            $errorMessage = $decodedResponse['message'] ?? $decodedResponse['issue'][0]['details']['text'] ?? $errorMessage;

            if (isset($decodedResponse['resourceType']) && $decodedResponse['resourceType'] === 'OperationOutcome') {
                $issues = collect($decodedResponse['issue'] ?? []);
                $errorMessage = $issues->pluck('details.text')->implode('; ') ?: $errorMessage;
            }
        }

        $this->logRequest($resource, $method, $data, ['error' => $responseBody], 'error', $errorMessage);

        Log::error('Satu Sehat API error', [
            'resource' => $resource,
            'method' => $method,
            'status_code' => $statusCode,
            'message' => $errorMessage,
        ]);

        throw new SatuSehatException(
            'Satu Sehat API error: ' . $errorMessage,
            $statusCode,
            $e,
            [
                'resource' => $resource,
                'method' => $method,
                'response' => $responseBody ? json_decode($responseBody, true) : null,
            ]
        );
    }
}
