<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
            ] : null,
            'items' => collect(is_string($this->items) ? json_decode($this->items, true) : $this->items)->map(function ($item) {
                return [
                    'drug_id' => $item['drug_id'] ?? null,
                    'name' => $item['name'] ?? null,
                    'price' => isset($item['price']) ? (float) $item['price'] : 0,
                    'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 0,
                    'subtotal' => isset($item['subtotal']) ? (float) $item['subtotal'] : 0,
                ];
            })->values()->all(),
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'prescription_uid' => $this->prescription_uid,
            'prescription_image' => $this->prescription_image,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
