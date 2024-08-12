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
 * @property int $language_id
 * @property string $name
 * @property string $seo_url
 * 
 * @param AttributeValue $attribute_value
 * @param Language $language
 */

class AttributeValueDescription extends \Ocme\Database\Model\Description {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute_value_description';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'attribute_value_id', 'language_id', 'name', 'seo_url',
	);
	
	////////////////////////////////////////////////////////////////////////////
	
	public static function boot() {
		parent::boot();
		
		/* @var $attribute_value_description AttributeValueDescription */
		self::saved(function( $attribute_value_description ){
			/* @var $attribute_value_description AttributeValueDescription */
			
			switch( $attribute_value_description->attribute_value->attribute->values_type ) {
				case Attribute::VALUES_TYPE_STRING : {
					$attribute_value_description->attribute_value->fill(array(
						'vinteger' => null,
						'vfloat' => null,
					));
					
					break;
				}
				case Attribute::VALUES_TYPE_INTEGER : {
					$attribute_value_description->attribute_value->fill(array(
						'vinteger' => intval( $attribute_value_description->name ),
						'vfloat' => null,
					));
					
					break;
				}
				case Attribute::VALUES_TYPE_STRING : {
					$attribute_value_description->attribute_value->fill(array(
						'vinteger' => null,
						'vfloat' => floatval( $attribute_value_description->name ),
					));
					
					break;
				}
			}
			
			$attribute_value_description->attribute_value->save();
		});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute_value() {
		return $this->belongsTo('\Ocme\Model\AttributeValue', 'attribute_value_id', 'attribute_value_id');
	}
	
}