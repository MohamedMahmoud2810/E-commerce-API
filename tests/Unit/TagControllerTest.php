<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();

        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($this->user, 'api');
    }

    public function testStoreTag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/tags', ['name' => 'New Tag']);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJsonStructure(['data' => ['id', 'name']]);

        $this->assertDatabaseHas('tags', ['name' => 'New Tag']);
    }

    public function testGetTags()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/tags');

        $response->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure(['data' => [['id', 'name']]]);
    }

    public function testUpdateTag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/tags/{$tag->id}", ['name' => 'Updated Name']);

        $response->assertStatus(Response::HTTP_OK)
                ->assertJson(['name' => 'Updated Name']);

        $this->assertDatabaseHas('tags', ['name' => 'Updated Name']);
    }

    public function testDeleteTag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/tags/{$tag->id}");

        $response->assertStatus(Response::HTTP_OK)
                ->assertJson(['message' => 'Tag deleted']);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

}
