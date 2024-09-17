<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    /**
 * @OA\Post(
 *     path="/api/create-payment-intent",
 *     operationId="createPaymentIntent",
 *     tags={"Payments"},
 *     summary="Create a payment intent",
 *     description="Create a payment intent with Stripe, required for client-side payment confirmation.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"amount"},
 *             @OA\Property(property="amount", type="integer", example=1000, description="Amount in cents (e.g., $10 is 1000 cents)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Payment intent created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="client_secret", type="string", example="sk_test_4eC39HqLyjWDarjtT1zdp7dc"),
 *             @OA\Property(property="payment_intent_id", type="string", example="pi_1FJzFG2eZvKYlo2C0z8h5TuQ")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request data"
 *     )
 * )
 */

    public function createPaymentIntent(Request $request)
    {
        // Validate amount is required in cents (e.g., $10 is 1000 cents)
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);
    
        // Create a payment intent with Stripe using manual confirmation
        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount,
            'currency' => 'usd',
            'payment_method_types' => ['card'], // Specify payment methods
            'confirmation_method' => 'manual', // Manually confirm the payment
            'confirm' => false, // Do not confirm immediately
        ]);
    
        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ], 201);
    }
    

/**
 * @OA\Post(
 *     path="/api/confirm-payment",
 *     operationId="confirmPayment",
 *     tags={"Payments"},
 *     summary="Confirm a payment",
 *     description="Confirm a payment intent with the provided payment method.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"payment_intent_id", "payment_method"},
 *             @OA\Property(property="payment_intent_id", type="string", example="pi_1FJzFG2eZvKYlo2C0z8h5TuQ", description="Payment Intent ID from create-payment-intent response"),
 *             @OA\Property(property="payment_method", type="string", example="pm_card_visa", description="Payment method ID used for confirmation")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment confirmed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="succeeded", description="The status of the payment intent")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request data"
 *     )
 * )
 */

    public function confirmPayment(Request $request)
    {
        // Validate the request
        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        // Retrieve the payment intent by ID from the request
        $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

        // Confirm the payment intent with the payment method
        $paymentIntent->confirm([
            'payment_method' => $request->payment_method,
        ]);

        // Return the status of the payment intent
        return response()->json([
            'status' => $paymentIntent->status,
        ]);
    }

}
