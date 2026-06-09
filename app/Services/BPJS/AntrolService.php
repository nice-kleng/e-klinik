<?php

namespace App\Services\BPJS;

class AntrolService
{
    protected BPJSHttpClient $client;
    protected string $baseUrl;

    public function __construct(BPJSHttpClient $client)
    {
        $this->client = $client;
        $this->baseUrl = config('bpjs.antrol.base_url');
    }

    /**
     * Add a new queue (antrean) to BPJS.
     */
    public function addAntrean(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.antrean') . '/antrean';
        return $this->client->post($endpoint, $data);
    }

    /**
     * Update an existing queue entry.
     */
    public function updateAntrean(array $data): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.antrean') . '/antrean';
        return $this->client->put($endpoint, $data);
    }

    /**
     * Delete a queue by its antrean number and date.
     */
    public function deleteAntrean(string $noAntrean): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.antrean') . '/antrean/' . $noAntrean;
        return $this->client->delete($endpoint);
    }

    /**
     * Get queue list by polyclinic and date.
     */
    public function getAntreanPerPoli(string $kodePoli, string $tanggal): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.antrean') . '/antrean/' . $kodePoli . '/tanggal/' . $tanggal;
        return $this->client->get($endpoint);
    }

    /**
     * Get daily dashboard data.
     */
    public function getDashboardPerTanggal(string $tanggal): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.dashboard') . '/tanggal/' . $tanggal;
        return $this->client->get($endpoint);
    }

    /**
     * Get monthly dashboard data.
     */
    public function getDashboardPerBulan(string $bulan, string $tahun): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.dashboard') . '/bulan/' . $bulan . '/tahun/' . $tahun;
        return $this->client->get($endpoint);
    }

    /**
     * Get polyclinic surgery schedule.
     */
    public function getJadwalOperasiPoli(string $kodePoli, string $tanggal): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.jadwal') . '/operasi/poli/' . $kodePoli . '/tanggal/' . $tanggal;
        return $this->client->get($endpoint);
    }

    /**
     * Get queue status by no antrean.
     */
    public function getAntreanByNo(string $noAntrean): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.antrean') . '/status/' . $noAntrean;
        return $this->client->get($endpoint);
    }

    /**
     * Get estimated queue time by polyclinic and date.
     */
    public function getEstimasiWaktu(string $kodePoli, string $tanggal): ?array
    {
        $endpoint = $this->baseUrl . '/antrean/pendaftaran/waktu/' . $kodePoli . '/tanggal/' . $tanggal;
        return $this->client->get($endpoint);
    }

    /**
     * Get surgery schedule by doctor and date range.
     */
    public function getJadwalOperasiDokter(string $kodeDokter, string $tglMulai, string $tglAkhir): ?array
    {
        $endpoint = $this->baseUrl . config('bpjs.antrol.jadwal') . '/operasi/dokter/' . $kodeDokter . '/tglMulai/' . $tglMulai . '/tglAkhir/' . $tglAkhir;
        return $this->client->get($endpoint);
    }
}
