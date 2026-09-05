@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="page-header">
    <h1>Products</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add product</a>
</div>

<div class="panel">
    <form method="GET" style="display:flex; gap:12px; margin-bottom: 18px;">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search products&hellip;" style="max-width:320px;">
        <select name="category" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected($activeCategory === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @if($search || $activeCategory)
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">Clear</a>
        @endif
    </form>

    @if($products->isEmpty())
        <p style="color: var(--color-text-muted);">No products yet &mdash; add your first one.</p>
    @else
        <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td style="display:flex; align-items:center; gap:10px;">
                            <img src="{{ $product->imageUrl() }}" class="table-thumb" alt="">
                            {{ $product->name }}
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td>
                            @if($product->hasDiscount())
                                <span class="price-original">${{ number_format($product->price, 2) }}</span>
                                <span class="price-discounted">${{ number_format($product->discountedPrice(), 2) }}</span>
                                <span class="badge-discount-inline">-{{ $product->discount_percent }}%</span>
                            @else
                                ${{ number_format($product->price, 2) }}
                            @endif
                        </td>
                        <td>
                            <span class="stock-pill {{ $product->totalStock() === 0 ? 'out' : ($product->isLowStock() ? 'low' : 'ok') }}">
                                {{ $product->totalStock() }}
                            </span>
                        </td>
                        <td>{{ $product->is_active ? 'Active' : 'Hidden' }}</td>
                        <td class="row-actions">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        @include('partials.pagination', ['paginator' => $products])
    @endif
</div>
@endsection
