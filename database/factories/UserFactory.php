<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $genders = collect(Gender::values());

        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'sub' => 'auth|' . Str::random(10),
            'gender' => $genders->random(),
            'remember_token' => Str::random(10),
        ];
    }
}
