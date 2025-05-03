<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'is_role' => $this->is_role,
            'status' => $this->status,
            'pharmacy_name' => $this->pharmacy_name,
            'tin_number' => $this->tin_number,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'license_image' => $this->license_image,
            'tin_image' => $this->tin_image,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
