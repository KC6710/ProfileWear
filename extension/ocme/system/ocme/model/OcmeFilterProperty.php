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
 * @property int $attribute_id
 * @property int $option_id
 * @property int $filter_group_id
 * 
 * @property Attribute $attribute
 * @property Option $option
 * @property FilterGroup $filter_group
 * @property OcmeFilterPropertyValue[] $ocme_filter_property_values
 */

class OcmeFilterProperty extends \Ocme\Database\Model {
	
	const TYPE_ATTRIBUTE = 'attribute';
	const TYPE_OPTION = 'option';
	const TYPE_FILTER_GROUP = 'filter_group';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ocme_filter_property';

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
		'attribute_id', 'option_id', 'filter_group_id',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array(
		'attribute_id', 'option_id', 'filter_group_id',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $ocme_filter_property OcmeFilterProperty */
		self::deleted(function( $ocme_filter_property ){
			/* @var $ocme_filter_property OcmeFilterProperty */
			
			/* @var $ocme_filter_property_value OcmeFilterPropertyValue */
			foreach( $ocme_filter_property->ocme_filter_property_values as $ocme_filter_property_value ) {
				$ocme_filter_property_value->delete();
			}
		});
	}
	
	// Static functions ////////////////////////////////////////////////////////
	
	public static function reIndex( $limit, $types = null ) {
		if( ! is_array( $types ) ) {
			$types = is_null( $types ) ? array( self::TYPE_ATTRIBUTE, self::TYPE_FILTER_GROUP, self::TYPE_OPTION ) : array( $types );
		}
		
		/* @var $type string|null */
		foreach( $types as $type ) {
			/* @var $class string */
			$class = '\\Ocme\\Model\\' . ocme()->str()->studly( $type );
			
			/* @var $query \Illuminate\Database\Eloquent\Builder */
			$query = call_user_func( array( $class, 'query' ) );

			/* @var $item Attribute|Option|FilterGroup */
			foreach( $query->missingOcmeFilterProperty()->limit( $limit )->get() as $item ) {
				self::create(array(					
					$type . '_id' => $item->getKey(),
				));
			}
		}
	}
	
	public static function createIfNotExists( \Ocme\Database\Model $model ) {
		if( ! $model->ocme_filter_property ) {
			$model->setRelation('ocme_filter_property', self::firstOrCreate(array(
				$model->getKeyName() => $model->getKey()
			)));
		}
		
		return $model->ocme_filter_property;
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
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $types
	 */
	public function scopeRedundant( \Illuminate\Database\Eloquent\Builder $query, $types = null ) {
		if( ! is_array( $types ) ) {
			$types = is_null( $types ) ? array( null ) : array( $types );
		}
		
		if( in_array( self::TYPE_ATTRIBUTE, $types ) ) {
			$query->whereNotNull('attribute_id');
		}
		
		if( in_array( self::TYPE_FILTER_GROUP, $types ) ) {
			$query->whereNotNull('filter_group_id');
		}
		
		if( in_array( self::TYPE_OPTION, $types ) ) {
			$query->whereNotNull('option_id');
		}
		
		$query->where(function($q) use( $types ){
			/* @var $type string */
			foreach( $types as $type ) {
				if( is_null( $type ) || $type == self::TYPE_ATTRIBUTE ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('attribute')->whereColumn('attribute.attribute_id', 'ocme_filter_property.attribute_id');
					});
				}
				
				if( is_null( $type ) || $type == self::TYPE_OPTION ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('option')->whereColumn('option.option_id', 'ocme_filter_property.option_id');
					});
				}
				
				if( is_null( $type ) || $type == self::TYPE_FILTER_GROUP ) {
					$q->orWhereNotExists(function($q){
						$q->select(ocme()->db()->raw(1))->from('filter_group')->whereColumn('filter_group.filter_group_id', 'ocme_filter_property.filter_group_id');
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
	public function attribute() {
		return $this->belongsTo('\Ocme\Model\Attribute', 'property_id', 'attribute_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function option() {
		return $this->belongsTo('\Ocme\Model\Option', 'property_id', 'option_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function filter_group() {
		return $this->belongsTo('\Ocme\Model\FilterGroup', 'property_id', 'filter_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function ocme_filter_property_values() {
		return $this->hasMany('\Ocme\Model\OcmeFilterPropertyValue', 'ocme_filter_property_id', 'id');
	}
	
}