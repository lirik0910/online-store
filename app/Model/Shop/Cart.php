<?php

namespace App\Model\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Cart extends Model
{
	/**
	 * Bind product model
	 * @return boolean
	 */
	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	/**
	 * Get order model
	 * @return boolean
	 */
	public function order()
	{
		return $this->hasOne(Order::class);
	}

	/**
	 * Calculate products cost of current cart
	 * @return float
	 */
	public function calculateCartCost()
	{

	}
}
