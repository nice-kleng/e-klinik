<?php

return [
    'cons_id' => env('BPJS_CONS_ID', ''),
    'secret_key' => env('BPJS_SECRET_KEY', ''),
    'base_url' => env('BPJS_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id'),
    'user_key' => env('BPJS_USER_KEY', ''),

    'vclaim' => [
        'base_url' => env('BPJS_VCLAIM_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest'),
        'peserta' => '/Peserta',
        'sep' => '/SEP',
        'claim' => '/Claim',
        'referensi' => '/referensi',
    ],

    'antrol' => [
        'base_url' => env('BPJS_ANTROL_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs'),
        'antrean' => '/antrean',
        'jadwal' => '/jadwal',
        'dashboard' => '/dashboard',
    ],

    'aplicares' => [
        'base_url' => env('BPJS_APLICARES_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/aplicares'),
        'referensi' => '/ref',
        'faskes' => '/faskes',
    ],
];
