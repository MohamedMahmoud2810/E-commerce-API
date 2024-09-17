<?php

namespace App\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class ProductDTO extends SimpleDTO
{
    public string $name;
    public float $price;
    public int $stock;
    public string $description;
    public int $category_id;
    public int $tag_id;
    public int $vendor_id;

    public function __construct(array $data)
    {
        // Cast or validate data before assigning
        $this->name = (string) $data['name'];
        $this->price = (float) $data['price'];
        $this->stock = (int) $data['stock'];
        $this->description = (string) $data['description'];
        $this->category_id = (int) $data['category_id'];
        $this->tag_id = (int) $data['tag_id'];
        $this->vendor_id = (int) $data['vendor_id'];
    }

    // Corrected casts method
    public function casts(): array
    {
        return [
            
        ];
    }

    
    public function defaults(): array
    {
        return [
            
        ];
    }
}
