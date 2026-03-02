<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $dateFrom = now()->subDays(30)->toDateString();
        $driver = DB::connection()->getDriverName();

        $statusStats = Complaint::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $brandStats = Brand::query()
            ->leftJoin('complaints', 'complaints.brand_id', '=', 'brands.id')
            ->where(function ($query) use ($dateFrom) {
                $query->whereNull('complaints.id')
                    ->orWhereDate('complaints.complaint_date', '>=', $dateFrom);
            })
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc(DB::raw('count(complaints.id)'))
            ->limit(6)
            ->get([
                'brands.name',
                DB::raw('count(complaints.id) as total'),
            ]);

        $resolutionExpr = match ($driver) {
            'mysql', 'mariadb' => 'TIMESTAMPDIFF(HOUR, created_at, closed_at)',
            'pgsql' => 'EXTRACT(EPOCH FROM (closed_at - created_at)) / 3600',
            default => '(strftime("%s", closed_at) - strftime("%s", created_at))/3600.0',
        };

        $resolutionHours = Complaint::query()
            ->whereNotNull('closed_at')
            ->whereDate('closed_at', '>=', $dateFrom)
            ->selectRaw("avg({$resolutionExpr}) as avg_hours")
            ->value('avg_hours');

        $slaCompliant = Complaint::query()
            ->whereNotNull('closed_at')
            ->whereNotNull('target_resolution_date')
            ->whereRaw('date(closed_at) <= date(target_resolution_date)')
            ->count();

        $closedCount = Complaint::query()->whereNotNull('closed_at')->count();
        $slaRate = $closedCount > 0 ? round(($slaCompliant / $closedCount) * 100, 2) : 0;

        return view('dashboard.index', [
            'cards' => [
                'total' => Complaint::count(),
                'open' => Complaint::whereIn('status', ['Open', 'Investigating', 'Action Plan'])->count(),
                'closed_30d' => Complaint::whereDate('closed_at', '>=', $dateFrom)->count(),
                'avg_resolution_hours' => $resolutionHours ? round($resolutionHours, 2) : null,
                'sla_rate' => $slaRate,
            ],
            'statusStats' => $statusStats,
            'brandStats' => $brandStats,
            'dateFrom' => $dateFrom,
        ]);
    }
}
