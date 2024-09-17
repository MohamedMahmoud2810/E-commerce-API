<?php

namespace App\Services;

use App\DTOs\GetCategoryDTO;
use App\DTOs\StoreCategoryDTO;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryService
{


    public function __construct(protected CategoryRepository $categoryRepository)
    {
    }
    

    public function getAllCategories(GetCategoryDTO $dto)
    {
        return $this->categoryRepository->getAllCategories($dto);
    }

    public function getCategoryById($id)   
    {
        $category = $this->categoryRepository->getCategoryById($id);
        if (!$category) {
            return response('Category not found', Response::HTTP_NOT_FOUND);
        }
        return $category;
    }

    public function createCategory(StoreCategoryDTO $dto)
    {
        return $this->categoryRepository->createCategory($dto);
    }

    public function updateCategory($id, StoreCategoryDTO $dto)
    {
        $category = $this->categoryRepository->getCategoryById($id);
        if (!$category) {
            return response('Category not found', Response::HTTP_NOT_FOUND);
        }
        return $this->categoryRepository->updateCategory($category, $dto);
    }

    public function deleteCategory($id)
    {
        $category = $this->categoryRepository->getCategoryById($id);
        if (!$category) {
            return response('Category not found', Response::HTTP_NOT_FOUND);
        }
        return $this->categoryRepository->deleteCategory($id);
    }
}
