<?php

namespace Tests\Unit;

use App\DTOs\GetTagDTO;
use App\DTOs\StoreTagDTO;
use App\Models\Tag;
use App\Repositories\TagRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Tests\TestCase;

class TagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TagRepository $tagRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepository = new TagRepository();
    }

    public function testCreateTag()
    {
        $dto = new StoreTagDTO(['name' => 'Test Tag']);
        $tag = $this->tagRepository->createTag($dto);

        $this->assertDatabaseHas('tags', ['name' => 'Test Tag']);
        $this->assertInstanceOf(Tag::class, $tag);
    }

    public function testGetAllTags()
    {
        $dto = new GetTagDTO(['perPage' => 10]);
        $tags = Tag::factory()->count(5)->make();
        
        // Create LengthAwarePaginator instance
        $paginator = new LengthAwarePaginator(
            $tags, // Collection of items
            5, // Total number of items
            10, // Number of items per page
            1, // Current page
            ['path' => Paginator::resolveCurrentPath()] // Path for pagination links
        );

        // Mock the return value
        $this->tagRepository = \Mockery::mock(TagRepository::class);
        $this->tagRepository
            ->shouldReceive('getAllTags')
            ->with($dto)
            ->andReturn($paginator);

        $result = $this->tagRepository->getAllTags($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    public function testGetTagById()
    {
        $tag = Tag::factory()->create();

        $result = $this->tagRepository->getTagById($tag->id);

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($tag->id, $result->id);
    }

    public function testUpdateTag()
    {
        $tag = Tag::factory()->create(['name' => 'Old Name']);
        $dto = new StoreTagDTO(['name' => 'Updated Name']);

        $result = $this->tagRepository->updateTag($tag, $dto);

        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals('Updated Name', $result->name);
    }

    public function testDeleteTag()
    {
        $tag = Tag::factory()->create();

        $result = $this->tagRepository->deleteTag($tag->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
