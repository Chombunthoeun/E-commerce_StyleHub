<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $swatchCache = [];

    public function run(): void
    {
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'Bunthoeun@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::forceCreate([
            'name' => 'Jane Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        $shirts = Category::create(['name' => 'Shirts', 'slug' => 'shirts']);

        $this->product($shoes, 'Classic Runner Sneaker', 79.99, 5,
            'A lightweight everyday sneaker built for comfort on any surface.', [
                ['size' => '8', 'color' => 'Black', 'style' => 'Low-top', 'stock_qty' => 12],
                ['size' => '9', 'color' => 'Black', 'style' => 'Low-top', 'stock_qty' => 3],
                ['size' => '10', 'color' => 'White', 'style' => 'Low-top', 'stock_qty' => 8],
            ]);

        $this->product($shoes, 'Urban High-Top', 89.99, 5,
            'Street-style high-tops with a reinforced ankle collar.', [
                ['size' => '9', 'color' => 'Red', 'style' => 'High-top', 'stock_qty' => 2],
                ['size' => '10', 'color' => 'Black', 'style' => 'High-top', 'stock_qty' => 15],
            ]);

        $this->product($shoes, 'Trail Hiking Boot', 120.00, 5,
            'Rugged, water-resistant boots for off-road adventures.', [
                ['size' => '9', 'color' => 'Brown', 'style' => 'Boot', 'stock_qty' => 0],
                ['size' => '10', 'color' => 'Brown', 'style' => 'Boot', 'stock_qty' => 6],
                ['size' => '11', 'color' => 'Grey', 'style' => 'Boot', 'stock_qty' => 9],
            ]);

        $this->product($shoes, 'Slip-On Canvas', 55.00, 5,
            'Easy slip-on canvas shoes for a relaxed, casual look.', [
                ['size' => '8', 'color' => 'Navy', 'style' => 'Slip-on', 'stock_qty' => 20],
                ['size' => '9', 'color' => 'Navy', 'style' => 'Slip-on', 'stock_qty' => 18],
                ['size' => '10', 'color' => 'White', 'style' => 'Slip-on', 'stock_qty' => 4],
            ]);

        $this->product($shirts, 'Classic Cotton Tee', 24.99, 5,
            'A soft, breathable staple tee for everyday wear.', [
                ['size' => 'S', 'color' => 'White', 'style' => 'Crew Neck', 'stock_qty' => 30],
                ['size' => 'M', 'color' => 'White', 'style' => 'Crew Neck', 'stock_qty' => 25],
                ['size' => 'L', 'color' => 'Black', 'style' => 'Crew Neck', 'stock_qty' => 1],
            ]);

        $this->product($shirts, 'Slim Fit Oxford Shirt', 45.00, 5,
            'A tailored Oxford shirt that dresses up or down with ease.', [
                ['size' => 'S', 'color' => 'Blue', 'style' => 'Slim Fit', 'stock_qty' => 10],
                ['size' => 'M', 'color' => 'Blue', 'style' => 'Slim Fit', 'stock_qty' => 3],
                ['size' => 'L', 'color' => 'Blue', 'style' => 'Regular Fit', 'stock_qty' => 8],
            ]);

        $this->product($shirts, 'Flannel Casual Shirt', 39.99, 5,
            'Warm brushed-cotton flannel with a classic check pattern.', [
                ['size' => 'M', 'color' => 'Red Check', 'style' => 'Regular Fit', 'stock_qty' => 7],
                ['size' => 'L', 'color' => 'Red Check', 'style' => 'Regular Fit', 'stock_qty' => 2],
                ['size' => 'XL', 'color' => 'Green Check', 'style' => 'Regular Fit', 'stock_qty' => 12],
            ]);

        $this->product($shirts, 'Graphic Print Tee', 27.99, 5,
            'Statement graphic print tee made from 100% cotton.', [
                ['size' => 'S', 'color' => 'Print A', 'style' => 'Crew Neck', 'stock_qty' => 15],
                ['size' => 'M', 'color' => 'Print B', 'style' => 'Crew Neck', 'stock_qty' => 9],
                ['size' => 'L', 'color' => 'Print A', 'style' => 'Crew Neck', 'stock_qty' => 0],
            ]);
    }

    private function product(Category $category, string $name, float $price, int $threshold, string $description, array $variants): void
    {
        $product = Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'price' => $price,
            'low_stock_threshold' => $threshold,
            'is_active' => true,
        ]);

        foreach ($variants as $variant) {
            $variant['image'] = $this->swatchFor($variant['color'] ?? null);
            $product->variants()->create($variant);
        }
    }

    private function swatchFor(?string $color): ?string
    {
        if (! $color) {
            return null;
        }

        $key = Str::slug($color);

        if (! in_array($key, ['black', 'white', 'red', 'navy'], true)) {
            return null;
        }

        if (isset($this->swatchCache[$key])) {
            return $this->swatchCache[$key];
        }

        $source = resource_path("seed-images/{$key}.svg");
        $path = "variants/swatch-{$key}.svg";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents($source));
        }

        return $this->swatchCache[$key] = $path;
    }
}
