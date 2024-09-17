<?php

namespace App\Services;

use App\DTOs\GetProductDTO;
use App\DTOs\ProductDTO;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewProductAdded;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(GetProductDTO $dto)
    {
        $user = Auth::user();
        $products = $this->productRepository->getAllPaginated($dto , $user);
        
        if ($products) {
            return $products;
        }
        
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function storeProduct($request)
    {

        DB::beginTransaction();
        
        try {
            
            $productDTO = new ProductDTO($request->all());
            $product = $this->productRepository->storeProduct([
                'name' => $productDTO->name,
                'price' => $productDTO->price,
                'stock' => $productDTO->stock,
                'description' => $productDTO->description,
                'category_id' => $productDTO->category_id,
                'tag_id' => $productDTO->tag_id,
                'vendor_id' => $productDTO->vendor_id,
            ]);

        
            if ($request->hasFile('images')) {
                $this->storeProductImages($product, $request->file('images'));
            }

            DB::commit();


            $this->notifyUsers($product);

            return $product->load('images');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to create product'], 500);
        }
    }

    public function findProductById($id)
    {
        return $this->productRepository->findById($id);
    }

    public function updateProduct($id, $request)
    {
        $product = $this->productRepository->updateProduct($id, $request->only([
            'name', 'price', 'category_id', 'tag_id', 'stock', 'vendor_id', 'description'
        ]));

        if ($request->hasFile('images')) {
            $this->updateProductImages($product, $request->file('images'));
        }

        return $product->load('images');
    }

    public function deleteProduct($id)
    {
        return $this->productRepository->deleteProduct($id);
    }

    public function searchProducts($searchQuery)
    {
        return $this->productRepository->searchProducts($searchQuery);
    }

    public function getFilteredProducts($request)
    {
        return $this->productRepository->filter($request->only([
            'query', 'category_id', 'min_price', 'max_price', 'rating', 'sort_by', 'in_stock'
        ]));
    }

    private function storeProductImages($product, $images)
    {
        foreach ($images as $imageFile) {
            $path = $imageFile->store('product_images', 'public');
            $product->images()->create(['file_path' => $path]);
        }
    }

    private function updateProductImages($product, $images)
    {
        // Delete existing images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->file_path);
            $image->delete();
        }

        // Add new images
        foreach ($images as $imageFile) {
            $path = $imageFile->store('product_images', 'public');
            $product->images()->create(['file_path' => $path]);
        }
    }

    private function notifyUsers($product)
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->notify(new NewProductAdded($product));
        }
    }
}
