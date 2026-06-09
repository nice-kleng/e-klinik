<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            ['RM-2024-0001', '3174010101900001', 'Siti Nurhaliza', 'Jakarta', '1990-01-01', 'P', 'A', 'Jl. Merdeka No. 1', '001', '001', 'Kelurahan A', 'Kecamatan A', 'Jakarta Pusat', 'DKI Jakarta', '081211111111', 'Ibu Rumah Tangga', 'Kawin', 'Islam', null, null, null, 1],
            ['RM-2024-0002', '3174010202850002', 'Ahmad Fauzi', 'Bandung', '1985-02-02', 'L', 'B', 'Jl. Sudirman No. 10', '002', '002', 'Kelurahan B', 'Kecamatan B', 'Bandung', 'Jawa Barat', '081222222222', 'Karyawan Swasta', 'Kawin', 'Islam', 'BPJS', '0001234567890', 'aktif', 1],
            ['RM-2024-0003', '3174010310950003', 'Budi Prasetyo', 'Semarang', '1995-03-03', 'L', 'O', 'Jl. Diponegoro No. 5', '003', '003', 'Kelurahan C', 'Kecamatan C', 'Semarang', 'Jawa Tengah', '081233333333', 'Wiraswasta', 'Belum Kawin', 'Islam', null, null, null, 1],
            ['RM-2024-0004', '3174010412800004', 'Dewi Sartika', 'Surabaya', '1980-04-04', 'P', 'AB', 'Jl. Pahlawan No. 20', '004', '004', 'Kelurahan D', 'Kecamatan D', 'Surabaya', 'Jawa Timur', '081244444444', 'Guru', 'Cerai Hidup', 'Islam', 'BPJS', '0001234567891', 'aktif', 1],
            ['RM-2024-0005', '3174010519750005', 'Hendra Gunawan', 'Medan', '1975-05-05', 'L', 'A', 'Jl. Gajah Mada No. 8', '005', '005', 'Kelurahan E', 'Kecamatan E', 'Medan', 'Sumatera Utara', '081255555555', 'PNS', 'Kawin', 'Kristen', null, null, null, 1],
            ['RM-2024-0006', '3174010601920006', 'Rina Marlina', 'Yogyakarta', '1992-06-06', 'P', 'B', 'Jl. Malioboro No. 12', '006', '006', 'Kelurahan F', 'Kecamatan F', 'Yogyakarta', 'DI Yogyakarta', '081266666666', 'Mahasiswa', 'Belum Kawin', 'Islam', 'BPJS', '0001234567892', 'aktif', 2],
            ['RM-2024-0007', '3174010710880007', 'Agus Supriyanto', 'Solo', '1988-07-07', 'L', 'O', 'Jl. Slamet Riyadi No. 3', '007', '007', 'Kelurahan G', 'Kecamatan G', 'Solo', 'Jawa Tengah', '081277777777', 'Supir', 'Kawin', 'Islam', null, null, null, 2],
            ['RM-2024-0008', '3174010819780008', 'Maya Anggraini', 'Palembang', '1978-08-08', 'P', 'AB', 'Jl. Veteran No. 15', '008', '008', 'Kelurahan H', 'Kecamatan H', 'Palembang', 'Sumatera Selatan', '081288888888', 'Dokter', 'Kawin', 'Islam', 'BPJS', '0001234567893', 'aktif', 2],
            ['RM-2024-0009', '3174010901960009', 'Rudi Hartono', 'Makassar', '1996-09-09', 'L', 'A', 'Jl. Hasanuddin No. 7', '009', '009', 'Kelurahan I', 'Kecamatan I', 'Makassar', 'Sulawesi Selatan', '081299999999', 'Nelayan', 'Belum Kawin', 'Islam', null, null, null, 2],
            ['RM-2024-0010', '3174011019830010', 'Fitri Handayani', 'Aceh', '1983-10-10', 'P', 'B', 'Jl. Teuku Umar No. 22', '010', '010', 'Kelurahan J', 'Kecamatan J', 'Banda Aceh', 'Aceh', '081200000001', 'Pedagang', 'Cerai Mati', 'Islam', 'BPJS', '0001234567894', 'aktif', 1],
        ];

        foreach ($patients as [$rm, $nik, $name, $birthPlace, $birthDate, $gender, $bloodType, $address, $rt, $rw, $village, $district, $city, $province, $phone, $occupation, $marriageStatus, $religion, $insuranceType, $insuranceNumber, $bpjsStatus, $createdBy]) {
            Patient::firstOrCreate(
                ['no_rm' => $rm],
                [
                    'nik' => $nik,
                    'name' => $name,
                    'birth_place' => $birthPlace,
                    'birth_date' => $birthDate,
                    'gender' => $gender,
                    'blood_type' => $bloodType,
                    'address' => $address,
                    'rt' => $rt,
                    'rw' => $rw,
                    'village' => $village,
                    'district' => $district,
                    'city' => $city,
                    'province' => $province,
                    'phone' => $phone,
                    'occupation' => $occupation,
                    'marriage_status' => $marriageStatus,
                    'religion' => $religion,
                    'insurance_type' => $insuranceType,
                    'insurance_number' => $insuranceNumber,
                    'bpjs_status' => $bpjsStatus,
                    'created_by' => $createdBy,
                ]
            );
        }

        $this->command->info("Patients seeded: " . count($patients) . " sample patients");
    }
}
