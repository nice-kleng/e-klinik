<?php

namespace App\Services\SatuSehat;

use App\Exceptions\SatuSehat\SatuSehatException;
use Illuminate\Support\Facades\Log;

class TerminologyService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function searchIcd10(string $keyword): ?array
    {
        try {
            return $this->client->get('CodeSystem/$search', [
                'system' => 'http://hl7.org/fhir/sid/icd-10',
                'filter' => $keyword,
            ]);
        } catch (SatuSehatException $e) {
            Log::warning('ICD-10 search failed', ['keyword' => $keyword, 'error' => $e->getMessage()]);

            return $this->searchLocalIcd10($keyword);
        }
    }

    public function searchLoinc(string $keyword): ?array
    {
        try {
            return $this->client->get('CodeSystem/$search', [
                'system' => 'http://loinc.org',
                'filter' => $keyword,
            ]);
        } catch (SatuSehatException $e) {
            Log::warning('LOINC search failed', ['keyword' => $keyword, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function searchKfa(string $keyword): ?array
    {
        try {
            return $this->client->get('CodeSystem/$search', [
                'system' => 'http://sys-ids.kemkes.go.id/kfa',
                'filter' => $keyword,
            ]);
        } catch (SatuSehatException $e) {
            Log::warning('KFA search failed', ['keyword' => $keyword, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function getIcd10(string $code): ?array
    {
        try {
            return $this->client->get('CodeSystem/$lookup', [
                'system' => 'http://hl7.org/fhir/sid/icd-10',
                'code' => $code,
            ]);
        } catch (SatuSehatException $e) {
            Log::warning('ICD-10 lookup failed', ['code' => $code, 'error' => $e->getMessage()]);

            $local = \App\Models\Icd10Diagnosis::where('code', $code)->first();
            if ($local) {
                return [
                    'resourceType' => 'Parameters',
                    'parameter' => [
                        ['name' => 'name', 'valueString' => $local->name],
                        ['name' => 'code', 'valueString' => $local->code],
                        ['name' => 'system', 'valueString' => 'http://hl7.org/fhir/sid/icd-10'],
                    ],
                ];
            }

            return null;
        }
    }

    public function getLoinc(string $code): ?array
    {
        try {
            return $this->client->get('CodeSystem/$lookup', [
                'system' => 'http://loinc.org',
                'code' => $code,
            ]);
        } catch (SatuSehatException $e) {
            Log::warning('LOINC lookup failed', ['code' => $code, 'error' => $e->getMessage()]);

            return null;
        }
    }

    protected function searchLocalIcd10(string $keyword): ?array
    {
        $results = \App\Models\Icd10Diagnosis::where('code', 'like', '%' . $keyword . '%')
            ->orWhere('name', 'like', '%' . $keyword . '%')
            ->where('is_active', true)
            ->limit(20)
            ->get();

        if ($results->isEmpty()) {
            return null;
        }

        return [
            'resourceType' => 'Bundle',
            'type' => 'searchset',
            'total' => $results->count(),
            'entry' => $results->map(function ($item) {
                return [
                    'resource' => [
                        'resourceType' => 'Concept',
                        'code' => $item->code,
                        'display' => $item->name,
                    ],
                ];
            })->toArray(),
        ];
    }
}
