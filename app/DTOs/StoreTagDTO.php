<?php

namespace App\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class StoreTagDTO extends SimpleDTO
{
    public string $name;

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [];
    }
}
