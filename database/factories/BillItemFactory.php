<?php

namespace Database\Factories;

use App\Models\BillItem;
use App\Models\SplitBill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillItemFactory extends Factory
{
    protected $model = BillItem::class;

    public function definition(): array
    {
        return [
            'split_bill_id' => SplitBill::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(10000, 100000),
            'status' => 'unpaid',
            'proof_path' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }
}
