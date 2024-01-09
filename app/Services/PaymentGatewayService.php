<?php

namespace App\Services;

use App\Services\PaymentGateways\CheckoutService;
use App\Services\PaymentGateways\StripeService;

class PaymentGatewayService
{
    public function stripe()
    {
        return (new StripeService());
    }

    public function checkout()
    {
        return (new CheckoutService());
    }
}
