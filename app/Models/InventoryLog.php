<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    //
    protected $fillable = [
        'drug_id', 'user_id', 'change_type', 'quantity_changed', 'reason'
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
