<?php

namespace App\Repositories;

use App\DTOs\GetProductDTO;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProductRepository
{
    public function getAllPaginated( GetProductDTO $dto ,$user): LengthAwarePaginator
    {
        if ($user->hasRole('Admin') || $user->hasRole('Customer')) {
            return Product::with(['category', 'tag', 'images', 'vendor'])->paginate($dto->perPage);
        } elseif ($user->hasRole('Vendor')) {
            return Product::where('vendor_id', $user->id)->paginate(10);
        }
        return null;
    }

    public function storeProduct(array $data)
    {
        return Product::create($data);
    }

    public function findById($id)
    {
        return Product::with('images')->findOrFail($id);
    }

    public function updateProduct($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product;
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response(['message' => 'Product deleted successfully'], 200);
    }

    public function searchProducts($searchQuery)
    {

        $product = Product::with(['category', 'tag', 'vendor'])
            ->where('name', 'like', '%' . $searchQuery . '%')
            ->orWhere('description', 'like', '%' . $searchQuery . '%')
            ->get();

        if($product->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }
        return response()->json($product);
    }

    public function filter(array $filters)
    {
        $query = Product::with(['category', 'tag', 'vendor']);

        if (isset($filters['query'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['query'] . '%')
                ->orWhere('description', 'like', '%' . $filters['query'] . '%');
            });
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['min_price']) && isset($filters['max_price'])) {
            $query->whereBetween('price', [$filters['min_price'], $filters['max_price']]);
        } elseif (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        } elseif (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['rating'])) {
            $query->where('rating', '>=', $filters['rating']);
        }

        if (isset($filters['sort_by'])) {
            if ($filters['sort_by'] == 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($filters['sort_by'] == 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($filters['sort_by'] == 'rating') {
                $query->orderBy('rating', 'desc');
            }
        }

        if (isset($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }
        Log::info('Filter Query: ', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);


        return $query->paginate(10);
    }

}
