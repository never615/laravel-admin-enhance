<?php

namespace Malto\Admin\Seeder;

use Illuminate\Database\Seeder;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database  seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(BaseTablesSeeder::class);
        $this->call(MenuTablesSeeder::class);
        $this->call(Menu2TablesSeeder::class);
        $this->call(VideoMenuSeeder::class);

        $this->call(PermissionTablesSeeder::class);
        $this->call(Permission2TablesSeeder::class);
        $this->call(VideoPermissionSeeder::class);
    }
}
