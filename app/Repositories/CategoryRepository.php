<?php

namespace App\Repositories;

use App\DTOs\GetCategoryDTO;
use App\DTOs\StoreCategoryDTO;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function getAllCategories(GetCategoryDTO $dto):LengthAwarePaginator  
    {
        $query = Category::query();
        return $query->paginate($dto->perPage);
    }

    public function getCategoryById($id):?Category
    {
        return Category::find($id);
    }

    public function createCategory(StoreCategoryDTO $dto):Category
    {
        $category = Category::create($dto->toArray());
        return $category->refresh();
    }

    public function updateCategory(Category $category , StoreCategoryDTO $dto)
    {
            $category->update($dto->toArray());
            return $category;
    }

    public function deleteCategory($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();
            return true;
        }
        return false;
    }
}
