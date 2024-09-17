<?php

namespace App\Services;

use App\DTOs\GetTagDTO;
use App\DTOs\StoreTagDTO;
use App\Models\Tag;
use App\Repositories\TagRepository;
use Illuminate\Http\Response;


class TagService
{

public function __construct(protected TagRepository $tagRepository)
{
}

public function getAllTags(GetTagDTO $dto)
{
    return $this->tagRepository->getAllTags($dto);
}

public function getTagById($id)   
{
    $tag = $this->tagRepository->getTagById($id);
    if (!$tag) {
        return response('Tag not found', Response::HTTP_NOT_FOUND);
    }
    return $tag;
}

public function createTag(StoreTagDTO $dto)
{
    return $this->tagRepository->createTag($dto);
}

public function updateTag($id, StoreTagDTO $dto)
{
    $tag = $this->tagRepository->getTagById($id);
    return $this->tagRepository->updateTag($tag, $dto);
}

public function deleteTag($id)
{
    $tag = $this->tagRepository->getTagById($id);

    if (!$tag) {
        return false; // Indicate that the tag was not found
    }

    return $this->tagRepository->deleteTag($id);
}

}
