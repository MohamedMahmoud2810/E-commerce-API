<?php

namespace App\Http\Controllers;

use App\DTOs\GetProductDTO;
use App\Http\Requests\GetCategoryRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product model",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Sample Product"),
 *         @OA\Property(property="price", type="number", format="float", example=99.99),
 *         @OA\Property(property="description", type="string", example="Product description here"),
 *         @OA\Property(property="stock", type="integer", example=50),
 *         @OA\Property(property="category_id", type="integer", example=1),
 *         @OA\Property(property="tag_id", type="integer", example=2),
 *         @OA\Property(property="vendor_id", type="integer", example=3),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-01T12:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-01T12:00:00Z"),
 *     }
 * )
 */

class ProductController extends Controller
{
    protected $productService;


    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->middleware('auth:api');
        $this->middleware('role_or:Admin,Vendor')->only(['index', 'store', 'update', 'destroy']);
    }
    /**
     * @OA\Get(
     *      path="/api/products",
     *      operationId="getProductsList",
     *      tags={"Products"},
     *      summary="Get list of products",
     *      description="Returns list of products",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *       ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      security={{"bearerAuth": {}}}
     * )
     */


    public function index(GetCategoryRequest $request)
    {
        $dto = new GetProductDTO($request->all());
        $products = $this->productService->getAllProducts($dto);
        return  new ProductCollection($products);
    }

     /**
     * @OA\Post(
     *      path="/api/products",
     *      operationId="storeProduct",
     *      tags={"Products"},
     *      summary="Store new product",
     *      description="Creates a new product",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","price","stock","description","category_id","tag_id","vendor_id"},
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="price", type="number"),
     *              @OA\Property(property="stock", type="integer"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="category_id", type="integer"),
     *              @OA\Property(property="tag_id", type="integer"),
     *              @OA\Property(property="vendor_id", type="integer"),
     *              @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary"))
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Product created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *       ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=422, description="Validation Error"),
     *      security={{"bearerAuth": {}}}
     * )
     */

    public function store(StoreProductRequest $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Vendor')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $product = $this->productService->storeProduct($request);
        if (!$product) {
            return response()->json(['message' => 'Failed to create product'], 500);
        }
        return new ProductResource($product);
    }

     /**
     * @OA\Get(
     *      path="/api/products/{id}",
     *      operationId="showProduct",
     *      tags={"Products"},
     *      summary="Get product details",
     *      description="Returns product details by ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="Product ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product details retrieved successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *       ),
     *      @OA\Response(response=404, description="Product not found"),
     *      security={{"bearerAuth": {}}}
     * )
     */


    public function show($id)
    {
        $product = $this->productService->findProductById($id);
        return new ProductResource($product);
    }

    /**
 * @OA\Put(
 *     path="/api/products/{id}",
 *     summary="Update an existing product",
 *     description="Allows authorized users to update an existing product",
 *     tags={"Products"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Product ID",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Product Name"),
 *             @OA\Property(property="price", type="number", format="float", example="99.99"),
 *             @OA\Property(property="stock", type="integer", example="20"),
 *             @OA\Property(property="description", type="string", example="Updated Product Description"),
 *             @OA\Property(property="category_id", type="integer", example="1"),
 *             @OA\Property(property="tag_id", type="integer", example="2"),
 *             @OA\Property(property="vendor_id", type="integer", example="3"),
 *             @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary")),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update product"
 *     )
 * )
 */


    public function update(UpdateProductRequest $request, $id)
    {
        $product = $this->productService->updateProduct($id, $request);
        return new ProductResource($product);
    }
    
    
    /**
 * @OA\Delete(
 *     path="/api/products/{id}",
 *     summary="Delete a product",
 *     description="Allows authorized users to delete a product",
 *     tags={"Products"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Product ID",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product and its images deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */



    public function destroy($id)
    {
        return $this->productService->deleteProduct($id);
    }

    /**
 * @OA\Get(
 *     path="/api/products/search",
 *     summary="Search for products",
 *     description="Search for products based on name, description, category, tag, vendor, or price",
 *     tags={"Products"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="query",
 *         in="query",
 *         description="Search query",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Search results",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No products found"
 *     )
 * )
 */


    public function search(Request $request)
    {
        $searchQuery = $request->get('query');
        return $this->productService->searchProducts($searchQuery);
    }

    /**
 * @OA\Get(
 *     path="/api/products/filter",
 *     summary="Filter products",
 *     description="Filter products by name, category, price range, rating, and stock status",
 *     tags={"Products"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="query",
 *         in="query",
 *         description="Search query",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="category_id",
 *         in="query",
 *         description="Category ID to filter",
 *         required=false,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="min_price",
 *         in="query",
 *         description="Minimum price",
 *         required=false,
 *         @OA\Schema(
 *             type="number",
 *             format="float"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="max_price",
 *         in="query",
 *         description="Maximum price",
 *         required=false,
 *         @OA\Schema(
 *             type="number",
 *             format="float"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="rating",
 *         in="query",
 *         description="Minimum rating",
 *         required=false,
 *         @OA\Schema(
 *             type="number",
 *             format="float"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="in_stock",
 *         in="query",
 *         description="Whether the product is in stock (true or false)",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="sort_by",
 *         in="query",
 *         description="Sort by price (asc/desc) or rating",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"price_asc", "price_desc", "rating"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Filtered products",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No products found"
 *     )
 * )
 */

    public function filter(Request $request)
    {
        return $this->productService->getFilteredProducts($request);
    }

}
