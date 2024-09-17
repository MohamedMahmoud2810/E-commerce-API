<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($this->user, 'api');
    }



    /** @test */
    public function it_can_create_category()
    {
        $response = $this->postJson('/api/categories', ['name' => 'Electronics']);
        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertJsonStructure(['data' => ['id', 'name', 'created_at', 'updated_at']]);
    }

    /** @test */
    public function it_can_get_categories()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonFragment(['name' => $category->name]);
    }

    /** @test */
    public function it_can_get_category_by_id()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonFragment(['name' => $category->name]);
    }

    /** @test */
    public function it_can_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/categories/{$category->id}", ['name' => 'Updated Name']);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonFragment(['name' => 'Updated Name']);
    }

    /** @test */
    public function it_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson(['message' => 'Category deleted']);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
