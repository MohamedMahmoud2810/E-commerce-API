<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ProductReviewed;
use App\Services\SpamDetector;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database with roles and other necessary data
        $this->seed(RolesTableSeeder::class);
    }

    /** @test */
    public function it_returns_reviews_for_a_product()
    {
        // Create a user and product
        $user = User::factory()->create();
        $user->assignRole('Customer');
        $token = auth()->tokenById($user->id);
        $product = Product::factory()->create();
        // Create an approved review for the product
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        // Act as the customer and make a request
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson("/api/products/{$product->id}/reviews");
        
        // dd($response->json());
        // Assert the response
        $response->assertStatus(200);
        $response->assertJsonFragment(['review' => $review->review]);
        
    }

    public function test_it_stores_a_review_for_a_product()
{
    // Create a customer role and assign it to a user
    $user = User::factory()->create();
    $user->assignRole('Customer');
    
    // Log the user in and create a product
    Auth::login($user);
    $product = Product::factory()->create();

    // Fake notifications to ensure they aren't actually sent
    Notification::fake();

    // Fake spam detection service (if applicable)
    $this->mock(SpamDetector::class, function ($mock) {
        $mock->shouldReceive('isSpam')->andReturn(false); // Assume no spam
    });

    // Prepare the data for the review
    $data = [
        'review' => 'This is a great product!',
        'rating' => 5,
    ];

    // Send the post request
    $response = $this->actingAs($user, 'api')->postJson("/api/products/{$product->id}/reviews", $data);

    // Assert that the review was stored and a success response is returned
    $response->assertStatus(201);
    $response->assertJson(['message' => 'Review submitted successfully']);

    // Assert that the review exists in the database
    $this->assertDatabaseHas('reviews', [
        'product_id' => $product->id,
        'user_id' => $user->id,
        'review' => $data['review'],
        'rating' => $data['rating'],
        'status' => 'approved',
    ]);

    // Assert that a notification was sent
    Notification::assertSentTo($product->vendor, ProductReviewed::class);
}




    
}
