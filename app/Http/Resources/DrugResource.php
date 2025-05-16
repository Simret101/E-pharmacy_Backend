<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DrugResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'stock' => $this->stock,
            'dosage' => $this->dosage,
            'image' => $this->image,
            'prescription_needed' => $this->prescription_needed,
            'expires_at' => $this->expires_at,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'username' => $this->whenLoaded('creator', fn() => $this->creator->username)
        ];
    }
}
