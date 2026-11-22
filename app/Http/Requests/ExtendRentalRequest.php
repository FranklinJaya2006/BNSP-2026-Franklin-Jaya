<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Cek tanggal selesai baru dan file bukti bayar. */
class ExtendRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'previous_end' => ['required', 'date_format:Y-m-d'],
            'new_end' => ['required', 'date_format:Y-m-d', 'after:previous_end'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }
}
