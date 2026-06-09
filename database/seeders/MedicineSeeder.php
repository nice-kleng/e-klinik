<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn(string $code) => MedicineCategory::where('code', $code)->first()->id;

        $medicines = [
            // Antibiotik
            ['AMOX-500', 'Amoxicillin 500 mg', 'Amoxicillin', $cat('ANTIBIOTIK'), 'Kalbe Farma', 'kaplet', '500 mg', true, true],
            ['AMOX-SYR', 'Amoxicillin Sirup 125 mg/5ml', 'Amoxicillin', $cat('ANTIBIOTIK'), 'Kalbe Farma', 'botol', '125 mg/5ml 60 ml', true, true],
            ['CIPRO-500', 'Ciprofloxacin 500 mg', 'Ciprofloxacin', $cat('ANTIBIOTIK'), 'Hexpharm Jaya', 'tablet', '500 mg', true, true],
            ['METRO-500', 'Metronidazole 500 mg', 'Metronidazole', $cat('ANTIBIOTIK'), 'Kimia Farma', 'tablet', '500 mg', true, true],
            ['AZITHRO-500', 'Azithromycin 500 mg', 'Azithromycin', $cat('ANTIBIOTIK'), 'Kalbe Farma', 'tablet', '500 mg', false, true],

            // Analgesik
            ['PARA-500', 'Paracetamol 500 mg', 'Paracetamol', $cat('ANALGESIK'), 'Sanbe', 'tablet', '500 mg', true, false],
            ['PARA-SYR', 'Paracetamol Sirup 250 mg/5ml', 'Paracetamol', $cat('ANALGESIK'), 'Sanbe', 'botol', '250 mg/5ml 60 ml', true, false],
            ['IBUPRO-400', 'Ibuprofen 400 mg', 'Ibuprofen', $cat('ANALGESIK'), 'Dexa Medica', 'tablet', '400 mg', true, false],
            ['DICLO-50', 'Diclofenac Sodium 50 mg', 'Diclofenac Sodium', $cat('ANALGESIK'), 'Novell Pharma', 'tablet', '50 mg', true, true],
            ['MEFEN-500', 'Asam Mefenamat 500 mg', 'Asam Mefenamat', $cat('ANALGESIK'), 'Sanbe', 'kaplet', '500 mg', true, true],

            // Antihipertensi
            ['AMLOD-5', 'Amlodipine 5 mg', 'Amlodipine', $cat('ANTIHIPERTENSI'), 'Kalbe Farma', 'tablet', '5 mg', true, true],
            ['AMLOD-10', 'Amlodipine 10 mg', 'Amlodipine', $cat('ANTIHIPERTENSI'), 'Kalbe Farma', 'tablet', '10 mg', true, true],
            ['CAPTO-25', 'Captopril 25 mg', 'Captopril', $cat('ANTIHIPERTENSI'), 'Hexpharm Jaya', 'tablet', '25 mg', true, true],
            ['BISOP-5', 'Bisoprolol 5 mg', 'Bisoprolol', $cat('ANTIHIPERTENSI'), 'Novartis', 'tablet', '5 mg', false, true],
            ['FUROS-40', 'Furosemide 40 mg', 'Furosemide', $cat('ANTIHIPERTENSI'), 'Sanbe', 'tablet', '40 mg', true, true],

            // Vitamin & Suplemen
            ['VIT-C-500', 'Vitamin C 500 mg', 'Vitamin C', $cat('VITAMIN'), 'Kimia Farma', 'tablet', '500 mg', true, false],
            ['VIT-BCOMP', 'Vitamin B Kompleks', 'Vitamin B Kompleks', $cat('VITAMIN'), 'Sanbe', 'tablet', '-', true, false],
            ['FERR-200', 'Ferrous Sulfate 200 mg', 'Ferrous Sulfate', $cat('VITAMIN'), 'Kalbe Farma', 'tablet', '200 mg', true, false],
            ['ZINC-20', 'Zinc Sulfate 20 mg', 'Zinc Sulfate', $cat('VITAMIN'), 'Pharos', 'tablet', '20 mg', true, false],
            ['MULTIVIT', 'Multivitamin', 'Multivitamin', $cat('VITAMIN'), 'Kimia Farma', 'kaplet', '-', true, false],

            // Saluran Cerna
            ['OMEP-20', 'Omeprazole 20 mg', 'Omeprazole', $cat('SALURAN_CERNA'), 'Kalbe Farma', 'kapsul', '20 mg', true, true],
            ['RANIT-150', 'Ranitidine 150 mg', 'Ranitidine', $cat('SALURAN_CERNA'), 'Hexpharm Jaya', 'tablet', '150 mg', true, true],
            ['ANTASIDA', 'Suspensi Antasida', 'Antasida', $cat('SALURAN_CERNA'), 'Pharos', 'botol', '-', true, false],
            ['LOPERAM-2', 'Loperamide 2 mg', 'Loperamide', $cat('SALURAN_CERNA'), 'Sanbe', 'tablet', '2 mg', true, false],
            ['DOMPER-10', 'Domperidone 10 mg', 'Domperidone', $cat('SALURAN_CERNA'), 'Dexa Medica', 'tablet', '10 mg', true, true],

            // Saluran Napas
            ['SALBU-2', 'Salbutamol 2 mg', 'Salbutamol', $cat('PERNAPASAN'), 'Kalbe Farma', 'tablet', '2 mg', true, true],
            ['DEXTRO-10', 'Dextromethorphan 10 mg', 'Dextromethorphan', $cat('PERNAPASAN'), 'Pharos', 'tablet', '10 mg', true, false],
            ['CTM-4', 'CTM 4 mg', 'Chlorpheniramine Maleate', $cat('PERNAPASAN'), 'Sanbe', 'tablet', '4 mg', true, false],
            ['AMBROX-30', 'Ambroxol 30 mg', 'Ambroxol', $cat('PERNAPASAN'), 'Dexa Medica', 'tablet', '30 mg', true, false],
            ['PREDNI-5', 'Prednisone 5 mg', 'Prednisone', $cat('KORTIKOSTEROID'), 'Kimia Farma', 'tablet', '5 mg', true, true],

            // Lainnya
            ['DEXAM-0.5', 'Dexamethasone 0.5 mg', 'Dexamethasone', $cat('LAINNYA'), 'Kalbe Farma', 'tablet', '0.5 mg', true, true],
            ['ALLOPU-100', 'Allopurinol 100 mg', 'Allopurinol', $cat('LAINNYA'), 'Dexa Medica', 'tablet', '100 mg', true, true],
            ['GLIMEP-2', 'Glimepiride 2 mg', 'Glimepiride', $cat('LAINNYA'), 'Sanbe', 'tablet', '2 mg', false, true],
            ['METFO-500', 'Metformin 500 mg', 'Metformin', $cat('LAINNYA'), 'Hexpharm Jaya', 'tablet', '500 mg', true, true],
            ['SIMVA-10', 'Simvastatin 10 mg', 'Simvastatin', $cat('LAINNYA'), 'Kalbe Farma', 'tablet', '10 mg', true, true],
        ];

        foreach ($medicines as [$code, $name, $generic, $catId, $mfr, $unit, $content, $isGeneric, $needRx]) {
            Medicine::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'generic_name' => $generic,
                    'category_id' => $catId,
                    'manufacturer' => $mfr,
                    'unit' => $unit,
                    'content' => $content,
                    'is_generic' => $isGeneric,
                    'requires_prescription' => $needRx,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info("Medicines seeded: " . count($medicines) . " items");
    }
}
