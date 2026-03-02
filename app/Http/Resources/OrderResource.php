<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'placed_at' => $this->placed_at,
            'created_at' => $this->created_at,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'items_count' => $this->whenCounted('items'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
