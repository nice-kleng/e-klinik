<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pharmacy Configuration
    |--------------------------------------------------------------------------
    |
    | Global settings for pharmacy module including auto-calculation
    | and compounding fees (tuslah & embalase).
    |
    */

    'auto_calc' => env('PHARMACY_AUTO_CALC', true),

    'tuslah' => env('PHARMACY_TUSLAH', 3000),

    'embalase' => env('PHARMACY_EMBALASE', 1000),
];
