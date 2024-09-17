<?php

namespace Tests\Unit;

use App\DTOs\GetCategoryDTO;
use App\DTOs\StoreCategoryDTO;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $categoryService;
    protected $categoryRepository;

    public function setUp(): void
{
    parent::setUp();

    $this->categoryRepository = Mockery::mock(CategoryRepository::class);
    $this->categoryService = new CategoryService($this->categoryRepository);
}


    public function it_can_create_category()
    {
        $dto = new StoreCategoryDTO(['name' => 'Electronics']);
        $category = Category::factory()->make();

        $this->CategoryRepository
            ->shouldReceive('createCategory')
            ->with($dto)
            ->andReturn($category);

        $result = $this->categoryService->createCategory($dto);

        $this->assertInstanceOf(Category::class, $result);
    }

    public function it_can_get_category_by_id()
    {
        $category = Category::factory()->create();

        $this->categoryRepository
            ->shouldReceive('getCategoryById')
            ->with($category->id)
            ->andReturn($category);

        $result = $this->categoryService->getCategoryById($category->id);

        $this->assertEquals($category->id, $result->id);
    }

    public function it_can_update_category()
    {
        $existingCategory = Category::factory()->create();
        $dto = new StoreCategoryDTO(['name' => 'Updated Category Name']);

        $this->categoryRepository
            ->shouldReceive('getCategoryById')
            ->with($existingCategory->id)
            ->andReturn($existingCategory);

        $this->categoryRepository
            ->shouldReceive('updateCategory')
            ->with($existingCategory, $dto)
            ->andReturn($existingCategory->update($dto->toArray()));

        $result = $this->categoryService->updateCategory($existingCategory->id, $dto);

        $this->assertEquals('Updated Category Name', $result->name);
    }

    public function it_can_delete_category()
    {
        $category = Category::factory()->create();

        $this->categoryRepository
            ->shouldReceive('getCategoryById')
            ->with($category->id)
            ->andReturn($category);

        $this->categoryRepository
            ->shouldReceive('deleteCategory')
            ->with($category->id)
            ->andReturn(true);

        $result = $this->categoryService->deleteCategory($category->id);

        $this->assertTrue($result);
    }


}
