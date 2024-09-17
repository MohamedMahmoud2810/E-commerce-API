<?php

namespace App\Http\Controllers;

use App\DTOs\GetCategoryDTO;
use App\DTOs\StoreCategoryDTO;
use App\Http\Requests\GetCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Electronics"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-10T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-10T10:00:00Z")
 * )
 */
class CategoryController extends Controller
{

    public function __construct(protected CategoryService $categoryService)
    {
    }
/**
 * @OA\Get(
 *     path="/api/categories",
 *     operationId="getCategories",
 *     tags={"Category"},
 *     summary="Get list of categories",
 *     description="Returns a list of categories",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Category")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *     )
 * )
 */

    public function index(GetCategoryRequest $request)
    {
            $dto = new GetCategoryDTO($request->all());
            $categories = $this->categoryService->getAllCategories($dto);
            return new CategoryCollection($categories);
            
    }

    /**
 * @OA\Post(
 *     path="/api/categories",
 *     operationId="createCategory",
 *     tags={"Category"},
 *     summary="Create new category",
 *     description="Creates a new category",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Electronics")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Category created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The name field is required.")
 *         )
 *     )
 * )
 */
    public function store(StoreCategoryRequest $request)
    {
        $dto = new StoreCategoryDTO($request->all());
        $category = $this->categoryService->createCategory($dto);
        return new CategoryResource($category);

    }


    /**
 * @OA\Get(
 *     path="/api/categories/{id}",
 *     operationId="getCategoryById",
 *     tags={"Category"},
 *     summary="Get category by ID",
 *     description="Returns a specific category by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Category ID"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category not found.")
 *         )
 *     )
 * )
 */
    public function show($id)
    {
            $category = $this->categoryService->getCategoryById($id);
            return response()->json($category, Response::HTTP_OK);
    }
    /**
 * @OA\Put(
 *     path="/api/categories/{id}",
 *     operationId="updateCategory",
 *     tags={"Category"},
 *     summary="Update category",
 *     description="Updates an existing category by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Category ID"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Updated Category Name")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category not found.")
 *         )
 *     )
 * )
 */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
            $dto = new StoreCategoryDTO($request->all());
            $category = $this->categoryService->updateCategory($id, $dto);
            return response()->json($category, Response::HTTP_OK);
    }

    /**
 * @OA\Delete(
 *     path="/api/categories/{id}",
 *     operationId="deleteCategory",
 *     tags={"Category"},
 *     summary="Delete category",
 *     description="Deletes an existing category by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Category ID"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category deleted")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Category not found.")
 *         )
 *     )
 * )
    */
    public function destroy($id)
    {
        $deleted = $this->categoryService->deleteCategory($id);
        if (!$deleted ) {
            return response()->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['message' => 'Category deleted'], Response::HTTP_OK);
    }
}
