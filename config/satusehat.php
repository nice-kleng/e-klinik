<?php

return [
    'base_url' => env('SATUSEHAT_BASE_URL', 'https://api.satusehat.kemkes.go.id/fhir-r4/v1'),
    'auth_url' => env('SATUSEHAT_AUTH_URL', 'https://api-satusehat.kemkes.go.id/oauth2/v1'),
    'client_id' => env('SATUSEHAT_CLIENT_ID', ''),
    'client_secret' => env('SATUSEHAT_CLIENT_SECRET', ''),
    'organization_id' => env('SATUSEHAT_ORGANIZATION_ID', ''),
    'kyc_endpoint' => env('SATUSEHAT_KYC_URL', ''),
    'timeout' => env('SATUSEHAT_TIMEOUT', 30),
];
