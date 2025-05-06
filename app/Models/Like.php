<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'user_id'
    ];

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
