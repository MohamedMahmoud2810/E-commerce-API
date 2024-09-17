<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'stock' => $this->stock,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'tag' => $this->tag ? [
                'id' => $this->tag->id,
                'name' => $this->tag->name,
            ] : null,
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'file_path' => $image->file_path,
                ];
            }),
            'vendor' => $this->vendor ? [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
            ] : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
