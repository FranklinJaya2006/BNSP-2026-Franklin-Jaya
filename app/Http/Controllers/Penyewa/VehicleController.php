<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Atur kendaraan milik penyewa yang login. */
class VehicleController extends Controller
{
    public function __construct(private VehicleService $vehicles) {}

    public function index(): View
    {
        $vehicles = Vehicle::ownedBy(auth()->id())->orderByDesc('created_at')->get();

        return view('penyewa.vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('penyewa.vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $this->vehicles->create(auth()->user(), $request->validated());

        return redirect()->route('penyewa.vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $vehicle = Vehicle::ownedBy(auth()->id())->findOrFail($id);

        return view('penyewa.vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, int $id): RedirectResponse
    {
        $vehicle = Vehicle::ownedBy(auth()->id())->findOrFail($id);
        $this->vehicles->update($vehicle, $request->validated());

        return redirect()->route('penyewa.vehicles.index')
            ->with('success', 'Kendaraan berhasil diperbarui.');
    }
}
