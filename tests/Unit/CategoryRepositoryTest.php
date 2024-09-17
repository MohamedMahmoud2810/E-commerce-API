<?php

namespace Tests\Unit;

use App\DTOs\StoreCategoryDTO;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $categoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = new CategoryRepository();
    }

    /** @test */
    public function it_can_create_category()
    {
        $dto = new StoreCategoryDTO(['name' => 'Electronics']);
        $category = $this->categoryRepository->createCategory($dto);

        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
        $this->assertInstanceOf(Category::class, $category);
    }

    public function it_can_get_category_by_id()
    {
        $category = Category::factory()->create();

        $result = $this->categoryRepository->getCategoryById($category->id);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($category->id, $result->id);
    }

    public function it_can_update_category()
    {
        $category = Category::factory()->create();
        $dto = new StoreCategoryDTO(['name' => 'Updated Category Name']);

        $updatedCategory = $this->categoryRepository->updateCategory($category, $dto);

        $this->assertEquals('Updated Category Name', $updatedCategory->name);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Category Name']);
    }

    public function it_can_delete_category()
    {
        $category = Category::factory()->create();

        $result = $this->categoryRepository->deleteCategory($category->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

}
