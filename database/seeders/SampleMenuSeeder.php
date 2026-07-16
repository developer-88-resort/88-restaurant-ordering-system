<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class SampleMenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategory('Samgyupsal & Korean BBQ', 1, [
            ['Pork Samgyupsal (Plain)', 259],
            ['Pork Samgyupsal (Marinated)', 279],
            ['Beef Bulgogi', 349],
            ['Spicy Pork Bulgogi', 299],
            ['Chicken Galbi', 269],
        ]);

        $this->seedCategory('Korean Stews & Soups', 2, [
            ['Kimchi Jjigae', 259],
            ['Sundubu Jjigae', 269],
            ['Budae Jjigae (Army Stew)', 399],
            ['Samgyetang', 349],
        ]);

        $this->seedCategory('Rice Meals', 3, [
            ['Bibimbap', 249],
            ['Kimchi Fried Rice', 219],
            ['Japchae with Rice', 259],
            ['Bulgogi Rice Bowl', 269],
        ]);

        $this->seedCategory('Appetizers & Side Dishes', 4, [
            ['Kimchi (Extra Serving)', 99],
            ['Korean Fish Cake (Odeng)', 129],
            ['Japchae (Glass Noodles)', 189],
            ['Korean Pancake (Pajeon)', 179],
            ['Fried Mandu (Dumplings)', 159],
        ]);

        $this->seedCategory('Drinks & Beverages', 5, [
            ['Bottled Water', 30],
            ['Soft Drinks (Regular)', 45],
            ['Iced Tea', 50],
            ['Soju', 250],
            ['Korean Rice Wine (Makgeolli)', 280],
            ['Hot Barley Tea', 40],
        ]);

        $this->seedCategory('Desserts', 6, [
            ['Mango Bingsu', 199],
            ['Chocolate Bingsu', 199],
            ['Honey Toast', 149],
            ['Fresh Fruit Platter', 129],
        ]);
    }

    /**
     * @param  array<int, array{0: string, 1: int}>  $items
     */
    protected function seedCategory(string $name, int $sortOrder, array $items): void
    {
        $category = MenuCategory::firstOrCreate(
            ['name' => $name],
            ['sort_order' => $sortOrder, 'is_active' => true]
        );

        foreach ($items as [$itemName, $price]) {
            $category->menuItems()->firstOrCreate(
                ['name' => $itemName],
                ['price' => $price, 'is_available' => true]
            );
        }
    }
}
