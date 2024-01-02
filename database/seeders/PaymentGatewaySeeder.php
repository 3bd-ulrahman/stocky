<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentGateway::query()->insert([
            [
                'name' => 'stripe',
                'keys' => json_encode([
                    'STRIPE_KEY' => '',
                    'STRIPE_SECRET' => ''
                ])
            ],
            [
                'name' => 'checkout',
                'keys' => json_encode([
                    'CHECKOUT_PUBLIC_KEY' => '',
                    'CHECKOUT_SECRET_KEY' => '',
                    'CHECKOUT_CHANNEL_ID' => ''
                ])
            ],
        ]);
    }
}
