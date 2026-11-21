<?php

namespace App\Services;

use App\Enums\VehicleType;
use App\Models\User;
use App\Models\Vehicle;

class VehicleService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $owner, array $data): Vehicle
    {
        $type = VehicleType::from($data['type']);

        return Vehicle::create([
            'user_id' => $owner->id,
            ...$this->attributes($type, $data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($this->attributes($vehicle->typeEnum(), $data));

        return $vehicle->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(VehicleType $type, array $data): array
    {
        return [
            'type' => $type->value,
            'plate' => $type->requiresPlate() ? ($data['plate'] ?? null) : null,
            'make' => $data['make'] ?? null,
            'model' => $data['model'] ?? null,
            'battery_capacity_kwh' => $type->isElectric()
                ? ($data['battery_capacity_kwh'] ?? null)
                : null,
        ];
    }
}
