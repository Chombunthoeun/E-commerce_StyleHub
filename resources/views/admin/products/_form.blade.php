@csrf
@if(isset($product))
    @method('PUT')
@endif

<div class="panel">
    <h2 style="margin-bottom:18px;">Product details</h2>

    <div class="image-upload">
        <img src="{{ isset($product) ? $product->imageUrl() : asset('images/placeholder.svg') }}" class="image-upload__preview" data-image-preview alt="Preview">
        <div>
            <label for="image_file">Product image</label>
            <input type="file" id="image_file" name="image_file" accept="image/*" data-image-input>
            <div class="field-error">@error('image_file'){{ $message }}@enderror</div>
        </div>
    </div>

    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
        <div class="field-error">@error('name'){{ $message }}@enderror</div>
    </div>

    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select a category&hellip;</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <div class="field-error">@error('category_id'){{ $message }}@enderror</div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
        <div class="form-group">
            <label for="price">Base price ($)</label>
            <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" required>
            <div class="field-error">@error('price'){{ $message }}@enderror</div>
        </div>

        <div class="form-group">
            <label for="discount_percent">Discount (%)</label>
            <input type="number" min="0" max="100" id="discount_percent" name="discount_percent" value="{{ old('discount_percent', $product->discount_percent ?? '') }}" placeholder="none">
            <div class="field-error">@error('discount_percent'){{ $message }}@enderror</div>
        </div>

        <div class="form-group">
            <label for="low_stock_threshold">Low stock threshold</label>
            <input type="number" min="0" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" required>
        </div>
    </div>

    <div class="form-group checkbox-row">
        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
        <label for="is_active">Visible in store</label>
    </div>
</div>

<div class="panel">
    <div class="panel__header">
        <h2>Variants (size / color / style)</h2>
        <button type="button" class="btn btn-outline btn-sm" data-add-variant>+ Add variant</button>
    </div>
    <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom:14px;">
        Each variant is a specific combination (e.g. Size M, Black, Slim Fit) with its own stock count. Leave price override blank to use the base price.
    </p>

    <input type="hidden" name="deleted_variant_ids" data-deleted-variants value="">

    <div data-variant-rows data-placeholder="{{ asset('images/placeholder.svg') }}" data-low-stock-threshold="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}">
        @php($variants = old('variants', isset($product) ? $product->variants->map(fn ($v) => $v->toArray())->all() : [[]]))
        @foreach($variants as $i => $variant)
            <div class="variant-row">
                <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id'] ?? '' }}">
                <div class="variant-row__swatch">
                    <img class="variant-swatch-preview" data-variant-preview src="{{ !empty($variant['image']) ? asset('storage/'.$variant['image']) : asset('images/placeholder.svg') }}" alt="">
                    <label class="btn btn-outline btn-sm variant-swatch-upload">Photo
                        <input type="file" accept="image/*" class="visually-hidden" data-variant-image-input name="variants[{{ $i }}][image_file]">
                    </label>
                </div>
                <div>
                    <label>Size</label>
                    <input type="text" name="variants[{{ $i }}][size]" value="{{ $variant['size'] ?? '' }}" placeholder="e.g. M / 9">
                </div>
                <div>
                    <label>Color</label>
                    <input type="text" name="variants[{{ $i }}][color]" value="{{ $variant['color'] ?? '' }}" placeholder="e.g. Black">
                </div>
                <div>
                    <label>Style</label>
                    <input type="text" name="variants[{{ $i }}][style]" value="{{ $variant['style'] ?? '' }}" placeholder="e.g. Slim Fit">
                </div>
                <div>
                    <label>Stock qty</label>
                    <input type="number" name="variants[{{ $i }}][stock_qty]" min="0" value="{{ $variant['stock_qty'] ?? 0 }}" required data-variant-stock-input>
                    <span class="variant-stock-status" data-variant-stock-status></span>
                </div>
                <div>
                    <label>Price override</label>
                    <input type="number" step="0.01" min="0" name="variants[{{ $i }}][price_override]" value="{{ $variant['price_override'] ?? '' }}" placeholder="optional">
                </div>
                <div>
                    <label>Discount %</label>
                    <input type="number" min="0" max="100" name="variants[{{ $i }}][discount_percent]" value="{{ $variant['discount_percent'] ?? '' }}" placeholder="same as product">
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-variant" data-remove-variant>Remove</button>
            </div>
        @endforeach
    </div>
    <p style="color: var(--color-text-muted); font-size: 0.8rem; margin-top:10px;">Tip: add a photo to at least one variant per color &mdash; customers will see it as a clickable color swatch that swaps the main product photo.</p>
    <div class="field-error">@error('variants'){{ $message }}@enderror</div>
</div>

<div style="display:flex; gap:12px;">
    <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Save changes' : 'Create product' }}</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Cancel</a>
</div>
