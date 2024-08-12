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
 * @property int $attribute_id
 * @property int $attribute_group_id
 * @property int $sort_order
 * @property array $store_ids
 * @property bool $with_image
 * @property bool $with_color
 * @property string $displayed_values_separator
 * @property string $values_type
 * 
 * @property AttributeDescription[] $descriptions
 * @property AttributeValue[] $values
 * @property AttributeGroup $attribute_group
 * @property OcmeFilterProperty $ocme_filter_property
 */

class Attribute extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;
	use \Ocme\Database\MissingOcmeFilterProperty;
	
	const VALUES_TYPE_STRING = 'string';
	const VALUES_TYPE_INTEGER = 'integer';
	const VALUES_TYPE_FLOAT = 'float';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'attribute_id';

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
		'attribute_group_id', 'sort_order', 'store_ids', 'with_image', 'with_color', 'displayed_values_separator', 'values_type',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array(
		'displayed_values_separator',
	);
	
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * @param \Ocme\Model\Attribute $attribute
	 */
	public static function eventCreated( Attribute $attribute ) {
		OcmeFilterProperty::create(array(
			'attribute_id' => $attribute->attribute_id,
		));
	}
	
	/**
	 * @param \Ocme\Model\Attribute $attribute
	 */
	public static function eventSaved( Attribute $attribute ) {
		if( $attribute->isDirty('values_type') ) {
			/* @var $props array */
			$props = array(
				'vinteger' => null,
				'vfloat' => null,
			);

			/* @var $field string */
			$field = null;

			switch( $attribute->values_type ) {
				case Attribute::VALUES_TYPE_INTEGER : {
					$field = 'vinteger';

					break;
				}
				case Attribute::VALUES_TYPE_FLOAT : {
					$field = 'vfloat';

					break;
				}
			}

			if( $field ) {
				$props[$field] = ocme()->db()->raw('`avd`.name');
			}

			/* @var $query \Illuminate\Database\Eloquent\Builder */
			$query = AttributeValue::where('attribute_id', $attribute->attribute_id);

			if( $field ) {
				$query->join('attribute_value_description AS `avd`', function($join){
					$join
						->on('`avd`.attribute_value_id', '=', 'attribute_value.attribute_value_id')
						->on('`avd`.language_id', '=', ocme()->db()->raw( ocme()->oc()->registry()->get('config')->get('config_language_id') ) );
				});
			}

			$query->update( $props );
		}
	}
	
	/**
	 * @param Attribute $attribute
	 */
	public static function eventDeleted( Attribute $attribute ) {
		if( $attribute->ocme_filter_property ) {
			$attribute->ocme_filter_property->delete();
		}
		
		$attribute->descriptions()->delete();
		
		/* @var $attribute_value AttributeValue */
		foreach( $attribute->values()->get() as $attribute_value ) {
			$attribute_value->delete();
		}
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		foreach( OcmeFilterCondition::where('condition_type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE)
			->where('record_id', $attribute->attribute_id)
			->get() as $ocme_filter_condition 
		) {
			$ocme_filter_condition->delete();
		}
	}
	
	public static function boot() {
		parent::boot();
		
		/* @var $attribute Attribute */
		self::created(function( $attribute ){
			self::eventCreated( $attribute );
		});
		
		/* @var $attribute Attribute */
		self::saved(function( $attribute ){
			self::eventSaved( $attribute );
		});
		
		/* @var $attribute Attribute */
		self::deleted(function( $attribute ){
			self::eventDeleted( $attribute );
		});
	}
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeWithAttributeGroup( \Illuminate\Database\Eloquent\Builder $query, $table_alias = null ) {
		/* @var $foreign_table string */
		if( null == ( $foreign_table = $query->getQuery()->getFromAlias() ) ) {
			$foreign_table = $this->getTable();
		}
		
		/* @var $table string */
		$table = 'attribute_group';
		
		if( is_null( $table_alias ) ) {
			$table_alias = $table;
		} else {
			$table .= ' AS ' . $table_alias;
		}
		
		$query->leftJoin($table, $table_alias . '.attribute_group_id', '=', $foreign_table . '.attribute_group_id');
	}
	
	public function scopeWithAttributeGroupDescription( \Illuminate\Database\Eloquent\Builder $query, $table_alias = null ) {
		/* @var $foreign_table string */
		if( null == ( $foreign_table = $query->getQuery()->getFromAlias() ) ) {
			$foreign_table = $this->getTable();
		}
		
		/* @var $table string */
		$table = 'attribute_group_description';
		
		if( is_null( $table_alias ) ) {
			$table_alias = $table;
		} else {
			$table .= ' AS ' . $table_alias;
		}
		
		$query
			->leftJoin($table, $table_alias . '.attribute_group_id', '=', $foreign_table . '.attribute_group_id')
			->where($table_alias . '.language_id', ocme()->oc()->registry()->get('config')->get('config_language_id'));
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function values() {
		return $this->hasMany('\Ocme\Model\AttributeValue', 'attribute_id', 'attribute_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute_group() {
		return $this->belongsTo('\Ocme\Model\AttributeGroup', 'attribute_group_id', 'attribute_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property() {
		return $this->hasOne('\Ocme\Model\OcmeFilterProperty', 'attribute_id', 'attribute_id');
	}
	
}