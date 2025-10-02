<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * @property int $id
 * @property string $username
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $phone
 * @property string|null $address
 * @property Carbon|null $email_verified_at
 * @property int $role_id
 * @property int $status
 * @property string|null $avatar
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Role $role
 * @property Collection|Cart[] $carts
 * @property Collection|Order[] $orders
 * @property Collection|Review[] $reviews
 *
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail
{
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use HasFactory, Notifiable;
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
		'role_id' => 'int',
		'status' => 'int',
		'social_id' => 'int',
		'ward_id' => 'int'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'username',
		'name',
		'email',
		'password',
		'phone',
		'address',
		'email_verified_at',
		'ward_id',
		'role_id',
		'status',
		'avatar',
		'social_id',
		'remember_token'
	];

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function carts()
	{
		return $this->hasMany(Cart::class);
	}

	public function orders()
	{
		return $this->hasMany(Order::class);
	}

	public function reviews()
	{
		return $this->hasMany(Review::class);
	}
	public function ward()
	{
		return $this->belongsTo(Ward::class);
	}

	// Accessor: always return usable URL for avatar
	public function getAvatarUrlAttribute(): string
	{
		$avatar = $this->avatar;
		if (!$avatar) {
			return asset('img/default-avatar.png');
		}
		if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
			return $avatar;
		}
		// Assume stored as 'storage/...'
		return asset($avatar);
	}
}
