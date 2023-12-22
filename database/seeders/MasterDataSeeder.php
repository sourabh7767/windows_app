<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('master_datas')->insert([
            [
                'master_key' => 'meating_total_time',
                'master_value' => '30',
            ],
            [
                'master_key' => 'break_total_time',
                'master_value' => '30',
            ]
        ]);
    }
}
