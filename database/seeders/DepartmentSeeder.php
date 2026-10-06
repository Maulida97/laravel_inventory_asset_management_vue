<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'code' => 'DEP-IT',
                'name' => 'Information Technology',
                'description' => 'Divisi Teknologi Informasi & Infrastruktur',
                'is_active' => true,
            ],
            [
                'code' => 'DEP-HR',
                'name' => 'Human Resources & General Affairs',
                'description' => 'Divisi Sumber Daya Manusia & Urusan Umum',
                'is_active' => true,
            ],
            [
                'code' => 'DEP-FIN',
                'name' => 'Finance & Accounting',
                'description' => 'Divisi Keuangan, Akuntansi & Perpajakan',
                'is_active' => true,
            ],
            [
                'code' => 'DEP-OPS',
                'name' => 'Operations & Logistics',
                'description' => 'Divisi Operasional & Manajemen Logistik',
                'is_active' => true,
            ],
            [
                'code' => 'DEP-MKT',
                'name' => 'Marketing & Sales',
                'description' => 'Divisi Pemasaran & Penjualan',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}
