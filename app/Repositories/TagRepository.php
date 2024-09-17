<?php

namespace App\Repositories;

use App\DTOs\GetTagDTO;
use App\DTOs\StoreTagDTO;
use App\Models\Tag;
use Illuminate\Pagination\LengthAwarePaginator;

class TagRepository
{
    public function getAllTags(GetTagDTO $dto): LengthAwarePaginator  
    {
        $query = Tag::query();
        return $query->paginate($dto->perPage);
    }

    public function getTagById($id): ?Tag
    {
        return Tag::find($id);
    }

    public function createTag(StoreTagDTO $dto): Tag
    {
        $tag = Tag::create($dto->toArray());
        return $tag->refresh();
    }

    public function updateTag(Tag $tag, StoreTagDTO $dto)
    {
        if ($tag) {
            $tag->update($dto->toArray());
            return $tag;
        }
        return null;
    }

    public function deleteTag($id)
    {
        $tag = Tag::find($id);
        if ($tag) {
            $tag->delete();
            return true;
        }
        return false;
    }
}
