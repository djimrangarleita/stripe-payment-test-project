<?php

namespace App\Service;

use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Request;

class StripeService
{
    private string $privateKey;
    public function __construct()
    {
        $this->privateKey = $_ENV['STRIPE_PRIVATE_KEY'];
    }

    public function paymentIntent(Request $request): PaymentIntent
    {
        \Stripe\Stripe::setApiKey($this->privateKey);

        // TODO: Wrap in try catch
        return \Stripe\PaymentIntent::create([
            'amount' => 100000,
            'currency' => $_ENV['PAYMENT_CURRENCY'],
            'payment_method_types' => ['card'],
            'description' => "Cart#125",
            'billing_details' => [
                'name' => 'DNS',
                'email' => "djimra@mossosouk.com",
                "address" => [],
            ],
            'shipping' => [
                'name' => 'Djimra NGARLEITA',
                'phone' => '23563257178',
                'tracking_number' => '',
                'address' => [
                    "city" => "N'Djamena",
                    "country" => "TD",
                    "line1" => "Route de la corniche",
                    // "line2" => "Immeuble Mossosouk",
                    // "postal_code" => "",
                    // "state" => "",
                ],
            ],
            'receipt_email' => 'djimra@mossosouk.com',
            'meta_data' => [
                // 'order' => "#h1897",
            ],
        ]);
    }

    public function payment(
        int $amount,
        string $currency,
        string $description,
        array $stripeParameter,
    ): PaymentIntent|null {
        \Stripe\Stripe::setApiKey($this->privateKey);
        $paymentIntent = null;

        if (isset($stripeParameter['stripeIntentId'])) {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($stripeParameter['stripeIntentId']);
            dd($paymentIntent);
        }
        if ($stripeParameter['stripeIntentStatus'] === 'succeeded') {
            # code...
        } else {
            $paymentIntent?->cancel();
        }

        return $paymentIntent;
    }

    public function payViaStripe(array $stripeParameter): PaymentIntent|null
    {
        return $this->payment(
            100000,
            $_ENV['PAYMENT_CURRENCY'],
            'A test product',
            $stripeParameter,
        );
    }
}
