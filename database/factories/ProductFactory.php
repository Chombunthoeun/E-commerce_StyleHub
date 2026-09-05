<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    private static ?array $categoryIds = null;

    private static array $colors = ['Black', 'White', 'Red', 'Blue', 'Green', 'Grey', 'Brown', 'Navy', 'Pink', 'Yellow'];

    private static array $sizes = [null, 'S', 'M', 'L', 'XL', '8', '9', '10', '11'];

    public function definition(): array
    {
        self::$categoryIds ??= Category::pluck('id')->all();

        $name = ucwords(fake()->unique()->words(rand(2, 4), true));

        return [
            'category_id' => fake()->randomElement(self::$categoryIds),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100000, 9999999),
            'description' => fake()->sentence(12),
            'price' => fake()->randomFloat(2, 10, 300),
            'discount_percent' => fake()->boolean(30) ? fake()->numberBetween(5, 60) : null,
            'low_stock_threshold' => fake()->numberBetween(3, 10),
            'is_active' => fake()->boolean(90),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $variantCount = rand(1, 4);
            $colors = fake()->randomElements(self::$colors, $variantCount);

            foreach ($colors as $color) {
                $product->variants()->create([
                    'color' => $color,
                    'size' => fake()->randomElement(self::$sizes),
                    'stock_qty' => fake()->numberBetween(0, 60),
                    'price_override' => fake()->boolean(15) ? fake()->randomFloat(2, 10, 300) : null,
                    'discount_percent' => fake()->boolean(20) ? fake()->numberBetween(5, 70) : null,
                ]);
            }
        });
    }
}
