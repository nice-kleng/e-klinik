<?php

namespace Database\Seeders;

use App\Models\LabTest;
use App\Models\LabTestCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LabDataSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'laboran@e-klinik.com')->exists()) {
            User::create([
                'name' => 'Laboran Rina',
                'email' => 'laboran@e-klinik.com',
                'password' => Hash::make('laboran123'),
                'role' => 'laborant',
                'phone' => '081234567895',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $labCatHematologi = LabTestCategory::firstOrCreate(
            ['code' => 'HEMATOLOGI'],
            ['name' => 'Hematologi', 'description' => 'Pemeriksaan darah lengkap dan komponen darah']
        );
        $labCatKimia = LabTestCategory::firstOrCreate(
            ['code' => 'KIMIA_DARAH'],
            ['name' => 'Kimia Darah', 'description' => 'Pemeriksaan kimia darah']
        );
        $labCatUrinalisis = LabTestCategory::firstOrCreate(
            ['code' => 'URINALISIS'],
            ['name' => 'Urinalisis', 'description' => 'Pemeriksaan urine']
        );
        $labCatMikrobiologi = LabTestCategory::firstOrCreate(
            ['code' => 'MIKROBIOLOGI'],
            ['name' => 'Mikrobiologi', 'description' => 'Pemeriksaan mikroorganisme']
        );
        $labCatImunologi = LabTestCategory::firstOrCreate(
            ['code' => 'IMUNOLOGI'],
            ['name' => 'Imunologi / Serologi', 'description' => 'Pemeriksaan imunologi dan serologi']
        );

        $labTests = [
            ['HEMATOLOGI', 'HB', 'Hemoglobin', 'Darah', 'g/dL', 0, null, 11.5, 15.5, null, 15000],
            ['HEMATOLOGI', 'LEUKOSIT', 'Leukosit', 'Darah', '10³/µL', 0, null, 4.0, 10.0, null, 15000],
            ['HEMATOLOGI', 'ERITROSIT', 'Eritrosit', 'Darah', '10⁶/µL', 0, null, 4.5, 5.5, null, 15000],
            ['HEMATOLOGI', 'TROMBOSIT', 'Trombosit', 'Darah', '10³/µL', 0, null, 150, 450, null, 15000],
            ['HEMATOLOGI', 'HCT', 'Hematokrit', 'Darah', '%', 0, null, 37, 48, null, 15000],
            ['HEMATOLOGI', 'DIFF_COUNT', 'Hitung Jenis Leukosit', 'Darah', '%', 0, null, null, null, 'Lihat manual', 20000],
            ['KIMIA_DARAH', 'GDS', 'Gula Darah Sewaktu', 'Darah', 'mg/dL', 0, null, 70, 200, null, 15000],
            ['KIMIA_DARAH', 'GDP', 'Gula Darah Puasa', 'Darah', 'mg/dL', 0, null, 70, 126, null, 15000],
            ['KIMIA_DARAH', 'KOLESTEROL', 'Kolesterol Total', 'Darah', 'mg/dL', 0, null, 100, 200, null, 20000],
            ['KIMIA_DARAH', 'TRIGLISERIDA', 'Trigliserida', 'Darah', 'mg/dL', 0, null, 50, 150, null, 20000],
            ['KIMIA_DARAH', 'HDL', 'HDL Kolesterol', 'Darah', 'mg/dL', 0, null, 40, 60, null, 20000],
            ['KIMIA_DARAH', 'LDL', 'LDL Kolesterol', 'Darah', 'mg/dL', 0, null, 50, 130, null, 20000],
            ['KIMIA_DARAH', 'SGPT', 'SGPT / ALT', 'Darah', 'U/L', 0, null, 0, 40, null, 25000],
            ['KIMIA_DARAH', 'SGOT', 'SGOT / AST', 'Darah', 'U/L', 0, null, 0, 40, null, 25000],
            ['KIMIA_DARAH', 'KREATININ', 'Kreatinin', 'Darah', 'mg/dL', 0, null, 0.5, 1.2, null, 20000],
            ['KIMIA_DARAH', 'ASAM_URAT', 'Asam Urat', 'Darah', 'mg/dL', 0, null, 2.5, 7.0, null, 20000],
            ['URINALISIS', 'PROTEIN_URINE', 'Protein Urine', 'Urine', 'mg/dL', 0, null, null, null, 'Negatif', 10000],
            ['URINALISIS', 'GLUKOSA_URINE', 'Glukosa Urine', 'Urine', 'mg/dL', 0, null, null, null, 'Negatif', 10000],
            ['URINALISIS', 'BILIRUBIN_URINE', 'Bilirubin Urine', 'Urine', null, 0, null, null, null, 'Negatif', 10000],
            ['URINALISIS', 'SEDIMEN', 'Sedimen Urine', 'Urine', null, 0, null, null, null, 'Lihat manual', 15000],
        ];

        $categoryMap = [
            'HEMATOLOGI' => $labCatHematologi, 'KIMIA_DARAH' => $labCatKimia,
            'URINALISIS' => $labCatUrinalisis, 'MIKROBIOLOGI' => $labCatMikrobiologi,
            'IMUNOLOGI' => $labCatImunologi,
        ];

        foreach ($labTests as [$catCode, $code, $name, $specimen, $unit, $ageMin, $ageMax, $refLow, $refHigh, $refText, $price]) {
            LabTest::firstOrCreate(
                ['code' => $code],
                [
                    'category_id' => $categoryMap[$catCode]->id,
                    'name' => $name,
                    'specimen_type' => $specimen,
                    'unit' => $unit,
                    'age_min' => $ageMin,
                    'age_max' => $ageMax,
                    'ref_range_low' => (string) $refLow === '' ? null : (string) $refLow,
                    'ref_range_high' => (string) $refHigh === '' ? null : (string) $refHigh,
                    'ref_range_text' => $refText,
                    'price' => $price,
                    'is_active' => true,
                    'created_by' => 1,
                ]
            );
        }

        $this->command->info("Laboran: laboran@e-klinik.com / laboran123");
        $this->command->info("Lab data seeded: " . count($labTests) . " tests, 5 categories");
    }
}
