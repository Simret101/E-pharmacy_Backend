<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\UsernameGenerator;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'is_role',
        'status',
        'profile_image',
        'cloudinary_public_id',
        'reset_token',
        'reset_token_expires_at',
        'username',
        'bank_name',
        'account_number',
        'license_image',
        'tin_image',
        'license_public_id',
        'tin_public_id',
        'pharmacy_name',
        'tin_number',
        'lat',
        'lng',
        'email_verified_at',
        'google_id',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'license_public_id',
        'tin_public_id'
    ];

    /**
     * The attributes that should be visible for serialization.
     *
     * @var array<int, string>
     */
    protected $visible = [
        'id',
        'name',
        'email',
        'profile_image',
        'cloudinary_public_id',
        'is_role',
        'status',
        'phone',
        'address',
        'pharmacy_name',
        'username',
        'bank_name',
        'account_number',
        'license_image',
        'tin_image',
        'lat',
        'lng',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_role' => 'integer',
            'status' => 'string',
            'lat' => 'float',
            'lng' => 'float'
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Generate username if not provided
            if (empty($user->username)) {
                $user->username = app(UsernameGenerator::class)->generateUsername($user->name);
            }
        });


    }



    public function pharmacist()
    {
        return $this->hasOne(Pharmacist::class);
    }

    public function place()
    {
        return $this->hasOne(Place::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }
}
