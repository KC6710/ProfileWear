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
 * @property int $product_attribute_value_id
 * @property int $product_id
 * @property int $attribute_id
 * @property int $attribute_value_id
 * @property int $sort_order
 * 
 * @property Product $product
 * @property Attribute $attribute
 * @property AttributeValue $attribute_value
 * @property OcmeFilterProperty $ocme_filter_property
 * @property OcmeFilterPropertyValue $ocme_filter_property_value
 * @property OcmeFilterPropertyValueToProduct $ocme_filter_property_value_to_product
 */

class ProductAttributeValue extends \Ocme\Database\Model {
	
	use \Ocme\Database\MissingOcmeFilterPropertyValueToProduct;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_attribute_value';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'product_attribute_value_id';

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
		'product_id', 'attribute_id', 'attribute_value_id', 'sort_order',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $product_attribute_value ProductAttributeValue */
		self::created(function( $product_attribute_value ){
			/* @var $product_attribute_value ProductAttributeValue */
			
			OcmeFilterPropertyValueToProduct::firstOrNew(array(
				'ocme_filter_property_id' => OcmeFilterProperty::createIfNotExists( $product_attribute_value->attribute )->id,
				'ocme_filter_property_value_id' => OcmeFilterPropertyValue::createIfNotExists( $product_attribute_value->attribute_value )->id,
				'product_id' => $product_attribute_value->product_id,
			))->fill(array(
				'attribute_id' => $product_attribute_value->attribute_id,
				'attribute_value_id' => $product_attribute_value->attribute_value_id,
			))->save();
		});
		
		/* @var $product_attribute_value ProductAttributeValue */
		self::deleted(function( $product_attribute_value ){
			/* @var $product_attribute_value ProductAttributeValue */
			
			if( $product_attribute_value->ocme_filter_property_value_to_product ) {				
				$product_attribute_value->ocme_filter_property_value_to_product->delete();
			}
		});
	}
	
	// Functions ///////////////////////////////////////////////////////////////
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeRedundant( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->where(function($q){
				$q
					->whereNotExists(function($q) {
						$q->select(ocme()->db()->raw(1))
							->from('product')
							->whereColumn('product.product_id', 'product_attribute_value.product_id');
					})
					->orWhereNotExists(function($q) {
						$q->select(ocme()->db()->raw(1))
							->from('attribute')
							->whereColumn('attribute.attribute_id', 'product_attribute_value.attribute_id');
					})
					->orWhereNotExists(function($q) {
						$q->select(ocme()->db()->raw(1))
							->from('attribute_value')
							->whereColumn('attribute_value.attribute_value_id', 'product_attribute_value.attribute_value_id');
					});
			});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product() {
		return $this->belongsTo('\Ocme\Model\Product', 'product_id', 'product_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute() {
		return $this->belongsTo('\Ocme\Model\Attribute', 'attribute_id', 'attribute_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute_value() {
		return $this->belongsTo('\Ocme\Model\AttributeValue', 'attribute_value_id', 'attribute_value_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property() {
		return $this->hasOne('\Ocme\Model\OcmeFilterProperty', 'attribute_id', 'attribute_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property_value() {
		return $this->hasOne('\Ocme\Model\OcmeFilterPropertyValue', 'attribute_value_id', 'attribute_value_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property_value_to_product() {
		return $this->hasOne('\Ocme\Model\OcmeFilterPropertyValueToProduct', 'product_id', 'product_id')
			->where('ocme_filter_property_value_to_product.attribute_id', $this->attribute_id)
			->where('ocme_filter_property_value_to_product.attribute_value_id', $this->attribute_value_id);
	}
	
}