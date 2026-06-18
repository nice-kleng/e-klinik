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
                'nik' => '3174010101900011',
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

        User::firstOrCreate(
            ['email' => 'receptionist@e-klinik.com'],
            [
                'name' => 'Resepsionis Rudi',
                'password' => Hash::make('receptionist123'),
                'phone' => '081234567898',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('receptionist');

        // Dokter per poli spesialis
        $dokterUsers = [];
        $dokterData = [
            ['key' => 'gigi', 'email' => 'dokter.gigi@e-klinik.com',  'name' => 'Drg. Bambang',     'nik' => '3174010101900012'],
            ['key' => 'anak', 'email' => 'dokter.anak@e-klinik.com',  'name' => 'Dr. Anita',       'nik' => '3174010101900013'],
            ['key' => 'pdl',  'email' => 'dokter.pdl@e-klinik.com',   'name' => 'Dr. Budi Santoso','nik' => '3174010101900014'],
            ['key' => 'saraf','email' => 'dokter.saraf@e-klinik.com', 'name' => 'Dr. Citra Dewi',  'nik' => '3174010101900015'],
            ['key' => 'rad',  'email' => 'dokter.radiologi@e-klinik.com', 'name' => 'Dr. Eko Prasetyo','nik' => '3174010101900016'],
        ];
        foreach ($dokterData as $dd) {
            $u = User::firstOrCreate(
                ['email' => $dd['email']],
                [
                    'name' => $dd['name'],
                    'nik' => $dd['nik'],
                    'password' => Hash::make('dokter123'),
                    'phone' => '0812345679' . str_pad((string)rand(0, 99), 2, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $u->assignRole('doctor');
            $dokterUsers[$dd['key']] = $u;
        }

        // Perawat per poli
        $perawatPoli = [
            ['key' => 'umu',  'email' => 'perawat.umu@e-klinik.com',  'name' => 'Perawat Sari'],
            ['key' => 'gigi', 'email' => 'perawat.gigi@e-klinik.com', 'name' => 'Perawat Dewi'],
            ['key' => 'anak', 'email' => 'perawat.anak@e-klinik.com', 'name' => 'Perawat Rina'],
            ['key' => 'pdl',  'email' => 'perawat.pdl@e-klinik.com',  'name' => 'Perawat Budi'],
            ['key' => 'saraf','email' => 'perawat.saraf@e-klinik.com','name' => 'Perawat Tono'],
            ['key' => 'rad',  'email' => 'perawat.radiologi@e-klinik.com', 'name' => 'Perawat Lina'],
        ];
        foreach ($perawatPoli as $pp) {
            $u = User::firstOrCreate(
                ['email' => $pp['email']],
                [
                    'name' => $pp['name'],
                    'password' => Hash::make('perawat123'),
                    'phone' => '0812345679' . str_pad((string)rand(0, 99), 2, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $u->assignRole('nurse');
        }

        $this->command->info("Users: admin/dokter/apoteker/laboran/kasir/perawat/resepsionis @e-klinik.com + 5 dokter poli + 6 perawat poli");

        $poliData = [
            ['code' => 'UMU', 'name' => 'Poli Umum', 'location' => 'Lantai 1'],
            ['code' => 'GIG', 'name' => 'Poli Gigi', 'location' => 'Lantai 1'],
            ['code' => 'MATA', 'name' => 'Poli Mata', 'location' => 'Lantai 2'],
            ['code' => 'KAND', 'name' => 'Poli Kandungan', 'location' => 'Lantai 2'],
            ['code' => 'ANAK', 'name' => 'Poli Anak', 'location' => 'Lantai 1'],
            ['code' => 'THT', 'name' => 'Poli THT', 'location' => 'Lantai 2'],
            ['code' => 'PDL', 'name' => 'Poli Penyakit Dalam', 'location' => 'Lantai 1'],
            ['code' => 'SARAF', 'name' => 'Poli Saraf', 'location' => 'Lantai 2'],
            ['code' => 'RAD', 'name' => 'Poli Radiologi', 'location' => 'Lantai 1'],
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
        $poliPdl = Polyclinic::where('code', 'PDL')->first();
        $poliSaraf = Polyclinic::where('code', 'SARAF')->first();
        $poliRad = Polyclinic::where('code', 'RAD')->first();

        Doctor::updateOrCreate(
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

        Doctor::updateOrCreate(
            ['code' => 'DR002'],
            [
                'user_id' => $dokterUsers['gigi']->id,
                'polyclinic_id' => $poliGigi->id,
                'name' => 'Drg. Bambang',
                'specialist' => 'Dokter Gigi',
                'sip_number' => 'SIP-002/2024',
                'phone' => '081234567893',
                'is_active' => true,
            ]
        );

        Doctor::updateOrCreate(
            ['code' => 'DR003'],
            [
                'user_id' => $dokterUsers['anak']->id,
                'polyclinic_id' => $poliAnak->id,
                'name' => 'Dr. Anita',
                'specialist' => 'Dokter Anak',
                'sip_number' => 'SIP-003/2024',
                'phone' => '081234567894',
                'is_active' => true,
            ]
        );

        Doctor::updateOrCreate(
            ['code' => 'DR004'],
            [
                'user_id' => $dokterUsers['pdl']->id,
                'polyclinic_id' => $poliPdl->id,
                'name' => 'Dr. Budi Santoso',
                'specialist' => 'Spesialis Penyakit Dalam',
                'sip_number' => 'SIP-004/2024',
                'phone' => '081234567899',
                'is_active' => true,
            ]
        );

        Doctor::updateOrCreate(
            ['code' => 'DR005'],
            [
                'user_id' => $dokterUsers['saraf']->id,
                'polyclinic_id' => $poliSaraf->id,
                'name' => 'Dr. Citra Dewi',
                'specialist' => 'Spesialis Saraf',
                'sip_number' => 'SIP-005/2024',
                'phone' => '081234567800',
                'is_active' => true,
            ]
        );

        Doctor::updateOrCreate(
            ['code' => 'DR006'],
            [
                'user_id' => $dokterUsers['rad']->id,
                'polyclinic_id' => $poliRad->id,
                'name' => 'Dr. Eko Prasetyo',
                'specialist' => 'Spesialis Radiologi',
                'sip_number' => 'SIP-006/2024',
                'phone' => '081234567801',
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

        $this->command->info("Master data: 10 poli, 6 dokter (6 user-linked), 6 perawat poli, 8 kategori obat");
    }
}
