<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CafeSeeder extends Seeder
{
    public function run(): void
    {
        $cafes = [
            [
                'name' => 'Cafe Hồ Gươm',
                'address' => 'Khu vực Hồ Hoàn Kiếm, Hà Nội',
                'description' => 'Quán cà phê gần khu vực hồ.',
                'longitude' => 105.8523,
                'latitude' => 21.0287,
            ],
            [
                'name' => 'Cafe Nhà Thờ',
                'address' => 'Khu vực Nhà thờ Lớn Hà Nội',
                'description' => 'Một điểm dừng chân ở trung tâm.',
                'longitude' => 105.8480,
                'latitude' => 21.0288,
            ],
            [
                'name' => 'Cafe Văn Miếu',
                'address' => 'Khu vực Văn Miếu, Hà Nội',
                'description' => 'Quán mẫu ở phía tây khu trung tâm.',
                'longitude' => 105.8350,
                'latitude' => 21.0270,
            ],
        ];

        foreach ($cafes as $cafe) {
            DB::insert(
                'INSERT INTO cafes
                    (name, address, description, location, created_at, updated_at)
                 VALUES
                    (?, ?, ?, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, NOW(), NOW())',
                [
                    $cafe['name'],
                    $cafe['address'],
                    $cafe['description'],
                    $cafe['longitude'],
                    $cafe['latitude'],
                ]
            );
        }
    }
}
