<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Coffee', 'description' => 'Coffee drinks'],
            ['name' => 'Tea', 'description' => 'Tea drinks'],
            ['name' => 'Juice', 'description' => 'Fresh juice'],
            ['name' => 'Cake', 'description' => 'Desserts and cakes'],
        ])->mapWithKeys(function (array $category) {
            $record = Category::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );

            return [$category['name'] => $record->id];
        });

        Product::query()
            ->where('name', 'like', 'Sample Product %')
            ->delete();

        $products = [
            ['name' => 'Espresso', 'category' => 'Coffee', 'price' => 2.50, 'description' => 'Strong and bold espresso shot'],
            ['name' => 'Americano', 'category' => 'Coffee', 'price' => 2.80, 'description' => 'Espresso with hot water'],
            ['name' => 'Cappuccino', 'category' => 'Coffee', 'price' => 3.20, 'description' => 'Espresso with steamed milk foam'],
            ['name' => 'Latte', 'category' => 'Coffee', 'price' => 3.50, 'description' => 'Smooth espresso with steamed milk'],
            ['name' => 'Mocha', 'category' => 'Coffee', 'price' => 3.80, 'description' => 'Chocolate flavored coffee'],
            ['name' => 'Caramel Macchiato', 'category' => 'Coffee', 'price' => 4.20, 'description' => 'Espresso with caramel milk'],
            ['name' => 'Cold Brew', 'category' => 'Coffee', 'price' => 3.90, 'description' => 'Slow steeped cold coffee'],
            ['name' => 'Vietnamese Iced Coffee', 'category' => 'Coffee', 'price' => 3.70, 'description' => 'Coffee with condensed milk'],
            ['name' => 'Matcha Latte', 'category' => 'Tea', 'price' => 3.60, 'description' => 'Creamy Japanese green tea latte'],
            ['name' => 'Peach Tea', 'category' => 'Tea', 'price' => 2.90, 'description' => 'Refreshing peach black tea'],
            ['name' => 'Lemon Tea', 'category' => 'Tea', 'price' => 2.70, 'description' => 'Classic black tea with lemon'],
            ['name' => 'Earl Grey Tea', 'category' => 'Tea', 'price' => 2.80, 'description' => 'Fragrant bergamot tea'],
            ['name' => 'Orange Juice', 'category' => 'Juice', 'price' => 3.10, 'description' => 'Freshly squeezed orange juice'],
            ['name' => 'Watermelon Juice', 'category' => 'Juice', 'price' => 3.00, 'description' => 'Sweet chilled watermelon juice'],
            ['name' => 'Pineapple Juice', 'category' => 'Juice', 'price' => 3.20, 'description' => 'Tropical pineapple juice'],
            ['name' => 'Mango Smoothie', 'category' => 'Juice', 'price' => 3.90, 'description' => 'Creamy mango smoothie'],
            ['name' => 'Tiramisu Slice', 'category' => 'Cake', 'price' => 4.50, 'description' => 'Italian coffee-flavored dessert'],
            ['name' => 'Cheesecake', 'category' => 'Cake', 'price' => 4.20, 'description' => 'Classic creamy cheesecake'],
            ['name' => 'Chocolate Brownie', 'category' => 'Cake', 'price' => 3.40, 'description' => 'Rich chocolate brownie'],
            ['name' => 'Croissant Butter', 'category' => 'Cake', 'price' => 2.60, 'description' => 'Flaky buttery croissant'],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $categories[$item['category']],
                    'price' => $item['price'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
