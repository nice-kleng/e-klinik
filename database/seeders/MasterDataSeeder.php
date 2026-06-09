<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\MedicineCategory;
use App\Models\Polyclinic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@e-klinik.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'phone' => '081234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $dokterUser = User::firstOrCreate(
            ['email' => 'dokter@e-klinik.com'],
            [
                'name' => 'Dr. Sari',
                'password' => Hash::make('dokter123'),
                'phone' => '081234567891',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $dokterUser->assignRole('doctor');

        User::firstOrCreate(
            ['email' => 'apoteker@e-klinik.com'],
            [
                'name' => 'Apoteker Budi',
                'password' => Hash::make('apoteker123'),
                'phone' => '081234567892',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('pharmacist');

        User::firstOrCreate(
            ['email' => 'laboran@e-klinik.com'],
            [
                'name' => 'Laboran Rina',
                'password' => Hash::make('laboran123'),
                'phone' => '081234567895',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('laborant');

        User::firstOrCreate(
            ['email' => 'kasir@e-klinik.com'],
            [
                'name' => 'Kasir Dewi',
                'password' => Hash::make('kasir123'),
                'phone' => '081234567896',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('cashier');

        User::firstOrCreate(
            ['email' => 'perawat@e-klinik.com'],
            [
                'name' => 'Perawat Ani',
                'password' => Hash::make('perawat123'),
                'phone' => '081234567897',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('nurse');

        $this->command->info("Users: admin/dokter/apoteker/laboran/kasir/perawat @e-klinik.com");

        $poliData = [
            ['code' => 'UMU', 'name' => 'Poli Umum', 'location' => 'Lantai 1'],
            ['code' => 'GIG', 'name' => 'Poli Gigi', 'location' => 'Lantai 1'],
            ['code' => 'MATA', 'name' => 'Poli Mata', 'location' => 'Lantai 2'],
            ['code' => 'KAND', 'name' => 'Poli Kandungan', 'location' => 'Lantai 2'],
            ['code' => 'ANAK', 'name' => 'Poli Anak', 'location' => 'Lantai 1'],
            ['code' => 'THT', 'name' => 'Poli THT', 'location' => 'Lantai 2'],
            ['code' => 'SARAF', 'name' => 'Poli Saraf', 'location' => 'Lantai 2'],
            ['code' => 'JANTUNG', 'name' => 'Poli Jantung', 'location' => 'Lantai 3'],
        ];

        foreach ($poliData as $p) {
            Polyclinic::firstOrCreate(
                ['code' => $p['code']],
                $p + ['is_active' => true, 'description' => $p['name']]
            );
        }

        $poliUmum = Polyclinic::where('code', 'UMU')->first();
        $poliGigi = Polyclinic::where('code', 'GIG')->first();
        $poliAnak = Polyclinic::where('code', 'ANAK')->first();

        Doctor::firstOrCreate(
            ['code' => 'DR001'],
            [
                'user_id' => $dokterUser->id,
                'polyclinic_id' => $poliUmum->id,
                'name' => 'Dr. Sari',
                'specialist' => 'Dokter Umum',
                'sip_number' => 'SIP-001/2024',
                'phone' => '081234567891',
                'is_active' => true,
            ]
        );

        Doctor::firstOrCreate(
            ['code' => 'DR002'],
            [
                'polyclinic_id' => $poliGigi->id,
                'name' => 'Drg. Bambang',
                'specialist' => 'Dokter Gigi',
                'sip_number' => 'SIP-002/2024',
                'phone' => '081234567893',
                'is_active' => true,
            ]
        );

        Doctor::firstOrCreate(
            ['code' => 'DR003'],
            [
                'polyclinic_id' => $poliAnak->id,
                'name' => 'Dr. Anita',
                'specialist' => 'Dokter Anak',
                'sip_number' => 'SIP-003/2024',
                'phone' => '081234567894',
                'is_active' => true,
            ]
        );

        $kategori = [
            ['code' => 'ANTIBIOTIK', 'name' => 'Antibiotik', 'description' => 'Obat antibiotik'],
            ['code' => 'ANALGESIK', 'name' => 'Analgesik', 'description' => 'Obat pereda nyeri'],
            ['code' => 'ANTIHIPERTENSI', 'name' => 'Antihipertensi', 'description' => 'Obat tekanan darah tinggi'],
            ['code' => 'VITAMIN', 'name' => 'Vitamin & Suplemen', 'description' => 'Vitamin dan suplemen makanan'],
            ['code' => 'SALURAN_CERNA', 'name' => 'Saluran Cerna', 'description' => 'Obat saluran pencernaan'],
            ['code' => 'PERNAPASAN', 'name' => 'Saluran Napas', 'description' => 'Obat saluran pernapasan'],
            ['code' => 'KORTIKOSTEROID', 'name' => 'Kortikosteroid', 'description' => 'Obat kortikosteroid'],
            ['code' => 'LAINNYA', 'name' => 'Lainnya', 'description' => 'Kategori lainnya'],
        ];

        foreach ($kategori as $k) {
            MedicineCategory::firstOrCreate(['code' => $k['code']], $k);
        }

        $this->command->info("Master data: 8 poli, 3 dokter, 8 kategori obat");
    }
}
