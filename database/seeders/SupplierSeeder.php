<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['SUP-001', 'PT Kalbe Farma Tbk', 'Jl. Gedung Panjang No. 5, Jakarta', '021-1234561', 'kalbe@example.com', 'Budi Santoso'],
            ['SUP-002', 'PT Kimia Farma Tbk', 'Jl. Veteran No. 18, Bandung', '022-1234562', 'kimiafarma@example.com', 'Siti Rahmawati'],
            ['SUP-003', 'PT Dexa Medica', 'Jl. Raya Bogor Km 28, Depok', '021-1234563', 'dexa@example.com', 'Ahmad Hidayat'],
            ['SUP-004', 'PT Sanbe Farma', 'Jl. Cimahi No. 92, Bandung', '022-1234564', 'sanbe@example.com', 'Rina Marlina'],
            ['SUP-005', 'PT Hexpharm Jaya', 'Jl. Raya Semarang Km 12, Semarang', '024-1234565', 'hexpharm@example.com', 'Joko Widodo'],
            ['SUP-006', 'PT Pharos Indonesia', 'Jl. TB Simatupang No. 88, Jakarta', '021-1234566', 'pharos@example.com', 'Dewi Sartika'],
        ];

        foreach ($suppliers as [$code, $name, $address, $phone, $email, $contact]) {
            Supplier::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'address' => $address,
                    'phone' => $phone,
                    'email' => $email,
                    'contact_person' => $contact,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info("Suppliers seeded: " . count($suppliers) . " items");
    }
}
