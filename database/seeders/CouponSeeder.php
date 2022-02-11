<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('coupons')->insert([
            [
                'code' => 'HAPPYHOST2022',
                'description' => '100% off for 1 month',
                'api_id' => 'promo_1KOSVeLoqoklr6qp0IVfPnR1',
            ],
            [
                'code' => 'FRIENDS100',
                'description' => '100% free forever',
                'api_id' => 'promo_1KRldtLoqoklr6qpFvIZs9ML',
            ]
        ]);
    }
}
