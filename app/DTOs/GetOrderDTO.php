<?php

namespace App\DTOs;

use Illuminate\Support\Collection;
use WendellAdriel\ValidatedDTO\SimpleDTO;
use WendellAdriel\ValidatedDTO\Attributes\Map;


class GetOrderDTO extends SimpleDTO
{
    #[Map(data: 'per_page')]
    public ?int $perPage;
    
    public ?int $page;

    // Default values for the attributes
    protected function defaults(): array
    {
        return [
            'page' => 1,
            'per_page' => 15, // Default perPage is 15
        ];
    }

    // Attribute casting, if necessary
    protected function casts(): array
    {
        return [
            
        ];
    }
}