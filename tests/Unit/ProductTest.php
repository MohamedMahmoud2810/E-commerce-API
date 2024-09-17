<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_create_a_product()
    {
        // Create necessary related models
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $vendor = User::factory()->create();

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'vendor_id' => $vendor->id,
        ]);

        // Assertions
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
        ]);
    }

    public function it_can_update_a_product()
    {
        // Create necessary related models
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $vendor = User::factory()->create();

        // Create a product
        $product = Product::create([
            'name' => 'Original Product',
            'description' => 'This is the original product.',
            'price' => 100.00,
            'stock' => 5,
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'vendor_id' => $vendor->id,
        ]);

        // Update the product
        $product->update([
            'name' => 'Updated Product',
            'price' => 200.00,
        ]);

        // Assertions
        $this->assertDatabaseHas('products', [
            'name' => 'Updated Product',
            'price' => 200.00,
        ]);
    }

    public function it_can_delete_a_product()
    {
        // Create necessary related models
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $vendor = User::factory()->create();

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 50.00,
            'stock' => 20,
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'vendor_id' => $vendor->id,
        ]);

        // Delete the product
        $product->delete();

        // Assertions
        $this->assertDatabaseMissing('products', [
            'name' => 'Test Product',
        ]);
    }


    public function it_belongs_to_a_category()
    {
        // Create necessary related models
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $vendor = User::factory()->create();

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 75.00,
            'stock' => 15,
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'vendor_id' => $vendor->id,
        ]);

        // Assertions
        $this->assertInstanceOf(Category::class, $product->category);
    }

    public function it_belongs_to_a_tag()
    {
        // Create necessary related models
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $vendor = User::factory()->create();

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 85.00,
            'stock' => 8,
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'vendor_id' => $vendor->id,
        ]);

        // Assertions
        $this->assertInstanceOf(Tag::class, $product->tag);
    }
    
}
