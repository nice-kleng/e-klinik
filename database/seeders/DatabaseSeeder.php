<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            RoleSeeder::class,
            MasterDataSeeder::class,
            MedicineSeeder::class,
            SupplierSeeder::class,
            PatientSeeder::class,
            LabDataSeeder::class,
            Icd10Seeder::class,
            RegionSeeder::class,
        ]);
    }
}
