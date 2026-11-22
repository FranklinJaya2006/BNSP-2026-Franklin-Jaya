<?php

namespace App\Contracts;

use App\DTOs\ExtensionQuote;
use App\DTOs\RentalQuote;
use App\DTOs\RentalQuoteRequest;
use App\Models\Garage;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

interface RentalManager
{
    public function quote(Vehicle $vehicle, Garage $garage, RentalQuoteRequest $request): RentalQuote;

    public function create(
        User $tenant,
        Vehicle $vehicle,
        Garage $garage,
        RentalQuoteRequest $request,
        UploadedFile $paymentProof,
    ): Rental;

    public function quoteExtension(Rental $rental, Carbon $newEnd): ExtensionQuote;

    public function extend(
        Rental $rental,
        User $reviewer,
        UploadedFile $paymentProof,
        Carbon $newEnd,
        Carbon $expectedEnd,
    ): RentalExtension;
}
