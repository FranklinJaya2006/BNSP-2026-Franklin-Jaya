<?php

namespace App\Http\Controllers\Penyewa;

use App\Contracts\BillingCalculator;
use App\Contracts\RentalManager;
use App\DTOs\RentalQuoteRequest;
use App\Enums\RentalType;
use App\Http\Controllers\Controller;
use App\Http\Requests\PreviewRentalRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Models\Garage;
use App\Models\Rental;
use App\Models\Vehicle;
use App\Services\GarageService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class RentalController extends Controller
{
    public function __construct(
        private RentalManager $rentals,
        private BillingCalculator $billing,
        private GarageService $garages,
    ) {}

    /** Daftar sewa milik penyewa */
    public function index(): View
    {
        $rentals = Rental::with(['garage', 'vehicle', 'extensions'])
            ->ownedBy(auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('penyewa.rentals.index', compact('rentals'));
    }

    /** Form buat sewa baru */
    public function create(Request $request): View
    {
        $vehicles = Vehicle::ownedBy(auth()->id())->get();
        $selectedVehicle = $request->filled('vehicle_id')
            ? $vehicles->find($request->integer('vehicle_id'))
            : null;

        $garages = collect();
        $rentalType = RentalType::tryFrom((string) $request->input('rental_type'));
        $startDate = Carbon::today();

        if ($request->filled('start_date') && Carbon::hasFormat((string) $request->input('start_date'), 'Y-m-d')) {
            $startDate = Carbon::parse((string) $request->input('start_date'));
        }

        if ($selectedVehicle && $request->filled('start_date') && $rentalType !== null) {
            $days = $rentalType->isMonthly() ? 1 : (int) $request->input('days', 1);
            $endDate = $rentalType->isMonthly()
                ? $this->billing->monthlyEndDate($startDate)
                : $this->billing->dailyEndDate($startDate, $days);

            $garages = $this->garages->availableFor($selectedVehicle, $startDate, $endDate);
        }

        return view('penyewa.rentals.create', [
            'vehicles' => $vehicles,
            'selectedVehicle' => $selectedVehicle,
            'garages' => $garages,
            'maxDays' => $this->billing->maxDailyDays($startDate),
        ]);
    }

    /** Preview harga sebelum submit (AJAX / redirect) */
    public function preview(PreviewRentalRequest $request): View
    {
        $vehicle = Vehicle::ownedBy(auth()->id())->findOrFail($request->integer('vehicle_id'));
        $garage = Garage::findOrFail($request->integer('garage_id'));

        $quoteRequest = RentalQuoteRequest::fromArray($request->validated());
        $quote = $this->rentals->quote($vehicle, $garage, $quoteRequest);

        return view('penyewa.rentals.preview', [
            'vehicle' => $vehicle,
            'garage' => $garage,
            'startDate' => $quoteRequest->startDate,
            'endDate' => $quote->endDate,
            'rentalType' => $quoteRequest->rentalType->value,
            'days' => $quoteRequest->effectiveDays(),
            'result' => $quote->toArray(),
            'input' => $request->all(),
        ]);
    }

    /** Simpan transaksi sewa baru */
    public function store(StoreRentalRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::ownedBy(auth()->id())->findOrFail($request->integer('vehicle_id'));
        $garage = Garage::query()->active()->findOrFail($request->integer('garage_id'));

        $quoteRequest = RentalQuoteRequest::fromArray($request->validated());

        try {
            $this->rentals->create(
                auth()->user(),
                $vehicle,
                $garage,
                $quoteRequest,
                $request->file('payment_proof'),
            );
        } catch (RuntimeException $exception) {
            return back()
                ->withErrors(['garage_id' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('penyewa.rentals.index')
            ->with('success', 'Sewa berhasil disimpan!');
    }

    /** Detail sewa */
    public function show(int $id): View
    {
        $rental = Rental::with(['garage', 'vehicle', 'extensions.createdBy', 'paymentProofs'])
            ->ownedBy(auth()->id())
            ->findOrFail($id);

        $this->authorize('view', $rental);

        return view('penyewa.rentals.show', compact('rental'));
    }
}
