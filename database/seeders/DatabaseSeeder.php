<?php

namespace Database\Seeders;

use App\Models\Sparepart;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        Sparepart::create([
            "name_sparepart" => "Oli Mesin",
            "minimal_stok" => 5,
            "stok" => 20
        ]);

        Sparepart::create([
            "name_sparepart" => "Filter Oli",
            "minimal_stok" => 5,
            "stok" => 20
        ]);

        Sparepart::create([
            "name_sparepart" => "Kampas Rem",
            "minimal_stok" => 5,
            "stok" => 20
        ]);
    }
}
