<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'drug_id',
        'quantity',
        'prescription_image',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function getPrescriptionImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        // If the value is already a full URL, return it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Otherwise, construct the Cloudinary URL
        return "https://res.cloudinary.com/" . config('cloudinary.cloud_name') . "/image/upload/" . $value;
    }
}
