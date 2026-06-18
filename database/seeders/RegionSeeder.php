<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->fetchFromSatusehat()) {
            $this->command->info('Regions seeded from Satu Sehat API.');

            return;
        }

        $this->seedFromJson();
        $this->command->info('Regions seeded from JSON fallback.');
    }

    protected function fetchFromSatusehat(): bool
    {
        try {
            $baseUrl = config('satusehat.base_url');
            $token = app(\App\Services\SatuSehat\AuthService::class)->getAccessToken();
            $orgId = config('satusehat.organization_id');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-organization-id' => $orgId,
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$baseUrl}/Location", [
                '_count' => 1,
            ]);

            if (!$response->successful()) {
                return false;
            }

            // TODO: fetch actual KEMENDAGRI CodeSystem from Satu Sehat
            // Endpoint pattern: CodeSystem/$search?system=http://kemendagri.go.id/code/wilayah
            // For now, fallback to JSON
            return false;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch regions from Satu Sehat API', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function seedFromJson(): void
    {
        $jsonPath = database_path('seeders/data/regions.json');

        if (!file_exists($jsonPath)) {
            $this->command->warn('Regions JSON file not found. Skipping.');

            return;
        }

        $regions = json_decode(file_get_contents($jsonPath), true);

        if (empty($regions)) {
            return;
        }

        $codeToId = [];

        foreach ($regions as $region) {
            $id = Region::create([
                'code' => $region['code'],
                'name' => $region['name'],
                'level' => $region['level'],
                'parent_id' => $region['parent_code']
                    ? ($codeToId[$region['parent_code']] ?? null)
                    : null,
            ])->id;

            $codeToId[$region['code']] = $id;
        }
    }
}
