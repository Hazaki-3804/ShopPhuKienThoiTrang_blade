<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int $stock
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Category $category
 * @property Collection|CartItem[] $cart_items
 * @property Collection|OrderItem[] $order_items
 * @property Collection|Discount[] $discounts
 * @property Collection|ProductImage[] $product_images
 * @property Collection|Review[] $reviews
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';

	protected $casts = [
		'category_id' => 'int',
		'price' => 'float',
		'stock' => 'int',
		'status' => 'int'
	];

	protected $fillable = [
		'category_id',
		'name',
		'description',
		'price',
		'stock',
		'status'
	];

	public function category()
	{
		return $this->belongsTo(Category::class);
	}

	public function cart_items()
	{
		return $this->hasMany(CartItem::class);
	}

	public function order_items()
	{
		return $this->hasMany(OrderItem::class);
	}

	public function discounts()
	{
		return $this->belongsToMany(Discount::class, 'product_discounts')
					->withPivot('id');
	}

	public function product_images()
	{
		return $this->hasMany(ProductImage::class);
	}

	public function reviews()
	{
		return $this->hasMany(Review::class);
	}
}
