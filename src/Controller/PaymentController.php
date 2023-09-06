<?php

namespace App\Controller;

use App\Manager\ProductManager;
use App\Service\StripeService;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'app_payment')]
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }

    #[Route('/new', name: 'app_payment_new')]
    public function new(): Response
    {
        return $this->render('payment/new.html.twig');
    }

    #[Route('/get_client_secret', methods: ["POST"])]
    public function send_payment_intent(StripeService $stripeService, Request $request): JsonResponse
    {
        /**
         * @var PaymentIntent $paymentIntent;
         */
        $paymentIntent = $stripeService->paymentIntent($request);

        return $this->json(['clientSecret' => $paymentIntent->client_secret], 200);
    }

    #[Route('/post_payment', name: 'app_post_payment', methods: ["POST"])]
    public function post_payment(ProductManager $productManager, Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            $resource = $productManager->startPayment($_POST);

            if ($resource !== null) {
                $productManager->save_order($resource);

                return $this->redirectToRoute('app_payment', [
                    'message' => 'Payment completed',
                ]);
            }
        }
        return $this->redirectToRoute('payment');
    }

    #[Route('/payment_success')]
    public function payment_success(Request $request): Response
    {
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_PRIVATE_KEY']);
        dump($request->getUri());
        dd(\Stripe\PaymentIntent::retrieve($request->get('payment_intent')));
        return $this->render('payment/payment_success.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
