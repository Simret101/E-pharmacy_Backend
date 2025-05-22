<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    public $timestamps = false;
    protected $fillable = ['email', 'token'];

    public function delete()
    {
        return $this->where('email', $this->email)
                   ->where('token', $this->token)
                   ->delete();
    }
}