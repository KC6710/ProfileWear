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
 * @property int $filter_id
 * 
 * @property Product $product
 * @property Filter $filter
 * @property OcmeFilterPropertyValue $ocme_filter_property_value
 * @property OcmeFilterPropertyValueToProduct[] $ocme_filter_property_value_to_products
 */

class ProductFilter extends \Ocme\Database\Model {
	
	use \Ocme\Database\MissingOcmeFilterPropertyValueToProduct;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_filter';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'product_id', 'filter_id',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $product_filter ProductFilter */
		self::created(function( $product_filter ){
			/* @var $product_filter ProductFilter */
			
			OcmeFilterPropertyValueToProduct::create(array(
				'ocme_filter_property_id' => OcmeFilterProperty::createIfNotExists( $product_filter->filter->filter_group )->id,
				'ocme_filter_property_value_id' => OcmeFilterPropertyValue::createIfNotExists( $product_filter->filter )->id,
				'product_id' => $product_filter->product_id,
				'filter_group_id' => $product_filter->filter->filter_group_id,
				'filter_id' => $product_filter->filter_id,
			));
		});
		
		/* @var $product_filter ProductFilter */
		self::deleted(function( $product_filter ){
			/* @var $product_filter ProductFilter */
			
			if( $product_filter->ocme_filter_property_value_to_product ) {
				$product_filter->ocme_filter_property_value_to_product->delete();
			}
		});
	}
	
	// Functions ///////////////////////////////////////////////////////////////

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        $query->where( 'product_id', $this->product_id )->where( 'filter_id', $this->filter_id );

        return $query;
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
	public function filter() {
		return $this->belongsTo('\Ocme\Model\Filter', 'filter_id', 'filter_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_property_value() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterPropertyValue', 'filter_id', 'filter_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property_value_to_product() {
		return $this->hasOne('\Ocme\Model\OcmeFilterPropertyValueToProduct', 'product_id', 'product_id')
			->where('ocme_filter_property_value_to_product.filter_id', $this->filter_id);
	}
	
}