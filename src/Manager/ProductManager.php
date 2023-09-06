<?php

namespace App\Manager;

use App\Service\StripeService;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Request;

class ProductManager
{

    public function __construct(private StripeService $stripeService)
    {
    }

    public function startPayment(array $stripeParameter): array
    {
        $ressource = null;

        $data = $this->stripeService->payViaStripe($stripeParameter);

        if ($data) {
            $ressource = [
                'stripeBrand' => $data['charges']['data'][0]['payment_method_details']['card']['brand'],
                'stripeLast4' => $data['charges']['data'][0]['payment_method_details']['card']['last4'],
                'stripeId' => $data['charges']['data'][0]['id'],
                'stripeStatus' => $data['charges']['data'][0]['status'],
                'stripeToken' => $data['client_secret'],
            ];
        }

        return $ressource;
    }

    public function save_order(array $ressource): void
    {
        return;
    }
}
