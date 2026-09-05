<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['category', 'variants'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category'));
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $request->string('search')->toString(),
            'categories' => Category::orderBy('name')->get(),
            'activeCategory' => $request->integer('category'),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name']),
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount_percent' => $validated['discount_percent'] ?? null,
                'low_stock_threshold' => $validated['low_stock_threshold'],
                'is_active' => $request->boolean('is_active'),
                'image' => $this->storeImage($request),
            ]);

            $this->syncVariants($product, $request, $validated['variants'] ?? []);
        });

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load('variants');

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        DB::transaction(function () use ($validated, $request, $product) {
            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $product->name === $validated['name'] ? $product->slug : $this->uniqueSlug($validated['name'], $product->id),
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount_percent' => $validated['discount_percent'] ?? null,
                'low_stock_threshold' => $validated['low_stock_threshold'],
                'is_active' => $request->boolean('is_active'),
                'image' => $this->storeImage($request) ?? $product->image,
            ]);

            $deletedIds = array_filter(explode(',', (string) $request->input('deleted_variant_ids', '')));
            $this->syncVariants($product, $request, $validated['variants'] ?? [], $deletedIds);
        });

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.style' => ['nullable', 'string', 'max:50'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
            'variants.*.stock_qty' => ['required', 'integer', 'min:0'],
            'variants.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'variants.*.discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image_file')) {
            return null;
        }

        return $request->file('image_file')->store('products', 'public');
    }

    private function syncVariants(Product $product, Request $request, array $variants, array $deletedIds = []): void
    {
        if (! empty($deletedIds)) {
            $deletedVariants = ProductVariant::where('product_id', $product->id)->whereIn('id', $deletedIds)->get();
            foreach ($deletedVariants as $deletedVariant) {
                if ($deletedVariant->image) {
                    Storage::disk('public')->delete($deletedVariant->image);
                }
            }
            ProductVariant::where('product_id', $product->id)
                ->whereIn('id', $deletedIds)
                ->delete();
        }

        foreach ($variants as $i => $variant) {
            $attributes = [
                'size' => ($variant['size'] ?? null) ?: null,
                'color' => ($variant['color'] ?? null) ?: null,
                'style' => ($variant['style'] ?? null) ?: null,
                'sku' => ($variant['sku'] ?? null) ?: null,
                'stock_qty' => $variant['stock_qty'],
                'price_override' => ($variant['price_override'] ?? '') !== '' ? $variant['price_override'] : null,
                'discount_percent' => ($variant['discount_percent'] ?? '') !== '' ? $variant['discount_percent'] : null,
            ];

            if ($request->hasFile("variants.$i.image_file")) {
                $attributes['image'] = $request->file("variants.$i.image_file")->store('variants', 'public');
            }

            if (! empty($variant['id'])) {
                ProductVariant::where('product_id', $product->id)
                    ->where('id', $variant['id'])
                    ->update($attributes);
            } else {
                $product->variants()->create($attributes);
            }
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
