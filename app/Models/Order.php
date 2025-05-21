<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
class Order extends Model
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'user_id',
        'drug_id',
        'prescription_uid',
        'prescription_image',
        'refill_allowed',
        'refill_used',
        'prescription_status',
        'quantity',
        'total_amount',
        'status',
        'notes'
    ];
    protected $attributes = [
        'refill_allowed' => false, // Default to false if not specified
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'refill_allowed' => 'integer',
        'refill_used' => 'integer',
        'prescription_status' => 'string',
        'status' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
    public function scopePendingPrescription($query)
    {
        return $query->where('prescription_status', 'pending');
    }

    public function canRefill()
    {
        return $this->refill_allowed > 0;
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

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_uid', 'prescription_uid');
    }
}
