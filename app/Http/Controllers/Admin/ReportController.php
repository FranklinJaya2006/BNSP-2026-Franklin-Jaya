<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Tampilkan laporan sewa dan ringkasan pendapatan. */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Rental::with(['user', 'vehicle', 'garage', 'extensions'])
            ->orderByDesc('start_date');

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }
        if ($request->filled('garage_type')) {
            $query->whereHas('garage', fn ($q) => $q->where('type', $request->garage_type));
        }
        if ($request->filled('user_search')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$request->user_search.'%'));
        }

        $rentals = $query->paginate(25)->withQueryString();

        // Ringkasan
        $allQuery = Rental::query();
        if ($request->filled('date_from')) {
            $allQuery->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $allQuery->where('start_date', '<=', $request->date_to);
        }

        $summary = [
            'total_transactions' => $allQuery->count(),
            'total_revenue' => $allQuery->sum('total'),
            'total_electricity' => $allQuery->sum('electricity_fee'),
        ];

        return view('admin.reports.index', compact('rentals', 'summary'));
    }
}
