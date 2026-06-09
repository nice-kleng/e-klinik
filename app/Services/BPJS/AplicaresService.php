<?php

namespace App\Services\BPJS;

class AplicaresService
{
    protected BPJSHttpClient $client;
    protected string $baseUrl;

    public function __construct(BPJSHttpClient $client)
    {
        $this->client = $client;
        $this->baseUrl = config('bpjs.aplicares.base_url');
    }

    /**
     * Get referral facility information.
     */
    public function getReferensiFaskes(string $kodeFaskes, string $jenisFaskes): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.aplicares.referensi') . '/faskes/' . $kodeFaskes . '/jenis/' . $jenisFaskes;
        return $this->client->get($endpoint);
    }

    /**
     * Get polyclinics in a healthcare facility.
     */
    public function getPoliFaskes(string $kodeFaskes): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.aplicares.faskes') . '/poli/' . $kodeFaskes;
        return $this->client->get($endpoint);
    }

    /**
     * Get doctors in a healthcare facility.
     */
    public function getDokterFaskes(string $kodeFaskes): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.aplicares.faskes') . '/dokter/' . $kodeFaskes;
        return $this->client->get($endpoint);
    }

    /**
     * Get referral list by patient card number and date range.
     */
    public function getReferensiPasien(string $noKartu, string $tglMulai, string $tglAkhir): ?array
    {
        $endpoint = $this->baseUrl . '/referensi/pasien/' . $noKartu . '/tglMulai/' . $tglMulai . '/tglAkhir/' . $tglAkhir;
        return $this->client->get($endpoint);
    }

    /**
     * Get referral list by referral number.
     */
    public function getReferensiByNo(string $noReferensi): ?array
    {
        $endpoint = $this->baseUrl . '/referensi/noReferensi/' . $noReferensi;
        return $this->client->get($endpoint);
    }

    /**
     * Get list of healthcare facilities by filter.
     */
    public function getFaskesList(string $jenisFaskes = null): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.aplicares.faskes') . '/list';
        $params = [];
        if ($jenisFaskes) {
            $params['jenisFaskes'] = $jenisFaskes;
        }
        return $this->client->get($endpoint, $params);
    }
}
