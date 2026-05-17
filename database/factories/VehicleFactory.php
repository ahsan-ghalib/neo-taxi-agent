<?php

namespace Database\Factories;

use App\Enums\VehicleStatus;
use App\Enums\VehicleType;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /** @var array<string, array<string>> */
    private static array $makeModels = [
        'Toyota' => ['Camry', 'Corolla', 'Prius', 'Yaris'],
        'Mercedes' => ['E-Class', 'C-Class', 'S-Class', 'Vito'],
        'BMW' => ['3 Series', '5 Series', '7 Series', 'X5'],
        'Volkswagen' => ['Passat', 'Golf', 'Transporter'],
        'Ford' => ['Focus', 'Mondeo', 'Transit'],
    ];

    public function definition(): array
    {
        $make = fake()->randomElement(array_keys(self::$makeModels));
        $model = fake()->randomElement(self::$makeModels[$make]);
        $type = fake()->randomElement(VehicleType::cases());

        return [
            'driver_id' => Driver::factory(),
            'vehicle_type' => $type,
            'make' => $make,
            'model' => $model,
            'year' => fake()->numberBetween(2018, 2025),
            'color' => fake()->safeColorName(),
            'plate_number' => strtoupper(fake()->unique()->bothify('??-###-??')),
            'capacity' => match ($type) {
                VehicleType::Van => fake()->numberBetween(6, 8),
                VehicleType::Business => 4,
                default => fake()->numberBetween(3, 4),
            },
            'status' => VehicleStatus::Active,
        ];
    }

    public function standard(): static
    {
        return $this->state(fn () => [
            'vehicle_type' => VehicleType::Standard,
            'capacity' => 4,
        ]);
    }

    public function business(): static
    {
        return $this->state(fn () => [
            'vehicle_type' => VehicleType::Business,
            'capacity' => 4,
        ]);
    }

    public function van(): static
    {
        return $this->state(fn () => [
            'vehicle_type' => VehicleType::Van,
            'capacity' => 7,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => VehicleStatus::Inactive]);
    }
}
