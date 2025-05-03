<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory, Searchable, SoftDeletes; 

    protected $table = 'users';

    protected $fillable = [
        'user_id', 
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'address',
        'lat',  
        'lng', 
        'is_role'
    ];

    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (!$patient->id) {
                $patient->id = Str::uuid(); 
            }
        });
    }

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function toSearchableArray()
    {
        return $this->only(['id', 'first_name', 'last_name', 'phone_number', 'email', 'address']);
    }
}
