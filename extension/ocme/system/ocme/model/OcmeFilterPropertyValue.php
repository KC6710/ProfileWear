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
 * @property int $id
 * @property int $ocme_filter_property_id
 * @property int $attribute_id
 * @property int $attribute_value_id
 * @property int $option_id
 * @property int $option_value_id
 * @property int $filter_group_id
 * @property int $filter_id
 * 
 * @property OcmeFilterProperty $ocme_filter_property
 * @property Attribute $attribute
 * @property AttributeValue $attribute_value
 * @property Option $option
 * @property OptionValue $option_value
 * @property FilterGroup $filter_group
 * @property Filter $filter
 * 
 * @property OcmeFilterPropertyValueToProduct[] $ocme_filter_property_value_to_products
 */

class OcmeFilterPropertyValue extends \Ocme\Database\Model {
	
	const TYPE_ATTRIBUTE = 'attribute';
	const TYPE_ATTRIBUTE_VALUE = 'attribute_value';
	const TYPE_OPTION = 'option';
	const TYPE_OPTION_VALUE = 'option_value';
	const TYPE_FILTER_GROUP = 'filter_group';
	const TYPE_FILTER = 'filter';
	const TYPE_ITSELF = 'itself';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ocme_filter_property_value';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'ocme_filter_property_id', 'attribute_id', 'attribute_value_id', 'option_id', 'option_value_id', 'filter_group_id', 'filter_id',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array();
	
	public static function boot() {
		parent::boot();
		
		/* @var $ocme_filter_property_value OcmeFilterPropertyValue */
		self::deleted(function( $ocme_filter_property_value ){
			/* @var $ocme_filter_property_value OcmeFilterPropertyValue */
			
			$ocme_filter_property_value->ocme_filter_property_value_to_products()->delete();
		});
	}
	
	// Static functions ////////////////////////////////////////////////////////
	
	public static function reIndex( $limit, $types = null ) {
		if( ! is_array( $types ) ) {
			$types = is_null( $types ) ? array( self::TYPE_ATTRIBUTE_VALUE, self::TYPE_FILTER, self::TYPE_OPTION_VALUE ) : array( $types );
		}
		
		/* @var $type string|null */
		foreach( $types as $type ) {
			/* @var $class string */
			$class = '\\Ocme\\Model\\' . ocme()->str()->studly( $type );
			
			/* @var $query \Illuminate\Database\Eloquent\Builder */
			$query = call_user_func( array( $class, 'query' ) );

			/* @var $item AttributeValue|OptionValue|Filter */
			foreach( $query->missingOcmeFilterPropertyValue()->with('ocme_filter_property')->limit( $limit )->get() as $item ) {				
				/* @var $ocme_filter_property OcmeFilterProperty */
				$ocme_filter_property = OcmeFilterProperty::createIfNotExists( self::parentProperty( $item ) );
				
				self::create(array(
					'ocme_filter_property_id' => $ocme_filter_property->id,
					$ocme_filter_property->propertyKeyName() => $ocme_filter_property->propertyKey(),
					$type . '_id' => $item->getKey(),
				));
			}
		}
	}
	
	protected static function parentProperty( \Ocme\Database\Model $model ) {
		if( $model instanceof AttributeValue ) {
			return $model->attribute;
		}
		
		if( $model instanceof Filter ) {
			return $model->filter_group;
		}
		
		if( $model instanceof OptionValue ) {
			return $model->option;
		}
	}
	
	public static function createIfNotExists( \Ocme\Database\Model $model ) {
		if( ! $model->ocme_filter_property_value ) {
			/* @var $ocme_filter_property OcmeFilterProperty */
			$ocme_filter_property = OcmeFilterProperty::createIfNotExists( self::parentProperty( $model ) );
			
			$model->setRelation('ocme_filter_property_value', self::firstOrCreate(array(
				'ocme_filter_property_id' => $ocme_filter_property->id,
				$ocme_filter_property->propertyKeyName() => $ocme_filter_property->propertyKey(),
				$model->getKeyName() => $model->getKey()
			)));
		}
		
		return $model->ocme_filter_property_value;
	}
	
	// Functions ///////////////////////////////////////////////////////////////
	
	public function propertyKeyName() {
		if( $this->attribute_id ) {
			return 'attribute_id';
		}
		
		if( $this->option_id ) {
			return 'option_id';
		}
		
		if( $this->filter_group_id ) {
			return 'filter_group_id';
		}
	}
	
	public function propertyKey() {
		return $this->{$this->propertyKeyName()};
	}
	
	public function propertyValueKeyName() {
		if( $this->attribute_value_id ) {
			return 'attribute_value_id';
		}
		
		if( $this->option_value_id ) {
			return 'option_value_id';
		}
		
		if( $this->filter_id ) {
			return 'filter_id';
		}
	}
	
	public function propertyValueKey() {
		return $this->{$this->propertyValueKeyName()};
	}
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder  $query
	 * @param string|array $types
	 */
	public function scopeRedundant( \Illuminate\Database\Eloquent\Builder $query, $types = null ) {
		if( ! is_array( $types ) ) {
			$types = is_null( $types ) ? array( null ) : array( $types );
		}
		
		if( in_array( null, $types ) || in_array( self::TYPE_ATTRIBUTE, $types ) ) {
			$query->whereNotNull('attribute_id');
		}
		
		if( in_array( null, $types ) || in_array( self::TYPE_ATTRIBUTE_VALUE, $types ) ) {
			$query->whereNotNull('attribute_value_id');
		}
		
		if( in_array( null, $types ) || in_array( self::TYPE_OPTION, $types ) ) {
			$query->whereNotNull('option_id');
		}
		
		if( in_array( null, $types ) || in_array( self::TYPE_OPTION_VALUE, $types ) ) {
			$query->whereNotNull('option_value_id');
		}
		
		if( in_array( null, $types ) || in_array( self::TYPE_FILTER_GROUP, $types ) ) {
			$query->whereNotNull('filter_group_id');
		}
		
		if( in_array( null, $types ) || in_array( self::TYPE_FILTER, $types ) ) {
			$query->whereNotNull('filter_id');
		}
		
		$query->where(function($q) use( $types ){
			/* @var $type string */
			foreach( $types as $type ) {
				if( is_null( $type ) || $type == self::TYPE_ITSELF ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('ocme_filter_property')->whereColumn('ocme_filter_property.id', 'ocme_filter_property_value.ocme_filter_property_id');
					});
				}
				
				if( is_null( $type ) || $type == self::TYPE_ATTRIBUTE ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('attribute')->whereColumn('attribute.attribute_id', 'ocme_filter_property_value.attribute_id');
					});
				}
				
				if( is_null( $type ) || $type == self::TYPE_ATTRIBUTE_VALUE ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('attribute_value')->whereColumn('attribute_value.attribute_value_id', 'ocme_filter_property_value.attribute_value_id');
					});
				}

				if( is_null( $type ) || $type == self::TYPE_OPTION ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('option')->whereColumn('option.option_id', 'ocme_filter_property_value.option_id');
					});
				}
				
				if( is_null( $type ) || $type == self::TYPE_OPTION_VALUE ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('option_value')->whereColumn('option_value.option_value_id', 'ocme_filter_property_value.option_value_id');
					});
				}

				if( is_null( $type ) || $type == self::TYPE_FILTER_GROUP ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('filter_group')->whereColumn('filter_group.filter_group_id', 'ocme_filter_property_value.filter_group_id');
					});
				}
				
				if( is_null( $type ) || $type == self::TYPE_FILTER ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('filter')->whereColumn('filter.filter_id', 'ocme_filter_property_value.filter_id');
					});
				}
			}
		});
	}
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_property() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterProperty', 'ocme_filter_property_id', 'id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute() {
		return $this->belongsTo('\Ocme\Model\Attribute', 'attribute_id', 'attribute_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute_value() {
		return $this->belongsTo('\Ocme\Model\AttributeValue', 'attribute_value_id', 'attribute_value_id');
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
	public function filter_group() {
		return $this->belongsTo('\Ocme\Model\FilterGroup', 'filter_group_id', 'filter_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function filter() {
		return $this->belongsTo('\Ocme\Model\Filter', 'filter_id', 'filter_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function ocme_filter_property_value_to_products() {
		return $this->hasMany('\Ocme\Model\OcmeFilterPropertyValueToProduct', 'ocme_filter_property_value_id', 'id');
	}
	
}