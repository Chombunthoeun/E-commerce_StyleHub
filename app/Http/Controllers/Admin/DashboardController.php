<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PERIODS = ['today', 'week', '14days', 'month', 'year'];

    public function index(Request $request): View
    {
        $products = Product::with('variants')->get();

        $lowStockVariants = $products->flatMap(function (Product $product) {
            return $product->variants
                ->filter(fn ($variant) => $variant->stock_qty <= $product->low_stock_threshold)
                ->map(fn ($variant) => ['product' => $product, 'variant' => $variant]);
        })->sortBy(fn (array $row) => $row['variant']->stock_qty)->values();

        $period = $this->resolvePeriod($request);

        return view('admin.dashboard', [
            'productCount' => $products->count(),
            'totalStock' => $products->sum(fn (Product $product) => $product->totalStock()),
            'lowStockVariants' => $lowStockVariants,
            'orderCount' => Order::count(),
            'pendingOrderCount' => Order::where('status', 'Pending')->count(),
            'revenue' => Order::where('status', '!=', 'Cancelled')->sum('total'),
            'recentOrders' => Order::with('user')->orderByDesc('created_at')->limit(5)->get(),
            'period' => $period,
            'revenueChart' => $this->buildRevenueChart($period),
            'statusChart' => $this->buildStatusChart(),
        ]);
    }

    /**
     * AJAX endpoint: re-render just the revenue chart for a given period.
     */
    public function revenueChart(Request $request): View
    {
        $period = $this->resolvePeriod($request);

        return view('admin.partials.revenue-chart-body', [
            'revenueChart' => $this->buildRevenueChart($period),
        ]);
    }

    private function resolvePeriod(Request $request): string
    {
        $period = $request->string('period')->toString();

        return in_array($period, self::PERIODS, true) ? $period : '14days';
    }

    /**
     * Revenue (non-cancelled orders) bucketed for the given period, as a line/area chart.
     */
    private function buildRevenueChart(string $period): array
    {
        [$start, $end, $unit, $slots] = match ($period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay(), 'hour', 24],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), 'day', 7],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), 'day', Carbon::now()->daysInMonth],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), 'month', 12],
            default => [Carbon::today()->subDays(13), Carbon::today()->endOfDay(), 'day', 14],
        };

        $bucketFormat = match ($unit) {
            'hour' => 'Y-m-d H',
            'month' => 'Y-m',
            default => 'Y-m-d',
        };

        $ordersByBucket = Order::where('status', '!=', 'Cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->groupBy(fn (Order $order) => $order->created_at->format($bucketFormat));

        $series = collect(range(0, $slots - 1))->map(function (int $i) use ($start, $unit, $bucketFormat, $ordersByBucket) {
            $date = match ($unit) {
                'hour' => $start->copy()->addHours($i),
                'month' => $start->copy()->addMonths($i),
                default => $start->copy()->addDays($i),
            };
            $key = $date->format($bucketFormat);

            return [
                'date' => $key,
                'label' => match ($unit) {
                    'hour' => $date->format('ga'),
                    'month' => $date->format('M'),
                    default => $date->format('M j'),
                },
                'value' => (float) $ordersByBucket->get($key, collect())->sum('total'),
            ];
        });

        $width = 640;
        $height = 200;
        $padLeft = 46;
        $padRight = 16;
        $padTop = 20;
        $padBottom = 32;
        $chartW = $width - $padLeft - $padRight;
        $chartH = $height - $padTop - $padBottom;
        $baselineY = $padTop + $chartH;

        $maxValue = $this->niceMax((float) $series->max('value'));
        $count = $series->count();
        $labelStep = max(1, (int) round($count / 7));

        $points = $series->values()->map(function (array $point, int $i) use ($count, $chartW, $chartH, $padLeft, $padTop, $maxValue, $labelStep) {
            $x = $padLeft + ($count > 1 ? ($i / ($count - 1)) * $chartW : 0);
            $y = $padTop + $chartH - ($maxValue > 0 ? ($point['value'] / $maxValue) * $chartH : 0);
            $showLabel = $i % $labelStep === 0 || $i === $count - 1;

            return [
                ...$point,
                'x' => round($x, 2),
                'y' => round($y, 2),
                'valueLabel' => $this->compactCurrency($point['value']),
                'showLabel' => $showLabel,
            ];
        });

        $linePath = $points->map(fn (array $p, int $i) => ($i === 0 ? 'M' : 'L')." {$p['x']},{$p['y']}")->implode(' ');
        $areaPath = $linePath." L {$points->last()['x']},{$baselineY} L {$points->first()['x']},{$baselineY} Z";

        $gridlines = collect([0, 0.5, 1])->map(fn (float $fraction) => [
            'y' => round($padTop + $chartH - ($fraction * $chartH), 2),
            'label' => $this->compactCurrency($maxValue * $fraction),
        ]);

        return [
            'width' => $width,
            'height' => $height,
            'baselineY' => $baselineY,
            'points' => $points,
            'linePath' => $linePath,
            'areaPath' => $areaPath,
            'gridlines' => $gridlines,
            'total' => $this->compactCurrency($series->sum('value')),
        ];
    }

    /**
     * Order counts grouped by status, as a bar chart.
     */
    private function buildStatusChart(): array
    {
        $statuses = [
            ['status' => 'Pending', 'color' => '#eda100'],
            ['status' => 'Processing', 'color' => '#2a78d6'],
            ['status' => 'Shipped', 'color' => '#4a3aa7'],
            ['status' => 'Completed', 'color' => '#008300'],
            ['status' => 'Cancelled', 'color' => '#e34948'],
        ];

        $counts = Order::selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $width = 640;
        $height = 200;
        $padLeft = 16;
        $padRight = 16;
        $padTop = 30;
        $padBottom = 34;
        $chartW = $width - $padLeft - $padRight;
        $chartH = $height - $padTop - $padBottom;
        $baselineY = $padTop + $chartH;

        $maxCount = max(1, (int) ($counts->max() ?? 0));
        $slotW = $chartW / count($statuses);
        $barW = min(56, $slotW - 28);

        $bars = collect($statuses)->values()->map(function (array $entry, int $i) use ($counts, $slotW, $padLeft, $barW, $baselineY, $chartH, $maxCount) {
            $count = (int) ($counts[$entry['status']] ?? 0);
            $barH = ($count / $maxCount) * $chartH;
            $x = $padLeft + $i * $slotW + ($slotW - $barW) / 2;
            $y = $baselineY - $barH;
            $radius = min(4, $barH / 2);

            return [
                'status' => $entry['status'],
                'color' => $entry['color'],
                'count' => $count,
                'x' => round($x, 2),
                'y' => round($y, 2),
                'width' => round($barW, 2),
                'height' => round($barH, 2),
                'path' => $barH > 0 ? $this->roundedTopRectPath($x, $y, $barW, $barH, $radius) : null,
                'labelX' => round($x + $barW / 2, 2),
            ];
        });

        return [
            'width' => $width,
            'height' => $height,
            'baselineY' => $baselineY,
            'bars' => $bars,
        ];
    }

    private function roundedTopRectPath(float $x, float $y, float $w, float $h, float $r): string
    {
        $bottom = $y + $h;
        $right = $x + $w;

        return sprintf(
            'M %1$s,%2$s L %1$s,%3$s Q %1$s,%4$s %5$s,%4$s L %6$s,%4$s Q %7$s,%4$s %7$s,%3$s L %7$s,%2$s Z',
            $x,
            $bottom,
            $y + $r,
            $y,
            $x + $r,
            $right - $r,
            $right
        );
    }

    private function niceMax(float $value): float
    {
        if ($value <= 0) {
            return 10;
        }

        $magnitude = 10 ** floor(log10($value));
        $residual = $value / $magnitude;

        $niceResidual = match (true) {
            $residual > 5 => 10,
            $residual > 2 => 5,
            $residual > 1 => 2,
            default => 1,
        };

        return $niceResidual * $magnitude;
    }

    private function compactCurrency(float $value): string
    {
        if ($value >= 1000) {
            return '$'.rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'K';
        }

        return '$'.number_format($value, 0);
    }
}
