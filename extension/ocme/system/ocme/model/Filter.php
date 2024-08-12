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
 * @property int $filter_id
 * @property int $filter_group_id
 * @property int $sort_order
 * 
 * @property FilterGroup $filter_group
 * @property OcmeFilterProperty $ocme_filter_property
 * @property OcmeFilterPropertyValue $ocme_filter_property_value
 * @property OcmeFilterPropertyValueToProduct[] $ocme_filter_property_value_to_products
 */

class Filter extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;
	use \Ocme\Database\MissingOcmeFilterPropertyValue;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filter';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'filter_id';

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
		'filter_group_id', 'sort_order',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $filter Filter */
		self::created(function( $filter ){
			/* @var $filter Filter */
			
			OcmeFilterPropertyValue::create(array(
				'ocme_filter_property_id' => OcmeFilterProperty::createIfNotExists( $filter->filter_group )->id,
				'filter_group_id' => $filter->filter_group_id,
				'filter_id' => $filter->filter_id,
			));
		});
		
		/* @var $filter Filter */
		self::deleted(function( $filter ){
			/* @var $filter Filter */
			
			if( $filter->ocme_filter_property_value ) {
				$filter->ocme_filter_property_value->delete();
			}
			
			$filter->descriptions()->delete();
			
			$filter->ocme_filter_property_value_to_products()->delete();
		});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function filter_group() {
		return $this->belongsTo('\Ocme\Model\FilterGroup', 'filter_group_id', 'filter_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property() {
		return $this->hasOne('\Ocme\Model\OcmeFilterProperty', 'filter_group_id', 'filter_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property_value() {
		return $this->hasOne('\Ocme\Model\OcmeFilterPropertyValue', 'filter_id', 'filter_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function ocme_filter_property_value_to_products() {
		return $this->hasMany('\Ocme\Model\OcmeFilterPropertyValueToProduct', 'filter_id', 'filter_id');
	}
	
}