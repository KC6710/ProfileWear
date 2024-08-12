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
 * @property int $attribute_value_id
 * @property int $attribute_id
 * @property string $image
 * @property int $sort_order
 * @property string $color
 * @property int $vinteger
 * @property int $vfloat
 * 
 * @property Attribute $attribute
 * @property OcmeFilterProperty $ocme_filter_property
 * @property OcmeFilterPropertyValue $ocme_filter_property_value
 * @property OcmeFilterPropertyValueToProduct[] $ocme_filter_property_value_to_products
 */

class AttributeValue extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;
	use \Ocme\Database\MissingOcmeFilterPropertyValue;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute_value';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'attribute_value_id';

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
		'attribute_id', 'image', 'sort_order', 'color', 'vinteger', 'vfloat',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array(
		'vinteger', 'vfloat',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $attribute_value AttributeValue */
		self::created(function( $attribute_value ){
			/* @var $attribute_value AttributeValue */
			
			if( ! $attribute_value->attribute ) {
				throw new \Exception( 'For the record AttributeValue::' . $attribute_value->attribute_value_id . ' doesn\'t exists related Attribute::' . $attribute_value->attribute_id );
			}			
			
			OcmeFilterPropertyValue::create(array(
				'ocme_filter_property_id' => OcmeFilterProperty::createIfNotExists( $attribute_value->attribute )->id,
				'attribute_id' => $attribute_value->attribute_id,
				'attribute_value_id' => $attribute_value->attribute_value_id,
			));
		});
		
		/* @var $attribute_value AttributeValue */
		self::deleted(function( $attribute_value ){
			/* @var $attribute_value AttributeValue */
			
			if( $attribute_value->ocme_filter_property_value ) {
				$attribute_value->ocme_filter_property_value->delete();
			}
			
			$attribute_value->descriptions()->delete();
			
			$attribute_value->ocme_filter_property_value_to_products()->delete();
		});
	}
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeRedundant( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->where(function($q){
				$q
					->whereNotExists(function($q) {
						$q->select(ocme()->db()->raw(1))
							->from('attribute')
							->whereColumn('attribute.attribute_id', 'attribute_value.attribute_id');
					});
			});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute() {
		return $this->belongsTo('\Ocme\Model\Attribute', 'attribute_id', 'attribute_id');
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
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function ocme_filter_property_value_to_products() {
		return $this->hasMany('\Ocme\Model\OcmeFilterPropertyValueToProduct', 'attribute_value_id', 'attribute_value_id');
	}
	
}