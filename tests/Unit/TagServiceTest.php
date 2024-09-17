<?php

namespace Tests\Unit;

use App\DTOs\GetTagDTO;
use App\DTOs\StoreTagDTO;
use App\Models\Tag;
use App\Repositories\TagRepository;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Mockery;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TagService $tagService;
    protected $tagRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepositoryMock = Mockery::mock(TagRepository::class);
        $this->tagService = new TagService($this->tagRepositoryMock);
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

        $this->tagRepositoryMock
            ->shouldReceive('getAllTags')
            ->with($dto)
            ->andReturn($paginator);

        $result = $this->tagService->getAllTags($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    public function testUpdateTag()
    {
        $tag = Tag::factory()->create(['name' => 'Old Name']);
        $dto = new StoreTagDTO(['name' => 'Updated Name']);

        // Prepare the updated tag
        $updatedTag = $tag->replicate();
        $updatedTag->name = 'Updated Name';
        $updatedTag->save();

        $this->tagRepositoryMock
            ->shouldReceive('getTagById')
            ->with($tag->id)
            ->andReturn($tag);
        $this->tagRepositoryMock
            ->shouldReceive('updateTag')
            ->with($tag, $dto)
            ->andReturn($updatedTag);

        $result = $this->tagService->updateTag($tag->id, $dto);

        $this->assertEquals('Updated Name', $result->name);
    }
}
