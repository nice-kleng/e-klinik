<?php

namespace App\Services\SatuSehat;

use App\Models\Polyclinic;
use App\Models\SatusehatResource;
use Illuminate\Support\Facades\Log;

class LocationService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function createLocation(Polyclinic $polyclinic): ?array
    {
        $resource = $this->buildLocationResource($polyclinic);

        $response = $this->client->post('Location', $resource);

        if ($response && isset($response['id'])) {
            $this->saveResourceReference($polyclinic, 'Location', $response['id'], $response);

            Log::info('Location created on Satu Sehat', [
                'polyclinic_id' => $polyclinic->id,
                'ss_id' => $response['id'],
            ]);
        }

        return $response;
    }

    public function getLocation(string $ssId): ?array
    {
        return $this->client->getResourceById('Location', $ssId);
    }

    public function searchLocation(string $name): ?array
    {
        return $this->client->get('Location', ['name' => $name]);
    }

    public function syncLocation(Polyclinic $polyclinic): ?array
    {
        $resourceRef = $this->getResourceReference($polyclinic, 'Location');
        if ($resourceRef && $resourceRef->status === 'synced') {
            return $resourceRef->payload;
        }

        return $this->createLocation($polyclinic);
    }

    public function getLocationSsId(Polyclinic $polyclinic): ?string
    {
        return SatusehatResource::where('model_type', get_class($polyclinic))
            ->where('model_id', $polyclinic->id)
            ->where('resource_type', 'Location')
            ->where('status', 'synced')
            ->value('resource_id_ss');
    }

    protected function buildLocationResource(Polyclinic $polyclinic): array
    {
        $orgId = config('satusehat.organization_id');

        return [
            'resourceType' => 'Location',
            'name' => $polyclinic->name,
            'description' => $polyclinic->description ?? $polyclinic->name,
            'status' => 'active',
            'mode' => 'instance',
            'physicalType' => [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
                        'code' => 'ro',
                        'display' => 'Room',
                    ],
                ],
            ],
            'managingOrganization' => [
                'reference' => 'Organization/' . $orgId,
            ],
        ];
    }

    protected function getResourceReference(Polyclinic $polyclinic, string $resourceType): ?SatusehatResource
    {
        return SatusehatResource::where('model_type', get_class($polyclinic))
            ->where('model_id', $polyclinic->id)
            ->where('resource_type', $resourceType)
            ->first();
    }

    protected function saveResourceReference(Polyclinic $polyclinic, string $resourceType, string $ssId, array $response): SatusehatResource
    {
        $ref = $this->getResourceReference($polyclinic, $resourceType);

        if ($ref) {
            $ref->update([
                'resource_id_ss' => $ssId,
                'version' => $response['meta']['versionId'] ?? 1,
                'payload' => $response,
                'status' => 'synced',
                'sync_at' => now(),
            ]);

            return $ref;
        }

        return SatusehatResource::create([
            'model_type' => get_class($polyclinic),
            'model_id' => $polyclinic->id,
            'resource_type' => $resourceType,
            'resource_id_ss' => $ssId,
            'version' => $response['meta']['versionId'] ?? 1,
            'payload' => $response,
            'status' => 'synced',
            'sync_at' => now(),
        ]);
    }
}
