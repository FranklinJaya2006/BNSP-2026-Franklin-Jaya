<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExtendRentalRequest;
use App\Models\Rental;
use App\Services\BillingService;
use App\Services\RentalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/** Tampilkan transaksi dan urus perpanjangan sewa. */
class RentalController extends Controller
{
    public function __construct(private RentalService $rentals) {}

    public function index(Request $request): View
    {
        $query = Rental::with(['user', 'vehicle', 'garage'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('garage_type')) {
            $query->whereHas('garage', fn ($q) => $q->where('type', $request->input('garage_type')));
        }
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->input('date_to'));
        }

        $rentals = $query->paginate(20)->withQueryString();

        return view('admin.rentals.index', compact('rentals'));
    }

    public function show(int $id): View
    {
        $rental = Rental::with([
            'user', 'vehicle', 'garage',
            'extensions.createdBy',
            'paymentProofs',
        ])->findOrFail($id);

        return view('admin.rentals.show', compact('rental'));
    }

    /** Form perpanjangan */
    public function extendForm(int $id, BillingService $billing): View|RedirectResponse
    {
        $rental = Rental::with(['garage'])->findOrFail($id);

        if (! $rental->isActive()) {
            return redirect()->route('admin.rentals.show', $id)
                ->with('error', 'Transaksi tidak aktif, tidak bisa diperpanjang.');
        }

        $minimumEnd = $rental->end_date->copy()->addDay();

        return view('admin.rentals.extend', [
            'rental' => $rental,
            'minimumEnd' => $minimumEnd->toDateString(),
            'monthlyEnd' => $rental->rentalTypeEnum()->isMonthly()
                ? $billing->monthlyEndDate($minimumEnd)->toDateString()
                : null,
        ]);
    }

    /** Simpan perpanjangan */
    public function extend(ExtendRentalRequest $request, int $id): RedirectResponse
    {
        $rental = Rental::with(['garage'])->findOrFail($id);

        if (! $rental->isActive()) {
            return back()->with('error', 'Transaksi tidak aktif.');
        }

        try {
            $this->rentals->extend(
                $rental,
                auth()->user(),
                $request->file('payment_proof'),
                Carbon::parse($request->validated('new_end')),
                Carbon::parse($request->validated('previous_end')),
            );
        } catch (RuntimeException $exception) {
            return back()
                ->withErrors(['new_end' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('admin.rentals.show', $id)
            ->with('success', 'Perpanjangan berhasil disimpan.');
    }
}
