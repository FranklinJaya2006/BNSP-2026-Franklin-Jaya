<?php

namespace App\Http\Requests;

class StoreRentalRequest extends PreviewRentalRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }
}
