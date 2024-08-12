<?php namespace Ocme\Model;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 * 
 * @property int $product_id
 * @property string $model
 * @property string $sku
 * @property string $upc
 * @property string $ean
 * @property string $jan
 * @property string $isbn
 * @property string $mpn
 * @property string $location
 * @property int $quantity
 * @property int $stock_status_id
 * @property string $image
 * @property int $manufacturer_id
 * @property int $shipping
 * @property float $price
 * @property int $points
 * @property int $tax_class_id
 * @property string $date_available
 * @property float $weight
 * @property int $weight_class_id
 * @property float $length
 * @property float $width
 * @property float $height
 * @property int $length_class_id
 * @property int $subtract
 * @property int $minimum
 * @property int $sort_order
 * @property int $status
 * @property int $vewed
 * @property string $date_added
 * @property string $date_modified
 * 
 * @property ProductAttribute[] $product_attributes
 * @property ProductAttributeValue[] $product_attribute_values
 */

class Product extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'product_id';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = array(
		'sort_order' => 0,
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 'quantity', 'stock_status_id', 'image', 'manufacturer_id', 'shipping', 'price', 'points', 'tax_class_id',
		'date_available', 'weight', 'weight_class_id', 'length', 'width', 'height', 'length_class_id', 'subtract', 'minimum', 'sort_order', 'status', 'viewed', 'date_added', 'date_modified',
	);
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	/**
	 * @param Illuminate\Database\Eloquent\Builder $query
	 */
	public function scopeMissing( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->whereNull('ocme_filter_indexed_at')
			->orWhereColumn('ocme_filter_indexed_at', '<', 'date_modified');
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function product_attributes() {
		return $this->hasMany('\Ocme\Model\ProductAttribute', 'product_id', 'product_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function product_attribute_values() {
		return $this->hasMany('\Ocme\Model\ProductAttributeValue', 'product_id', 'product_id');
	}
	
}