@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1>Dashboard</h1>
    <p class="subtitle">Overview of your store's performance and stock levels.</p>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-card__label">Products</div>
        <div class="stat-card__value">{{ $productCount }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Total units in stock</div>
        <div class="stat-card__value">{{ $totalStock }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Orders</div>
        <div class="stat-card__value">{{ $orderCount }}</div>
    </div>
    <div class="stat-card {{ $pendingOrderCount > 0 ? 'warn' : '' }}">
        <div class="stat-card__label">Pending orders</div>
        <div class="stat-card__value">{{ $pendingOrderCount }}</div>
    </div>
</div>

<div class="charts-row">
    <div class="panel chart-card">
        <div class="panel__header">
            <h2>Revenue</h2>
        </div>

        <div class="chart-period-tabs" data-period-tabs data-revenue-url="{{ route('admin.dashboard.revenue-chart') }}">
            @foreach(['today' => 'Today', 'week' => 'This week', '14days' => 'Last 14 days', 'month' => 'This month', 'year' => 'This year'] as $key => $label)
                <button type="button" class="chart-period-tab {{ $period === $key ? 'is-active' : '' }}" data-period="{{ $key }}">{{ $label }}</button>
            @endforeach
        </div>

        <div data-revenue-chart-body>
            @include('admin.partials.revenue-chart-body')
        </div>
    </div>

    <div class="panel chart-card">
        <div class="panel__header">
            <h2>Orders by status</h2>
        </div>

        <div class="chart-wrap" data-chart="bar">
            <svg class="chart-svg" viewBox="0 0 {{ $statusChart['width'] }} {{ $statusChart['height'] }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Order counts by status">
                <line class="chart-gridline" x1="16" y1="{{ $statusChart['baselineY'] }}" x2="{{ $statusChart['width'] - 16 }}" y2="{{ $statusChart['baselineY'] }}"></line>

                @foreach($statusChart['bars'] as $bar)
                    @if($bar['path'])
                        <path class="chart-bar" data-status="{{ $bar['status'] }}" d="{{ $bar['path'] }}" fill="{{ $bar['color'] }}"></path>
                    @endif
                    <text class="chart-value-label" data-status="{{ $bar['status'] }}" x="{{ $bar['labelX'] }}" y="{{ $bar['y'] - 8 }}" text-anchor="middle">{{ $bar['count'] }}</text>
                    <text class="chart-axis-label" x="{{ $bar['labelX'] }}" y="{{ $statusChart['baselineY'] + 20 }}" text-anchor="middle">{{ $bar['status'] }}</text>
                    <rect class="chart-hit-bar" data-status="{{ $bar['status'] }}" data-count="{{ $bar['count'] }}" x="{{ $bar['x'] - 14 }}" y="0" width="{{ $bar['width'] + 28 }}" height="{{ $statusChart['baselineY'] }}"></rect>
                @endforeach
            </svg>
            <div class="chart-tooltip" data-tooltip hidden></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel__header">
        <h2>🔴 Low stock sub-products</h2>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">Manage products</a>
    </div>

    @if($lowStockVariants->isEmpty())
        <p style="color: var(--color-text-muted);">All variants are comfortably stocked.</p>
    @else
        <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Stock left</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockVariants as $row)
                    <tr>
                        <td style="display:flex; align-items:center; gap:10px;">
                            <img src="{{ $row['product']->imageUrl() }}" class="table-thumb" alt="">
                            {{ $row['product']->name }}
                        </td>
                        <td>{{ $row['variant']->label() ?: 'Standard' }}</td>
                        <td><span class="stock-pill out">{{ $row['variant']->stock_qty }} left</span></td>
                        <td><a href="{{ route('admin.products.edit', $row['product']) }}" class="btn btn-sm btn-outline">Restock</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

<div class="panel">
    <div class="panel__header">
        <h2>Recent orders</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">View all orders</a>
    </div>

    @if($recentOrders->isEmpty())
        <p style="color: var(--color-text-muted);">No orders placed yet.</p>
    @else
        <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>${{ number_format($order->total, 2) }}</td>
                        <td><span class="status-badge status-{{ $order->statusColor() }}">{{ $order->status }}</span></td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endsection
