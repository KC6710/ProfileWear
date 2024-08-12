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
 * @property int $filter_group_id
 * @property int $sort_order
 * 
 * @property Filter[]|\Illuminate\Database\Eloquent\Collection $filters
 * @property OcmeFilterProperty $ocme_filter_property
 */

class FilterGroup extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;
	use \Ocme\Database\MissingOcmeFilterProperty;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filter_group';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'filter_group_id';

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
		'sort_order',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $filter_group FilterGroup */
		self::created(function( $filter_group ){
			/* @var $filter_group FilterGroup */
			
			self::eventCreated( $filter_group );
		});
		
		/* @var $filter_group FilterGroup */
		self::deleted(function( $filter_group ){
			/* @var $filter_group FilterGroup */
			
			self::eventDeleted( $filter_group );
		});
	}
	
	/**
	 * @param FilterGroup $filter_group
	 */
	public static function eventCreated( FilterGroup $filter_group ) {			
		OcmeFilterProperty::create(array(
			'filter_group_id' => $filter_group->id,
		));
	}
	
	/**
	 * @param FilterGroup $filter_group
	 */
	public static function eventDeleted( FilterGroup $filter_group ) {			
		if( $filter_group->ocme_filter_property ) {
			$filter_group->ocme_filter_property->delete();
		}
		
		$filter_group->descriptions()->delete();
		
		/* @var $filter Filter */
		foreach( $filter_group->filters()->get() as $filter ) {
			$filter->delete();
		}
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		foreach( OcmeFilterCondition::where('condition_type', OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP)
			->where('record_id', $filter_group->filter_group_id)
			->get() as $ocme_filter_condition 
		) {
			$ocme_filter_condition->delete();
		}
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function filters() {
		return $this->hasMany('\Ocme\Model\Filter', 'filter_group_id', 'filter_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property() {
		return $this->hasOne('\Ocme\Model\OcmeFilterProperty', 'filter_group_id', 'filter_group_id');
	}
	
}