<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Testing\Fluent\Concerns\Has;
use Laravel\Scout\Searchable;

class Drug extends Model
{
    use HasFactory, Searchable;
    protected $table = 'drugs';
  
    protected $casts = [
        'expires_at' => 'datetime',
        'price' => 'float',
        'stock' => 'integer',
        'prescription_needed' => 'boolean'
    ];

    protected $fillable = [
        'name',
        'brand',
        'description',
        'category',
        'price',
        'stock',
        'dosage',
        'image',
        'expires_at',
        'prescription_needed',
        'created_by'
    ];



    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function toSearchableArray(){
        return [
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'category' => $this->category,
            'dosage' => $this->dosage,
        ];
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function isLikedBy(User $user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function toggleLike(User $user)
    {
        if ($this->isLikedBy($user)) {
            $this->likes()->where('user_id', $user->id)->delete();
            return false;
        }

        $this->likes()->create(['user_id' => $user->id]);
        return true;
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'dosage' => 'required|string|max:255',
            'image' => 'required|string|max:255',
            'expires_at' => 'required|date',
            'created_by' => 'required|exists:users,id'
        ];
    }
}
