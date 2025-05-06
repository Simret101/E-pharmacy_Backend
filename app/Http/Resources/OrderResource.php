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
            'user' => new UserResource($this->whenLoaded('user')),
            'drug' => new DrugResource($this->whenLoaded('drug')),
            'quantity' => $this->quantity,
            'prescription_uid' => $this->prescription_uid, // Add prescription_uid
            'prescription_image' => $this->prescription_image,
            'prescription_image_thumbnail' => $this->prescription_image ? str_replace('/upload/', '/upload/c_thumb,w_200,h_200/', $this->prescription_image) : null,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
