<?php

namespace App\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;
use WendellAdriel\ValidatedDTO\Attributes\Map;

class GetProductDTO extends SimpleDTO
{
    #[Map(data: 'per_page')]
    public ?int $perPage;
    public ?int $page;
    

    protected function defaults(): array
    {
        return [
            'page' => 1,
            'per_page' => 15,
        ];
    }

    protected function casts(): array
    {
        return [];
    }
}
