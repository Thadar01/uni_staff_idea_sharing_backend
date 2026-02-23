<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permissions')->insert([
            [
                'permission' => 'view_staff',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission' => 'create_staff',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission' => 'edit_staff',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission' => 'delete_staff',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}