<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'stock' => $product->stock,
                    'category' => new CategoryResource($product->category),
                    'tag' => new TagResource($product->tag),
                    'images' => ImageResource::collection($product->images),
                    'vendor' => new UserResource($product->vendor),
                    'created_at' => $product->created_at->toDateTimeString(),
                    'updated_at' => $product->updated_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
