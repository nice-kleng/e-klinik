<?php

namespace App\Services\BPJS;

use App\Exceptions\BPJS\BPJSException;

class VClaimService
{
    protected BPJSHttpClient $client;
    protected string $baseUrl;

    public function __construct(BPJSHttpClient $client)
    {
        $this->client = $client;
        $this->baseUrl = config('bpjs.vclaim.base_url');
    }

    public function getPeserta(string $nomorKartu, string $tglPelayanan): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.peserta') . '/nomorKartu/' . $nomorKartu . '/tglPelayanan/' . $tglPelayanan;
        return $this->client->get($endpoint);
    }

    public function getPesertaByNik(string $nik, string $tglPelayanan): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.peserta') . '/nik/' . $nik . '/tglPelayanan/' . $tglPelayanan;
        return $this->client->get($endpoint);
    }

    public function insertSep(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/1.1/insert';
        return $this->client->post($endpoint, $data);
    }

    public function updateSep(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/1.1/Update';
        return $this->client->put($endpoint, $data);
    }

    public function deleteSep(string $noSep): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/SEP/Delete';
        return $this->client->delete($endpoint, [
            'request' => [
                't_sep' => [
                    'noSep' => $noSep,
                ],
            ],
        ]);
    }

    public function getSep(string $noSep): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/' . $noSep;
        return $this->client->get($endpoint);
    }

    public function submitClaim(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.claim') . '/1.1/insert';
        return $this->client->post($endpoint, $data);
    }

    public function updateClaim(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.claim') . '/1.1/Update';
        return $this->client->put($endpoint, $data);
    }

    public function getClaimStatus(string $noSep): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.claim') . '/Status/' . $noSep;
        return $this->client->get($endpoint);
    }

    public function getHistoryPelayanan(string $noKartu): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.peserta') . '/historypelayanan/' . $noKartu;
        return $this->client->get($endpoint);
    }

    public function getDiagnosa(string $code): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.referensi') . '/diagnosa/' . $code;
        return $this->client->get($endpoint);
    }

    public function getPoli(string $kodePoli): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.referensi') . '/poli/' . $kodePoli;
        return $this->client->get($endpoint);
    }

    public function getFaskes(string $kodeFaskes): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.referensi') . '/faskes/' . $kodeFaskes;
        return $this->client->get($endpoint);
    }

    /**
     * Get list of SEPs by patient card number and date.
     */
    public function getSepList(string $noKartu, string $tglMulai, string $tglAkhir): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/peserta/' . $noKartu . '/tglPelayanan/' . $tglMulai . '/' . $tglAkhir;
        return $this->client->get($endpoint);
    }

    /**
     * Get procedure reference.
     */
    public function getProsedur(string $code): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.referensi') . '/prosedur/' . $code;
        return $this->client->get($endpoint);
    }

    /**
     * Get class eligibility (Hak Kelas) data by card number and date.
     */
    public function getHakKelas(string $noKartu, string $tglPelayanan): ?array
    {
        $endpoint = $this->baseUrl . '/HakKelas/' . $noKartu . '/tglPelayanan/' . $tglPelayanan;
        return $this->client->get($endpoint);
    }

    /**
     * Insert SEP with internal mapping (v2.0 format).
     */
    public function insertSepV2(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/2.0/insert';
        return $this->client->post($endpoint, $data);
    }

    /**
     * Update SEP v2.0.
     */
    public function updateSepV2(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/2.0/Update';
        return $this->client->put($endpoint, $data);
    }

    /**
     * Delete SEP v2.0.
     */
    public function deleteSepV2(string $noSep, string $user): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.vclaim.sep') . '/2.0/Delete';
        return $this->client->delete($endpoint, [
            'request' => [
                't_sep' => [
                    'noSep' => $noSep,
                    'user' => $user,
                ],
            ],
        ]);
    }
}
