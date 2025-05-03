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
        'stock' => 'integer'
    ];

    protected $fillable = [
        'name',
        'description',
        'expires_at',
        'brand',
        'category',
        'price',
        'stock',
        'dosage',
        'image',
        'created_by'
    ];

    public function toSearchableArray(){
        return [
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'category' => $this->category,
            'dosage' => $this->dosage,
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
