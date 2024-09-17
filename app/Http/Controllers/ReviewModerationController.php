<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Review Moderation",
 *     description="Endpoints for moderating reviews"
 * )
 */
class ReviewModerationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/reviews/pending",
     *     operationId="getPendingReviews",
     *     tags={"Review Moderation"},
     *     summary="Get all pending reviews",
     *     description="Fetches all reviews that are pending for moderation.",
     *     @OA\Response(
     *         response=200,
     *         description="List of pending reviews",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function index()
    {
        $reviews = Review::pending()->get();

        return response()->json($reviews);
    }
 /**
     * @OA\Post(
     *     path="/api/reviews/{reviewId}/approve",
     *     operationId="approveReview",
     *     tags={"Review Moderation"},
     *     summary="Approve a review",
     *     description="Updates the status of a review to 'approved'.",
     *     @OA\Parameter(
     *         name="reviewId",
     *         in="path",
     *         required=true,
     *         description="ID of the review to be approved",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review approved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return response()->json(['message' => 'Review approved successfully']);
    }
/**
     * @OA\Post(
     *     path="/api/reviews/{reviewId}/reject",
     *     operationId="rejectReview",
     *     tags={"Review Moderation"},
     *     summary="Reject a review",
     *     description="Updates the status of a review to 'rejected'.",
     *     @OA\Parameter(
     *         name="reviewId",
     *         in="path",
     *         required=true,
     *         description="ID of the review to be rejected",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review rejected successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        return response()->json(['message' => 'Review rejected successfully']);
    }
}
