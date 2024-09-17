<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesTableSeeder::class);
        // Creating roles and users
        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole('Admin');

        $this->vendor = User::factory()->create(['email_verified_at' => now()]);
        $this->vendor->assignRole('Vendor');

        $this->assertTrue($this->admin->hasRole('Admin'));
        $this->assertTrue($this->vendor->hasRole('Vendor'));

        // Acting as the admin user for most tests
        $this->actingAs($this->admin, 'api');
    }

    /** @test */
    public function admin_can_view_products_list()
    {
        $response = $this->getJson(route('products.index'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id', 'name', 'price', 'stock', 'category', 'tag', 'vendor'
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function test_vendor_can_create_product()
    {
        $this->actingAs($this->vendor, 'api');

        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        

        $productData = [
            'name' => 'New Product',
            'price' => 100,
            'stock' => 10,
            'description' => 'Test Description',
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'vendor_id' => $this->vendor->id,
        ];

        $response = $this->postJson(route('products.store'), $productData);
        

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'name', 'price', 'stock', 'description', 'category', 'tag', 'vendor'
                    ]
                ]);
    }


    /** @test */
    public function admin_can_update_product()
    {
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated Product',
            'price' => 150,
            'stock' => 5,
            'description' => 'Updated Description',
        ];

        $response = $this->putJson(route('products.update', $product->id), $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $product->id,
                         'name' => 'Updated Product',
                         'price' => 150,
                         'stock' => 5,
                         'description' => 'Updated Description',
                     ]
                 ]);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson(route('products.destroy', $product->id));

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Product deleted successfully',
                 ]);
    }

    /** @test */
    public function unauthorized_user_cannot_access_products_routes()
    {
        $this->actingAs(User::factory()->create(), 'api'); // Non-admin user

        $response = $this->getJson(route('products.index'));
        $response->assertStatus(403); // Forbidden

        $response = $this->postJson(route('products.store'));
        $response->assertStatus(403); // Forbidden
    }
}
