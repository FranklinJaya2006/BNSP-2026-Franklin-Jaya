<?php

namespace App\Services;

use App\Models\PaymentProof;
use App\Models\Rental;
use App\Models\RentalExtension;
use Illuminate\Http\UploadedFile;

/** Simpan file bukti bayar di penyimpanan privat. */
class PaymentProofService
{
    public function storeForRental(Rental $rental, UploadedFile $file): PaymentProof
    {
        return $this->store($file, $rental->id, null);
    }

    public function storeForExtension(
        Rental $rental,
        RentalExtension $extension,
        UploadedFile $file,
    ): PaymentProof {
        return $this->store($file, null, $extension->id);
    }

    private function store(UploadedFile $file, ?int $rentalId, ?int $extensionId): PaymentProof
    {
        $objectKey = $file->store('payment-proofs', 'private');

        return PaymentProof::create([
            'rental_id' => $rentalId,
            'extension_id' => $extensionId,
            'object_key' => $objectKey,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);
    }
}
