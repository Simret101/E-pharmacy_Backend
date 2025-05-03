<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailVerificationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'expired_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime'
    ];
}
