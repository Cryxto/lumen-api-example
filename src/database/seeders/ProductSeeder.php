<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $timestamp = \Carbon\Carbon::now()->toDateTimeString();
        DB::table('products')->insert(
            [
                [
                    'name' => 'book',
                    'price' => '5000',
                    'qty' => '20',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ],
                [
                    'name' => 'pen',
                    'price' => '2000',
                    'qty' => '20',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ],
            ]
        );
    }
}
