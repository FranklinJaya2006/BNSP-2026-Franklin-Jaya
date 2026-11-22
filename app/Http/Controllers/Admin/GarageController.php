<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGarageRequest;
use App\Http\Requests\UpdateGarageRequest;
use App\Models\Garage;
use App\Services\GarageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Atur daftar, jumlah, dan tarif garasi. */
class GarageController extends Controller
{
    public function __construct(private GarageService $garages) {}

    public function index(Request $request): View
    {
        $query = Garage::query()->orderBy('code');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $garages = $query->paginate(20)->withQueryString();

        return view('admin.garages.index', compact('garages'));
    }

    public function create(): View
    {
        return view('admin.garages.create');
    }

    public function store(StoreGarageRequest $request): RedirectResponse
    {
        $quantity = $request->integer('quantity', 1);
        $this->garages->createBatch($request->validated(), $quantity);

        return redirect()->route('admin.garages.index')
            ->with('success', "{$quantity} garasi berhasil ditambahkan.");
    }

    public function edit(int $id): View
    {
        $garage = Garage::findOrFail($id);

        return view('admin.garages.edit', [
            'garage' => $garage,
            'ratesLocked' => $garage->isOccupiedToday(),
        ]);
    }

    public function update(UpdateGarageRequest $request, int $id): RedirectResponse
    {
        $garage = Garage::findOrFail($id);
        $this->garages->update($garage, $request->validated());

        return redirect()->route('admin.garages.index')
            ->with('success', 'Garasi berhasil diperbarui.');
    }
}
