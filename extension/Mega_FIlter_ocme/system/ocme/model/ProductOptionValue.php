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
 * @property int $product_option_value_id
 * @property int $product_option_id
 * @property int $product_id
 * @property int $option_id
 * @property int $option_value_id
 * @property int $quantity
 * @property bool $subtract
 * @property float $price
 * @property string $price_prefix
 * @property int $points
 * @property string $points_prefix
 * @property float $weight
 * @property string $weight_prefix
 * 
 * @property ProductOption $product_option
 * @property Product $product
 * @property Option $option
 * @property OptionValue $option_value
 * @property OcmeFilterProperty $ocme_filter_property
 * @property OcmeFilterPropertyValue $ocme_filter_property_value
 * @property OcmeFilterPropertyValueToProduct $ocme_filter_property_value_to_product
 */

class ProductOptionValue extends \Ocme\Database\Model {
	
	use \Ocme\Database\MissingOcmeFilterPropertyValueToProduct;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_option_value';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'product_option_value_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'product_option_id', 'product_id', 'option_id', 'option_value_id', 'quantity', 'subtract', 'price', 'price_prefix', 'points', 'points_prefix', 'weight', 'weight_prefix',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $product_option_value ProductOptionValue */
		self::created(function( $product_option_value ){
			/* @var $product_option_value ProductOptionValue */
			
			OcmeFilterPropertyValueToProduct::create(array(
				'ocme_filter_property_id' => OcmeFilterProperty::createIfNotExists( $product_option_value->option )->id,
				'ocme_filter_property_value_id' => OcmeFilterPropertyValue::createIfNotExists( $product_option_value->option_value )->id,
				'product_id' => $product_option_value->product_id,
				'option_id' => $product_option_value->option_id,
				'option_value_id' => $product_option_value->option_value_id,
			));
		});
		
		/* @var $product_option_value ProductOptionValue */
		self::deleted(function( $product_option_value ){
			/* @var $product_option_value ProductOptionValue */
			
			if( $product_option_value->ocme_filter_property_value_to_product ) {
				$product_option_value->ocme_filter_property_value_to_product->delete();
			}
		});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product_option() {
		return $this->belongsTo('\Ocme\Model\ProductOption', 'product_option_id', 'product_option_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product() {
		return $this->belongsTo('\Ocme\Model\Product', 'product_id', 'product_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function option() {
		return $this->belongsTo('\Ocme\Model\Option', 'option_id', 'option_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function option_value() {
		return $this->belongsTo('\Ocme\Model\OptionValue', 'option_value_id', 'option_value_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_property() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterProperty', 'option_id', 'option_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_property_value() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterPropertyValue', 'option_value_id', 'option_value_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property_value_to_product() {
		return $this->hasOne('\Ocme\Model\OcmeFilterPropertyValueToProduct', 'product_id', 'product_id')
			->where('ocme_filter_property_value_to_product.option_id', $this->option_id)
			->where('ocme_filter_property_value_to_product.option_value_id', $this->option_value_id);
	}
	
}