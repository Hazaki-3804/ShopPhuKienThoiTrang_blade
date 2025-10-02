<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * 
 * @property int $id
 * @property int $user_id
 * @property float $total_price
 * @property string $status
 * @property string $shipping_address
 * @property string $payment_method
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property Collection|OrderItem[] $order_items
 * @property Collection|Payment[] $payments
 *
 * @package App\Models
 */
class Order extends Model
{
	protected $table = 'orders';

	protected $casts = [
		'user_id' => 'int',
		'total_price' => 'float'
	];

	protected $fillable = [
		'user_id',
		'total_price',
		'status',
		'shipping_address',
		'payment_method'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function order_items()
	{
		return $this->hasMany(OrderItem::class);
	}

	public function payments()
	{
		return $this->hasMany(Payment::class);
	}

	// Convenience accessor so "$order->items" works in views
	public function getItemsAttribute()
	{
		return $this->order_items;
	}

	// Map status to human text
	public function getStatusTextAttribute(): string
	{
		$map = [
			'pending' => 'Chờ xử lý',
			'processing' => 'Đang xử lý',
			'shipped' => 'Đã giao cho vận chuyển',
			'delivered' => 'Đã giao',
			'cancelled' => 'Đã hủy',
		];
		return $map[$this->status] ?? ucfirst($this->status);
	}

	// Map status to bootstrap badge color
	public function getStatusClassAttribute(): string
	{
		$map = [
			'pending' => 'secondary',
			'processing' => 'warning',
			'shipped' => 'info',
			'delivered' => 'success',
			'cancelled' => 'danger',
		];
		return $map[$this->status] ?? 'secondary';
	}
}
