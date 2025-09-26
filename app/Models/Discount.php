<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Discount
 * 
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property string $discount_type
 * @property float $discount_value
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Product[] $products
 *
 * @package App\Models
 */
class Discount extends Model
{
	protected $table = 'discounts';

	protected $casts = [
		'discount_value' => 'float',
		'start_date' => 'datetime',
		'end_date' => 'datetime',
		'status' => 'int'
	];

	protected $fillable = [
		'code',
		'description',
		'discount_type',
		'discount_value',
		'start_date',
		'end_date',
		'status'
	];

	public function products()
	{
		return $this->belongsToMany(Product::class, 'product_discounts')
					->withPivot('id');
	}
}
