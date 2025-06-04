<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @method hasPayments(int $count = 1)
 */
class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 1, 1000), // Random amount between 1 and 1000
            'number' => generateDonationNumber(),
            'frequency' => null, // Default frequency
            'info' => [
                'donor_name' => $this->faker->name(),
                'donor_email' => $this->faker->email(),
                'message' => $this->faker->sentence(),
            ],

            'type' => Donation::UNICA,
            'identifier' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Donation $donacion) {


            Payment::factory()->make([
                'number' => generatePaymentNumber($donacion),
                'payable_id' => $donacion->id,
                'payable_type' => Donation::class,
            ]);

            State::factory()->make([
                'name' => State::ACTIVA,
                'stateable_id_id' => $donacion->id,
                'stateable_type_type' => Donation::class,
            ]);
        })->afterCreating(function (Donation $donacion) {


            $donacion->states()->create([
                'name' => State::ACTIVA,
                'message' => 'Pago aceptado',
            ]);

        });
    }

    public function recurrente(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => DONATION::RECURRENTE,
                'identifier' => $this->faker->uuid(),
            ];
        });
    }

    public function withCertificado(): Factory
    {
        return $this->afterCreating(function (Donation $donacion) {
            $address = Address::factory()->create([
                'type' => ADDRESS::CERTIFICATE,
            ]);
            $donacion->addresses()->attach($address);
        });
    }

    public function withPayment(): Factory
    {
        return $this->afterCreating(function (Donation $donacion) {
            Payment::factory()->create([
                'number' => generatePaymentNumber($donacion),
                'payable_id' => $donacion->id,
                'payable_type' => Donation::class,
            ]);
        });
    }
}
