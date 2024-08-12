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
 * @property int $attribute_group_id
 * @property int $sort_order
 * 
 * @property Filter[]|\Illuminate\Database\Eloquent\Collection $filters
 */

class AttributeGroup extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute_group';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'attribute_group_id';

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
		
		/* @var $attribute_group AttributeGroup */
		self::deleted(function( $attribute_group ){
			self::eventDeleted( $attribute_group );
		});
	}
	
	/**
	 * @param AttributeGroup $attribute_group
	 */
	public static function eventDeleted( AttributeGroup $attribute_group ) {
		$attribute_group->descriptions()->delete();
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		foreach( OcmeFilterCondition::where('condition_type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP)
			->where('record_id', $attribute_group->attribute_group_id)
			->get() as $ocme_filter_condition 
		) {
			$ocme_filter_condition->delete();
		}
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function attributes() {
		return $this->hasMany('\Ocme\Model\Attribute', 'attribute_group_id', 'attribute_group_id');
	}
	
}