<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ReviewModerationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database with roles and other necessary data
        $this->seed(RolesTableSeeder::class);
    }

    /** @test */
    public function admin_can_approve_review()
    {
        // Create admin and review
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $token = auth()->tokenById($admin->id);
        $review = Review::factory()->create(['status' => 'pending']);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->patchJson("/api/reviews/{$review->id}/approve");

        
        
        // Assert the response
        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_can_reject_review()
    {
        // Create admin and review
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $token = auth()->tokenById($admin->id);
        $review = Review::factory()->create(['status' => 'pending']);

        // Act as the admin and reject the review
        $response = $this->actingAs($admin, 'api')
                        ->patchJson("/api/reviews/{$review->id}/reject");

        // Assert the response
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'rejected',
        ]);
    }

    public function test_it_returns_pending_reviews()
    {
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        // Generate a JWT token for the admin user
        $token = auth()->tokenById($admin->id);

        // Create a product and some reviews
        $product = Product::factory()->create();
        $pendingReview = Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
        $approvedReview = Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
        ]);

        // Retrieve pending reviews
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/reviews/pending');

        // Assert the correct response status
        $response->assertStatus(200);

        // Assert that the pending review is in the response
        $response->assertJsonFragment(['id' => $pendingReview->id]);

        // Assert that the approved review is not in the response
        $response->assertJsonMissing(['id' => $approvedReview->id]);
    }

    public function test_it_approves_a_pending_review()
{
    // Create an admin user
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    
    // Generate a JWT token for the admin user
    $token = auth()->tokenById($admin->id);

    // Create a product and a pending review
    $product = Product::factory()->create();
    $pendingReview = Review::factory()->create([
        'product_id' => $product->id,
        'status' => 'pending',
    ]);

    // Approve the pending review
    $response = $this->withHeaders([
        'Authorization' => "Bearer $token",
    ])->patchJson("/api/reviews/{$pendingReview->id}/approve");

    // Assert the correct response status
    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => 'Review approved successfully']);

    // Assert that the review status has been updated to "approved" in the database
    $this->assertDatabaseHas('reviews', [
        'id' => $pendingReview->id,
        'status' => 'approved',
    ]);
}



}
