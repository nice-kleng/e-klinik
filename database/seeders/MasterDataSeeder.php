<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\MedicineCategory;
use App\Models\Polyclinic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@e-klinik.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '081234567890',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info("Admin: admin@e-klinik.com / admin123");

        $dokterUser = User::create([
            'name' => 'Dr. Sari',
            'email' => 'dokter@e-klinik.com',
            'password' => Hash::make('dokter123'),
            'role' => 'doctor',
            'phone' => '081234567891',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $apotekerUser = User::create([
            'name' => 'Apoteker Budi',
            'email' => 'apoteker@e-klinik.com',
            'password' => Hash::make('apoteker123'),
            'role' => 'pharmacist',
            'phone' => '081234567892',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Laboran Rina',
            'email' => 'laboran@e-klinik.com',
            'password' => Hash::make('laboran123'),
            'role' => 'laborant',
            'phone' => '081234567895',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

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
            Polyclinic::create($p + ['is_active' => true, 'description' => $p['name']]);
        }

        $poliUmum = Polyclinic::where('code', 'UMU')->first();
        $poliGigi = Polyclinic::where('code', 'GIG')->first();
        $poliAnak = Polyclinic::where('code', 'ANAK')->first();

        Doctor::create([
            'user_id' => $dokterUser->id,
            'polyclinic_id' => $poliUmum->id,
            'code' => 'DR001',
            'name' => 'Dr. Sari',
            'specialist' => 'Dokter Umum',
            'sip_number' => 'SIP-001/2024',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        Doctor::create([
            'polyclinic_id' => $poliGigi->id,
            'code' => 'DR002',
            'name' => 'Drg. Bambang',
            'specialist' => 'Dokter Gigi',
            'sip_number' => 'SIP-002/2024',
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        Doctor::create([
            'polyclinic_id' => $poliAnak->id,
            'code' => 'DR003',
            'name' => 'Dr. Anita',
            'specialist' => 'Dokter Anak',
            'sip_number' => 'SIP-003/2024',
            'phone' => '081234567894',
            'is_active' => true,
        ]);

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
            MedicineCategory::create($k);
        }

        $this->command->info("Laboran: laboran@e-klinik.com / laboran123 — run 'php artisan db:seed --class=LabDataSeeder' to seed lab data");
    }
}
