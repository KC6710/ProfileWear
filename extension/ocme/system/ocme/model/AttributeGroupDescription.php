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
 * @property int $language_id
 * @property string $name
 * 
 * @param AttributeGroup $attribute_group
 * @param Language $language
 */

class AttributeGroupDescription extends \Ocme\Database\Model\Description {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute_group_description';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'attribute_group_id', 'language_id', 'name',
	);
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute_group() {
		return $this->belongsTo('\Ocme\Model\AttributeGroup', 'attribute_group_id', 'attribute_group_id');
	}
	
}