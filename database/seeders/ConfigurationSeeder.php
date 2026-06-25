<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['group' => 'pharmacy', 'key' => 'auto_calc', 'value' => 'true', 'data_type' => 'boolean'],
            ['group' => 'pharmacy', 'key' => 'tuslah', 'value' => '3000', 'data_type' => 'integer'],
            ['group' => 'pharmacy', 'key' => 'embalase', 'value' => '1000', 'data_type' => 'integer'],
        ];

        foreach ($configs as $config) {
            Configuration::updateOrCreate(
                ['key' => $config['key']],
                $config
            );
        }
    }
}
