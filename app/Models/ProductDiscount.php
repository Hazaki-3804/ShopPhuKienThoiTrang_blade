<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductDiscount
 * 
 * @property int $id
 * @property int $product_id
 * @property int $discount_id
 * 
 * @property Discount $discount
 * @property Product $product
 *
 * @package App\Models
 */
class ProductDiscount extends Model
{
	protected $table = 'product_discounts';
	public $timestamps = false;

	protected $casts = [
		'product_id' => 'int',
		'discount_id' => 'int'
	];

	protected $fillable = [
		'product_id',
		'discount_id'
	];

	public function discount()
	{
		return $this->belongsTo(Discount::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
