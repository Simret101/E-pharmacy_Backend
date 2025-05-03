<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescriptions';
    protected $fillable = [
        'prescription_uid',
        'user_id',
        'attachment_path',
        'refill_allowed',
        'refill_used',
        'status',
    ];

    
    protected $appends = ['remaining_refills'];

    public function getRemainingRefillsAttribute()
    {
        return $this->refill_allowed - $this->refill_used;
    }
    public function user()
{
    return $this->belongsTo(User::class);
}

}
