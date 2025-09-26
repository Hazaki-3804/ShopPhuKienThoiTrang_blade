<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * 
 * @property int $id
 * @property int $order_id
 * @property float $amount
 * @property string $payment_method
 * @property string $status
 * @property string|null $transaction_code
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Order $order
 *
 * @package App\Models
 */
class Payment extends Model
{
	protected $table = 'payments';

	protected $casts = [
		'order_id' => 'int',
		'amount' => 'float',
		'paid_at' => 'datetime'
	];

	protected $fillable = [
		'order_id',
		'amount',
		'payment_method',
		'status',
		'transaction_code',
		'paid_at'
	];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}
}
