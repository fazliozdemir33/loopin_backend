<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'age', 'bio', 'gender', 'zodiac_sign', 'avatar_url', 'interests', 'photos', 'latitude', 'longitude', 'wallet_balance'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'interests' => 'array',
            'photos' => 'array',
            'is_banned' => 'boolean',
        ];
    }

    public function getNameAttribute($value)
    {
        return $this->is_banned ? 'Loopn Kullanıcısı' : $value;
    }

    public function getAvatarUrlAttribute($value)
    {
        return $this->is_banned ? null : $value;
    }
}
