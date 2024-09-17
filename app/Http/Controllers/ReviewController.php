<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use App\Notifications\ProductReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SpamDetector;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-01T12:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-01T12:00:00Z"),
 *     }
 * )
 */


/**
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     title="Review",
 *     description="Review model",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="product_id", type="integer", example=123, description="The ID of the product being reviewed"),
 *         @OA\Property(property="user_id", type="integer", example=456, description="The ID of the user who submitted the review"),
 *         @OA\Property(property="review", type="string", example="This product is excellent!", description="The text content of the review"),
 *         @OA\Property(property="rating", type="integer", example=5, description="The rating given to the product, on a scale of 1 to 5"),
 *         @OA\Property(property="is_spam", type="boolean", example=false, description="Indicates if the review is flagged as spam"),
 *         @OA\Property(property="status", type="string", example="approved", description="The status of the review (e.g., approved, pending)"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-01T12:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-01T12:00:00Z"),
 *     }
 * )
 */


class ReviewController extends Controller
{

    private $spamDetector;

    public function __construct(SpamDetector $spamDetector)
    {
        $this->spamDetector = $spamDetector;
    }

    /**
 * @OA\Post(
 *     path="/api/products/{productId}/reviews",
 *     operationId="storeReview",
 *     tags={"Reviews"},
 *     summary="Submit a review for a product",
 *     description="Allow authenticated users to submit a review for a product. The review is checked for spam, and the product owner is notified if it's approved.",
 *     @OA\Parameter(
 *         name="productId",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"review", "rating"},
 *             @OA\Property(property="review", type="string", example="Great product!", description="Text content of the review"),
 *             @OA\Property(property="rating", type="integer", example=5, description="Rating given by the user (1 to 5)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Review submitted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Review submitted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input data"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */

    public function store(Request $request, $productId)
    {
        $request->validate([
            'review' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $isSpam = $this->spamDetector->isSpam($request->review);

        $review = new Review([
            'product_id' => $productId,
            'user_id' => Auth::id(),
            'review' => $request->review,
            'rating' => $request->rating,
            'is_spam' => $isSpam,
            'status' => $isSpam ? 'pending' : 'approved',
        ]);
        $review->save();
        $productOwnerId = $review->product->vendor_id;
        
        $cachedNotificationKey = "product_owner_{$productOwnerId}_review_{$review->id}";
        if (!cache()->has($cachedNotificationKey)) {
            $productOwner = User::find($productOwnerId);
            $productOwner->notify(new ProductReviewed($review));
            cache()->put($cachedNotificationKey, true, now()->addMinutes(10)); // Cache for 10 minutes
        }

        return response()->json(['message' => 'Review submitted successfully'], 201);
    }

/**
 * @OA\Get(
 *     path="/api/products/{productId}/reviews",
 *     operationId="getProductReviews",
 *     tags={"Reviews"},
 *     summary="Get all reviews and average rating for a product",
 *     description="Fetch all approved reviews and the average rating for a specific product. Reviews are cached to improve performance.",
 *     @OA\Parameter(
 *         name="productId",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="reviews", type="array", @OA\Items(ref="#/components/schemas/Review")),
 *             @OA\Property(property="average_rating", type="number", format="float", example=4.5, description="Average rating of the product")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */


    public function show($productId)
    {
        $cacheKeyReviews = "product_reviews_{$productId}";
        $cacheKeyAverageRating = "product_average_rating_{$productId}";

        $reviews = cache()->remember($cacheKeyReviews, 600, function () use ($productId) {
            return Review::where('product_id', $productId)
                ->where('status', 'approved')
                ->with('user')
                ->get();
        });
        $averageRating = cache()->remember($cacheKeyAverageRating, 600, function () use ($productId) {
            return Review::where('product_id', $productId)
                ->where('status', 'approved')
                ->avg('rating');
        });
        
        return response()->json([
            'reviews' => $reviews,
            'average_rating' => $averageRating,
        ]);
    }

}
