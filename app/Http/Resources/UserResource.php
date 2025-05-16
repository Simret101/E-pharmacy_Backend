<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'profile_image' => $this->profile_image,
            'cloudinary_public_id' => $this->cloudinary_public_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}