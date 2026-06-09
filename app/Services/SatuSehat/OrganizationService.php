<?php

namespace App\Services\SatuSehat;

use App\Exceptions\SatuSehat\SatuSehatException;
use Illuminate\Support\Facades\Log;

class OrganizationService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function getOrganization(?string $orgId = null): ?array
    {
        $orgId = $orgId ?? config('satusehat.organization_id');

        if (empty($orgId)) {
            throw new SatuSehatException('Organization ID is not configured');
        }

        return $this->client->getResourceById('Organization', $orgId);
    }

    public function searchOrganization(string $name): ?array
    {
        return $this->client->get('Organization', ['name' => $name]);
    }

    public function validateOrganization(): array
    {
        $orgId = config('satusehat.organization_id');
        $result = [
            'valid' => false,
            'organization' => null,
            'message' => '',
        ];

        if (empty($orgId)) {
            $result['message'] = 'Organization ID is not configured in SATUSEHAT_ORGANIZATION_ID';

            return $result;
        }

        try {
            $response = $this->getOrganization($orgId);

            if ($response && isset($response['id'])) {
                $result['valid'] = true;
                $result['organization'] = [
                    'id' => $response['id'],
                    'name' => $response['name'] ?? '',
                    'type' => $response['type'][0]['coding'][0]['display'] ?? '',
                    'active' => $response['active'] ?? false,
                ];
                $result['message'] = 'Organization is valid';

                Log::info('Satu Sehat organization validated successfully', [
                    'org_id' => $orgId,
                    'name' => $response['name'] ?? '',
                ]);
            } else {
                $result['message'] = 'Organization not found with ID: ' . $orgId;
            }
        } catch (\Exception $e) {
            $result['message'] = 'Failed to validate organization: ' . $e->getMessage();

            Log::error('Satu Sehat organization validation failed', [
                'org_id' => $orgId,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }
}
