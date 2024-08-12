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
 * @property int $option_value_id
 * @property int $option_id
 * @property string $image
 * @property int $sort_order
 * @property string $color
 * 
 * @property Option $option
 * @property OcmeFilterProperty $ocme_filter_property
 * @property OcmeFilterPropertyValue $ocme_filter_property_value
 * @property OcmeFilterPropertyValueToProduct[] $ocme_filter_property_value_to_products
 */

class OptionValue extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;
	use \Ocme\Database\MissingOcmeFilterPropertyValue;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'option_value';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'option_value_id';

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
		'option_id', 'image', 'sort_order', 'color',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $option_value OptionValue */
		self::created(function( $option_value ){
			/* @var $option_value OptionValue */
			
			OcmeFilterPropertyValue::create(array(
				'ocme_filter_property_id' => OcmeFilterProperty::createIfNotExists( $option_value->option )->id,
				'option_id' => $option_value->option_id,
				'option_value_id' => $option_value->option_value_id,
			));
		});
		
		/* @var $option_value OptionValue */
		self::deleted(function( $option_value ){
			/* @var $option_value OptionValue */
			
			if( $option_value->ocme_filter_property_value ) {
				$option_value->ocme_filter_property_value->delete();
			}
			
			$option_value->ocme_filter_property_value_to_products()->delete();
		});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function option() {
		return $this->belongsTo('\Ocme\Model\Option', 'option_id', 'option_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property() {
		return $this->hasOne('\Ocme\Model\OcmeFilterProperty', 'option_id', 'option_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property_value() {
		return $this->hasOne('\Ocme\Model\OcmeFilterPropertyValue', 'option_value_id', 'option_value_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function ocme_filter_property_value_to_products() {
		return $this->hasMany('\Ocme\Model\OcmeFilterPropertyValueToProduct', 'option_value_id', 'option_value_id');
	}
	
}