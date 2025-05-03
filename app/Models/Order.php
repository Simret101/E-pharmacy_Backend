<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'items',
        'total_amount',
        'status',
        'prescription_uid',
        'prescription_image',
    ];

    public function setItemsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['items'] = json_encode($value);
        } else {
            $this->attributes['items'] = $value;
        }
    }

    public function getItemsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_uid', 'prescription_uid');
    }
}
