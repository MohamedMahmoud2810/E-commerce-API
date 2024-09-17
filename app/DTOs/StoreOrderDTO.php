<?php

namespace App\DTOs;

use WendellAdriel\ValidatedDTO\Attributes\Map;
use WendellAdriel\ValidatedDTO\SimpleDTO;

/**
 * @OA\Schema(
 *     schema="StoreOrderDTO",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(
 *         @OA\Property(property="product_id", type="integer"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="price", type="number", format="float")
 *     ))
 * )
 */

class StoreOrderDTO extends SimpleDTO
{
    public $user;
    public array $items;
    
    public function __construct($user, array $data)
    {
        parent::__construct($data);

        $this->user = $user;
        $this->items = $data['items'];
    }
    // You can set default values here if needed
    protected function defaults(): array
    {
        return [];
    }

    // Cast attributes if necessary
    protected function casts(): array
    {
        return [

        ];
    }

    // Validation rules for the data
    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}
