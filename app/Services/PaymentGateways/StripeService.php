<?php

namespace App\Services\PaymentGateways;

use Illuminate\Http\Request;
use Stripe\Stripe;

class StripeService
{
    public function pay(Request $request)
    {
        Stripe::setApiKey(config('payment.STRIPE_SECRET'));
        \Stripe\Charge::create([
            'amount' => $request->amount * 100,
            'currency' => 'usd',
            'source' => $request->token]
        );
    }
}
