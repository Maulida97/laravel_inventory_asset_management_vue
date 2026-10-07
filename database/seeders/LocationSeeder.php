<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Seed 10 initial enterprise locations (Parents & Sub-locations) for Inventory & Asset features.
     */
    public function run(): void
    {
        // 1. Parent Locations (Level 1)
        $parentLocations = [
            [
                'code' => 'LOC-HO',
                'name' => 'Gedung Kantor Pusat (Head Office)',
                'type' => 'building',
                'address' => 'Gedung Menara Inventra Lt. 1-5, Jl. Jend. Sudirman Kav. 21, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'code' => 'LOC-WHS-JKT',
                'name' => 'Gudang Utama Logistik Jakarta',
                'type' => 'warehouse',
                'address' => 'Kawasan Pergudangan Sentra Cakung Blok B1-B4, Jakarta Timur',
                'is_active' => true,
            ],
            [
                'code' => 'LOC-BRC-SBY',
                'name' => 'Gedung Kantor Cabang Surabaya',
                'type' => 'building',
                'address' => 'Jl. Pemuda No. 88, Surabaya, Jawa Timur',
                'is_active' => true,
            ],
        ];

        $createdParents = [];
        foreach ($parentLocations as $parentData) {
            $createdParents[$parentData['code']] = Location::updateOrCreate(
                ['code' => $parentData['code']],
                [
                    'name' => $parentData['name'],
                    'type' => $parentData['type'],
                    'parent_id' => null,
                    'address' => $parentData['address'],
                    'is_active' => $parentData['is_active'],
                ]
            );
        }

        // 2. Child / Sub-locations (Level 2)
        $childLocations = [
            // Under Head Office
            [
                'parent_code' => 'LOC-HO',
                'code' => 'LOC-HO-SRV',
                'name' => 'Ruang Server & IT Data Center',
                'type' => 'room',
                'address' => 'Gedung Kantor Pusat Lt. 2 Sayap Barat',
                'is_active' => true,
            ],
            [
                'parent_code' => 'LOC-HO-MTG',
                'parent_code' => 'LOC-HO',
                'code' => 'LOC-HO-MTG',
                'name' => 'Ruang Rapat Eksekutif & Boardroom',
                'type' => 'room',
                'address' => 'Gedung Kantor Pusat Lt. 3',
                'is_active' => true,
            ],
            [
                'parent_code' => 'LOC-HO',
                'code' => 'LOC-HO-GA',
                'name' => 'Ruang Kerja General Affairs & HR',
                'type' => 'room',
                'address' => 'Gedung Kantor Pusat Lt. 1',
                'is_active' => true,
            ],

            // Under Main Warehouse Jakarta
            [
                'parent_code' => 'LOC-WHS-JKT',
                'code' => 'LOC-WHS-RCV',
                'name' => 'Area Penerimaan Barang (Inbound/Receiving)',
                'type' => 'area',
                'address' => 'Gudang Jakarta Zona A (Loading Dock)',
                'is_active' => true,
            ],
            [
                'parent_code' => 'LOC-WHS-JKT',
                'code' => 'LOC-WHS-STK',
                'name' => 'Area Penyimpanan Stok Utama (Main Racks)',
                'type' => 'area',
                'address' => 'Gudang Jakarta Zona B1-B10',
                'is_active' => true,
            ],
            [
                'parent_code' => 'LOC-WHS-JKT',
                'code' => 'LOC-WHS-DSP',
                'name' => 'Area Pengeluaran & Karantina (Outbound/Staging)',
                'type' => 'area',
                'address' => 'Gudang Jakarta Zona C',
                'is_active' => true,
            ],

            // Under Surabaya Branch
            [
                'parent_code' => 'LOC-BRC-SBY',
                'code' => 'LOC-SBY-IT',
                'name' => 'Ruang Operasional & IT Support Surabaya',
                'type' => 'room',
                'address' => 'Kantor Cabang Lt. 1 Ruang 102',
                'is_active' => true,
            ],
        ];

        foreach ($childLocations as $childData) {
            $parent = $createdParents[$childData['parent_code']] ?? null;

            Location::updateOrCreate(
                ['code' => $childData['code']],
                [
                    'name' => $childData['name'],
                    'type' => $childData['type'],
                    'parent_id' => $parent?->id,
                    'address' => $childData['address'],
                    'is_active' => $childData['is_active'],
                ]
            );
        }
    }
}
