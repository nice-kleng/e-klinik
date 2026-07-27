<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn(string $code) => MedicineCategory::where('code', $code)->first()->id;

        $medicines = [
            // Antibiotik
            ['AMOX-500', 'Amoxicillin 500 mg', 'Amoxicillin', $cat('ANTIBIOTIK'), 'Kalbe Farma', 'kaplet', '500 mg', true, true, 500],
            ['AMOX-SYR', 'Amoxicillin Sirup 125 mg/5ml', 'Amoxicillin', $cat('ANTIBIOTIK'), 'Kalbe Farma', 'botol', '125 mg/5ml 60 ml', true, true, null],
            ['CIPRO-500', 'Ciprofloxacin 500 mg', 'Ciprofloxacin', $cat('ANTIBIOTIK'), 'Hexpharm Jaya', 'tablet', '500 mg', true, true, 500],
            ['METRO-500', 'Metronidazole 500 mg', 'Metronidazole', $cat('ANTIBIOTIK'), 'Kimia Farma', 'tablet', '500 mg', true, true, 500],
            ['AZITHRO-500', 'Azithromycin 500 mg', 'Azithromycin', $cat('ANTIBIOTIK'), 'Kalbe Farma', 'tablet', '500 mg', false, true, 500],

            // Analgesik
            ['PARA-500', 'Paracetamol 500 mg', 'Paracetamol', $cat('ANALGESIK'), 'Sanbe', 'tablet', '500 mg', true, false, 500],
            ['PARA-SYR', 'Paracetamol Sirup 250 mg/5ml', 'Paracetamol', $cat('ANALGESIK'), 'Sanbe', 'botol', '250 mg/5ml 60 ml', true, false, null],
            ['IBUPRO-400', 'Ibuprofen 400 mg', 'Ibuprofen', $cat('ANALGESIK'), 'Dexa Medica', 'tablet', '400 mg', true, false, 400],
            ['DICLO-50', 'Diclofenac Sodium 50 mg', 'Diclofenac Sodium', $cat('ANALGESIK'), 'Novell Pharma', 'tablet', '50 mg', true, true, 50],
            ['MEFEN-500', 'Asam Mefenamat 500 mg', 'Asam Mefenamat', $cat('ANALGESIK'), 'Sanbe', 'kaplet', '500 mg', true, true, 500],

            // Antihipertensi
            ['AMLOD-5', 'Amlodipine 5 mg', 'Amlodipine', $cat('ANTIHIPERTENSI'), 'Kalbe Farma', 'tablet', '5 mg', true, true, 5],
            ['AMLOD-10', 'Amlodipine 10 mg', 'Amlodipine', $cat('ANTIHIPERTENSI'), 'Kalbe Farma', 'tablet', '10 mg', true, true, 10],
            ['CAPTO-25', 'Captopril 25 mg', 'Captopril', $cat('ANTIHIPERTENSI'), 'Hexpharm Jaya', 'tablet', '25 mg', true, true, 25],
            ['BISOP-5', 'Bisoprolol 5 mg', 'Bisoprolol', $cat('ANTIHIPERTENSI'), 'Novartis', 'tablet', '5 mg', false, true, 5],
            ['FUROS-40', 'Furosemide 40 mg', 'Furosemide', $cat('ANTIHIPERTENSI'), 'Sanbe', 'tablet', '40 mg', true, true, 40],

            // Vitamin & Suplemen
            ['VIT-C-500', 'Vitamin C 500 mg', 'Vitamin C', $cat('VITAMIN'), 'Kimia Farma', 'tablet', '500 mg', true, false, 500],
            ['VIT-BCOMP', 'Vitamin B Kompleks', 'Vitamin B Kompleks', $cat('VITAMIN'), 'Sanbe', 'tablet', '-', true, false, null],
            ['FERR-200', 'Ferrous Sulfate 200 mg', 'Ferrous Sulfate', $cat('VITAMIN'), 'Kalbe Farma', 'tablet', '200 mg', true, false, 200],
            ['ZINC-20', 'Zinc Sulfate 20 mg', 'Zinc Sulfate', $cat('VITAMIN'), 'Pharos', 'tablet', '20 mg', true, false, 20],
            ['MULTIVIT', 'Multivitamin', 'Multivitamin', $cat('VITAMIN'), 'Kimia Farma', 'kaplet', '-', true, false, null],

            // Saluran Cerna
            ['OMEP-20', 'Omeprazole 20 mg', 'Omeprazole', $cat('SALURAN_CERNA'), 'Kalbe Farma', 'kapsul', '20 mg', true, true, 20],
            ['RANIT-150', 'Ranitidine 150 mg', 'Ranitidine', $cat('SALURAN_CERNA'), 'Hexpharm Jaya', 'tablet', '150 mg', true, true, 150],
            ['ANTASIDA', 'Suspensi Antasida', 'Antasida', $cat('SALURAN_CERNA'), 'Pharos', 'botol', '-', true, false, null],
            ['LOPERAM-2', 'Loperamide 2 mg', 'Loperamide', $cat('SALURAN_CERNA'), 'Sanbe', 'tablet', '2 mg', true, false, 2],
            ['DOMPER-10', 'Domperidone 10 mg', 'Domperidone', $cat('SALURAN_CERNA'), 'Dexa Medica', 'tablet', '10 mg', true, true, 10],

            // Saluran Napas
            ['SALBU-2', 'Salbutamol 2 mg', 'Salbutamol', $cat('PERNAPASAN'), 'Kalbe Farma', 'tablet', '2 mg', true, true, 2],
            ['DEXTRO-10', 'Dextromethorphan 10 mg', 'Dextromethorphan', $cat('PERNAPASAN'), 'Pharos', 'tablet', '10 mg', true, false, 10],
            ['CTM-4', 'CTM 4 mg', 'Chlorpheniramine Maleate', $cat('PERNAPASAN'), 'Sanbe', 'tablet', '4 mg', true, false, 4],
            ['AMBROX-30', 'Ambroxol 30 mg', 'Ambroxol', $cat('PERNAPASAN'), 'Dexa Medica', 'tablet', '30 mg', true, false, 30],
            ['PREDNI-5', 'Prednisone 5 mg', 'Prednisone', $cat('KORTIKOSTEROID'), 'Kimia Farma', 'tablet', '5 mg', true, true, 5],

            // Lainnya
            ['DEXAM-0.5', 'Dexamethasone 0.5 mg', 'Dexamethasone', $cat('LAINNYA'), 'Kalbe Farma', 'tablet', '0.5 mg', true, true, 0.5],
            ['ALLOPU-100', 'Allopurinol 100 mg', 'Allopurinol', $cat('LAINNYA'), 'Dexa Medica', 'tablet', '100 mg', true, true, 100],
            ['GLIMEP-2', 'Glimepiride 2 mg', 'Glimepiride', $cat('LAINNYA'), 'Sanbe', 'tablet', '2 mg', false, true, 2],
            ['METFO-500', 'Metformin 500 mg', 'Metformin', $cat('LAINNYA'), 'Hexpharm Jaya', 'tablet', '500 mg', true, true, 500],
            ['SIMVA-10', 'Simvastatin 10 mg', 'Simvastatin', $cat('LAINNYA'), 'Kalbe Farma', 'tablet', '10 mg', true, true, 10],
        ];

        foreach ($medicines as [$code, $name, $generic, $catId, $mfr, $unit, $content, $isGeneric, $needRx, $dosagePerUnit]) {
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
                    'dosage_per_unit' => $dosagePerUnit,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info("Medicines seeded: " . count($medicines) . " items");

        // ——— Initial stock ———
        $supplierIds = Supplier::pluck('id')->toArray();
        if (empty($supplierIds)) {
            $this->command->warn('No suppliers found, skipping inventory seed');
            return;
        }
        $adminId = \App\Models\User::where('email', 'admin@e-klinik.com')->value('id') ?? 1;

        $stockData = [
            'AMOX-500'     => ['qty' => 200, 'hpp' => 800, 'selling' => 1500],
            'AMOX-SYR'     => ['qty' => 50,  'hpp' => 15000, 'selling' => 25000],
            'CIPRO-500'    => ['qty' => 150, 'hpp' => 1200, 'selling' => 2500],
            'METRO-500'    => ['qty' => 150, 'hpp' => 600, 'selling' => 1500],
            'AZITHRO-500'  => ['qty' => 100, 'hpp' => 3000, 'selling' => 5000],
            'PARA-500'     => ['qty' => 300, 'hpp' => 300, 'selling' => 1000],
            'PARA-SYR'     => ['qty' => 40,  'hpp' => 12000, 'selling' => 20000],
            'IBUPRO-400'   => ['qty' => 200, 'hpp' => 500, 'selling' => 1500],
            'DICLO-50'     => ['qty' => 150, 'hpp' => 700, 'selling' => 1500],
            'MEFEN-500'    => ['qty' => 200, 'hpp' => 500, 'selling' => 1200],
            'AMLOD-5'      => ['qty' => 200, 'hpp' => 400, 'selling' => 1000],
            'AMLOD-10'     => ['qty' => 200, 'hpp' => 500, 'selling' => 1200],
            'CAPTO-25'     => ['qty' => 200, 'hpp' => 300, 'selling' => 800],
            'BISOP-5'      => ['qty' => 100, 'hpp' => 1000, 'selling' => 2000],
            'FUROS-40'     => ['qty' => 150, 'hpp' => 400, 'selling' => 1000],
            'VIT-C-500'    => ['qty' => 250, 'hpp' => 200, 'selling' => 500],
            'VIT-BCOMP'    => ['qty' => 200, 'hpp' => 300, 'selling' => 800],
            'FERR-200'     => ['qty' => 150, 'hpp' => 400, 'selling' => 1000],
            'ZINC-20'      => ['qty' => 150, 'hpp' => 500, 'selling' => 1200],
            'MULTIVIT'     => ['qty' => 200, 'hpp' => 300, 'selling' => 800],
            'OMEP-20'      => ['qty' => 200, 'hpp' => 600, 'selling' => 1500],
            'RANIT-150'    => ['qty' => 150, 'hpp' => 500, 'selling' => 1200],
            'ANTASIDA'     => ['qty' => 40,  'hpp' => 10000, 'selling' => 18000],
            'LOPERAM-2'    => ['qty' => 100, 'hpp' => 400, 'selling' => 1000],
            'DOMPER-10'    => ['qty' => 150, 'hpp' => 600, 'selling' => 1500],
            'SALBU-2'      => ['qty' => 100, 'hpp' => 300, 'selling' => 800],
            'DEXTRO-10'    => ['qty' => 100, 'hpp' => 400, 'selling' => 1000],
            'CTM-4'        => ['qty' => 150, 'hpp' => 200, 'selling' => 500],
            'AMBROX-30'    => ['qty' => 150, 'hpp' => 500, 'selling' => 1200],
            'PREDNI-5'     => ['qty' => 100, 'hpp' => 300, 'selling' => 800],
            'DEXAM-0.5'    => ['qty' => 100, 'hpp' => 200, 'selling' => 600],
            'ALLOPU-100'   => ['qty' => 100, 'hpp' => 400, 'selling' => 1000],
            'GLIMEP-2'     => ['qty' => 100, 'hpp' => 600, 'selling' => 1500],
            'METFO-500'    => ['qty' => 200, 'hpp' => 400, 'selling' => 1000],
            'SIMVA-10'     => ['qty' => 100, 'hpp' => 800, 'selling' => 2000],
        ];

        $now = now();
        $expiredDate = $now->copy()->addYears(2);
        $prodDate = $now->copy()->subMonths(3);

        foreach ($stockData as $code => $stock) {
            $medicine = Medicine::where('code', $code)->first();
            if (!$medicine) continue;

            $batch = 'BTH-' . $now->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $supplierId = $supplierIds[array_rand($supplierIds)];

            $inv = Inventory::firstOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => $batch,
                ],
                [
                    'supplier_id' => $supplierId,
                    'quantity' => $stock['qty'],
                    'unit_price' => $stock['hpp'],
                    'selling_price' => $stock['selling'],
                    'production_date' => $prodDate,
                    'expired_date' => $expiredDate,
                    'notes' => 'Stok awal',
                    'created_by' => $adminId,
                ]
            );

            if ($inv->wasRecentlyCreated) {
                InventoryTransaction::create([
                    'inventory_id' => $inv->id,
                    'medicine_id' => $medicine->id,
                    'type' => 'in',
                    'quantity' => $stock['qty'],
                    'reference_type' => 'initial_stock',
                    'reference_id' => null,
                    'unit_price' => $stock['hpp'],
                    'total_price' => $stock['hpp'] * $stock['qty'],
                    'notes' => 'Stok awal ' . $medicine->name,
                    'created_by' => $adminId,
                ]);
            }
        }

        // Second batch (earlier expiry) for FIFO testing — top sellers
        $batch2 = [
            'PARA-500'   => ['qty' => 200, 'hpp' => 350, 'selling' => 1100],
            'AMOX-500'   => ['qty' => 100, 'hpp' => 850, 'selling' => 1600],
            'IBUPRO-400' => ['qty' => 100, 'hpp' => 550, 'selling' => 1600],
            'PARA-SYR'   => ['qty' => 20,  'hpp' => 13000, 'selling' => 22000],
        ];
        $expiredDate2 = $now->copy()->addMonths(18);
        $prodDate2 = $now->copy()->subMonths(1);

        foreach ($batch2 as $code => $stock) {
            $medicine = Medicine::where('code', $code)->first();
            if (!$medicine) continue;

            $batch = 'BTH-' . $now->format('Ymd') . '-' . str_pad(rand(1000, 1999), 4, '0', STR_PAD_LEFT);
            $supplierId = $supplierIds[array_rand($supplierIds)];

            $inv = Inventory::firstOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => $batch,
                ],
                [
                    'supplier_id' => $supplierId,
                    'quantity' => $stock['qty'],
                    'unit_price' => $stock['hpp'],
                    'selling_price' => $stock['selling'],
                    'production_date' => $prodDate2,
                    'expired_date' => $expiredDate2,
                    'notes' => 'Batch kedua',
                    'created_by' => $adminId,
                ]
            );

            if ($inv->wasRecentlyCreated) {
                InventoryTransaction::create([
                    'inventory_id' => $inv->id,
                    'medicine_id' => $medicine->id,
                    'type' => 'in',
                    'quantity' => $stock['qty'],
                    'reference_type' => 'initial_stock',
                    'reference_id' => null,
                    'unit_price' => $stock['hpp'],
                    'total_price' => $stock['hpp'] * $stock['qty'],
                    'notes' => 'Batch kedua ' . $medicine->name,
                    'created_by' => $adminId,
                ]);
            }
        }

        $this->command->info("Inventory seeded: " . count($stockData) . " items + " . count($batch2) . " batch-2");
    }
}
