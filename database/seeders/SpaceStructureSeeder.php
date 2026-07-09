<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\SpaceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpaceStructureSeeder extends Seeder
{
    /**
     * Seed the fixed Area/Category taxonomy (Cottages, Dining Area, Rooms +
     * the 5 Cottage sub-types). Individual Space units are created by the
     * admin via the UI, not seeded here.
     */
    public function run(): void
    {
        $cottages = Area::firstOrCreate(
            ['slug' => 'cottages'],
            ['name' => 'Cottages', 'sort_order' => 1, 'is_active' => true]
        );

        Area::firstOrCreate(
            ['slug' => 'dining-area'],
            ['name' => 'Dining Area', 'sort_order' => 2, 'is_active' => true]
        );

        Area::firstOrCreate(
            ['slug' => 'rooms'],
            ['name' => 'Rooms', 'sort_order' => 3, 'is_active' => true]
        );

        $categories = [
            ['name' => 'Standard Kubo', 'sort_order' => 1],
            ['name' => 'Lagoon Cottage', 'sort_order' => 2],
            ['name' => 'Big Cottage', 'sort_order' => 3],
            ['name' => 'Round Table Rental', 'sort_order' => 4],
            ['name' => 'Free Cottage', 'sort_order' => 5, 'is_free' => true, 'max_active_occupancy' => 18],
        ];

        foreach ($categories as $category) {
            SpaceCategory::firstOrCreate(
                ['area_id' => $cottages->id, 'slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'sort_order' => $category['sort_order'],
                    'is_free' => $category['is_free'] ?? false,
                    'max_active_occupancy' => $category['max_active_occupancy'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
