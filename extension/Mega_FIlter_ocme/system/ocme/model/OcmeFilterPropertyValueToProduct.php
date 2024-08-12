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
 * @property int $ocme_filter_property_id
 * @property int $ocme_filter_property_value_id
 * @property int $product_id
 * @property int $attribute_id
 * @property int $attribute_value_id
 * @property int $option_id
 * @property int $option_value_id
 * @property int $filter_group_id
 * @property int $filter_id
 * 
 * @property OcmeFilterProperty $ocme_filter_property
 * @property OcmeFilterPropertyValue $ocme_filter_property_value
 * @property Product $product
 * @property Attribute $attribute
 * @property AttributeValue $attribute_value
 * @property Option $option
 * @property OptionValue $option_value
 * @property FilterGroup $filter_group
 * @property Filter $filter
 */

class OcmeFilterPropertyValueToProduct extends \Ocme\Database\Model {
	
	const TYPE_PRODUCT = 'product';
	const TYPE_ATTRIBUTE = 'attribute';
	const TYPE_ATTRIBUTE_VALUE = 'attribute_value';
	const TYPE_OPTION = 'option';
	const TYPE_OPTION_VALUE = 'option_value';
	const TYPE_FILTER_GROUP = 'filter_group';
	const TYPE_FILTER = 'filter';
	const TYPE_OCME_FILTER_PROPERTY = 'ocme_filter_property';
	const TYPE_OCME_FILTER_PROPERTY_VALUE = 'ocme_filter_property_value';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ocme_filter_property_value_to_product';

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'ocme_filter_property_id', 'ocme_filter_property_value_id', 'product_id', 'attribute_id', 'attribute_value_id', 'option_id', 'option_value_id', 'filter_group_id', 'filter_id',
	);
	
	public static function boot() {
		parent::boot();
	}
	
	// Static functions ////////////////////////////////////////////////////////
	
	public static function reIndex( $limit, $types = null ) {
		if( ! is_array( $types ) ) {
			$types = is_null( $types ) ? array( self::TYPE_ATTRIBUTE, self::TYPE_FILTER_GROUP, self::TYPE_OPTION ) : array( $types );
		}
		
		/* @var $type string|null */
		foreach( $types as $type ) {
			/* @var $class string */
			$class = '\\Ocme\\Model\\Product';
			
			if( in_array( $type, array( self::TYPE_ATTRIBUTE, self::TYPE_ATTRIBUTE_VALUE ) ) ) {
				$class .= 'AttributeValue';
			} else if( in_array( $type, array( self::TYPE_OPTION, self::TYPE_OPTION_VALUE ) ) ) {
				$class .= 'OptionValue';
			} else if( in_array( $type, array( self::TYPE_FILTER, self::TYPE_FILTER_GROUP ) ) ) {
				$class .= 'Filter';
			} else {
				continue;
			}
			
			/* @var $query \Illuminate\Database\Eloquent\Builder */
			$query = call_user_func( array( $class, 'query' ) );

			/* @var $item ProductAttribute|ProductOption|ProductFilter */
			foreach( $query->with('ocme_filter_property_value')->missingOcmeFilterPropertyValueToProduct()->limit( $limit )->get() as $item ) {
				self::create(array(
					'ocme_filter_property_id' => $item->ocme_filter_property_value->ocme_filter_property_id,
					'ocme_filter_property_value_id' => $item->ocme_filter_property_value->id,
					'product_id' => $item->product_id,
					'attribute_id' => $item->attribute_id,
					'attribute_value_id' => $item->attribute_value_id,
					'option_id' => $item->option_id,
					'option_value_id' => $item->option_value_id,
					'filter_group_id' => $item->filter_id ? $item->filter->filter_group_id : null,
					'filter_id' => $item->filter_id,
				));
			}
		}
	}
	
	public static function reIndexProduct( $product_id ) {
		ProductAttributeValue::query()
			->where('product_attribute_value.product_id', $product_id)
			->whereNotExists(function($q){
				$q->select(ocme()->db()->raw(1))->from('ocme_filter_property_value_to_product')->whereColumn('ocme_filter_property_value_to_product.attribute_value_id', 'product_attribute_value.attribute_value_id');
			})
			->with('ocme_filter_property_value')
			->get()
			/* @var $product_attribute_value ProductAttributeValue */
			->each(function( $product_attribute_value ) use( $product_id ){
				/* @var $product_attribute_value ProductAttributeValue */
				
				self::create(array(
					'ocme_filter_property_id' => $product_attribute_value->ocme_filter_property_value->ocme_filter_property_id,
					'ocme_filter_property_value_id' => $product_attribute_value->ocme_filter_property_value->id,
					'product_id' => $product_id,
					'attribute_id' => $product_attribute_value->attribute_id,
					'attribute_value_id' => $product_attribute_value->attribute_value_id,
				));
			});
			
		ProductOptionValue::query()
			->where('product_option_value.product_id', $product_id)
			->whereNotExists(function($q){
				$q->select(ocme()->db()->raw(1))->from('ocme_filter_property_value_to_product')->whereColumn('ocme_filter_property_value_to_product.option_value_id', 'product_option_value.option_value_id');
			})
			->with('ocme_filter_property_value')
			->get()
			/* @var $product_option_value ProductOptionValue */
			->each(function( $product_option_value ) use( $product_id ){
				/* @var $product_option_value ProductOptionValue */
				
				self::create(array(
					'ocme_filter_property_id' => $product_option_value->ocme_filter_property_value->ocme_filter_property_id,
					'ocme_filter_property_value_id' => $product_option_value->ocme_filter_property_value->id,
					'product_id' => $product_id,
					'option_id' => $product_option_value->option_id,
					'option_value_id' => $product_option_value->option_value_id,
				));
			});
			
		ProductFilter::query()
			->where('product_filter.product_id', $product_id)
			->whereNotExists(function($q){
				$q->select(ocme()->db()->raw(1))->from('option_filter_property_value_to_product')->whereColumn('ocme_filter_property_value_to_product.filter_id', 'product_filter.filter_id');
			})
			->with(array(
				'ocme_filter_property_value', 'filter',
			))
			->get()
			/* @var $product_filter ProductFilter */
			->each(function( $product_filter ) use( $product_id ){
				/* @var $product_filter ProductFilter */
				
				self::create(array(
					'ocme_filter_property_id' => $product_filter->ocme_filter_property_value->ocme_filter_property_id,
					'ocme_filter_property_value_id' => $product_filter->ocme_filter_property_value->id,
					'product_id' => $product_id,
					'filter_group_id' => $product_filter->filter->filter_group_id,
					'filter_id' => $product_filter->filter_id,
				));
			});
	}
	
	// Functions ///////////////////////////////////////////////////////////////

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        $query->where( 'ocme_filter_property_id', $this->ocme_filter_property_id )->where( 'ocme_filter_property_value_id', $this->ocme_filter_property_value_id )->where( 'product_id', $this->product_id );

        return $query;
    }
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	/**
	 * 
	 * @param type $query
	 * @param string|array $types
	 * @return array
	 */
	private function applyTypes( $query, $types ) {		
		if( ! is_array( $types ) ) {
			$types = array( $types );
		}
		
		if( in_array( self::TYPE_ATTRIBUTE, $types ) ) {
			$query->whereNotNull('attribute_id');
		}
		
		if( in_array( self::TYPE_ATTRIBUTE_VALUE, $types ) ) {
			$query->whereNotNull('attribute_value_id');
		}
		
		if( in_array( self::TYPE_OPTION, $types ) ) {
			$query->whereNotNull('option_id');
		}
		
		if( in_array( self::TYPE_OPTION_VALUE, $types ) ) {
			$query->whereNotNull('option_value_id');
		}
		
		if( in_array( self::TYPE_FILTER_GROUP, $types ) ) {
			$query->whereNotNull('filter_group_id');
		}
		
		if( in_array( self::TYPE_FILTER, $types ) ) {
			$query->whereNotNull('filter_id');
		}
		
		return $types;
	}
	
	public function scopeDamaged( $query, $types ) {
		$query->where(function($q){
			$q->orWhereNotExists(function($q){
				
			});
		});
	}
	
	public function scopeRedundant( $query, $types ) {
		$types = $this->applyTypes($query, $types);
		
		$query
			->where(function($q) use( $types ){
				/* @var $type string */
				foreach( $types as $type ) {
					if( $type == self::TYPE_PRODUCT ) {
						$q->orWhereNotExists(function($q) {
							$q->select(ocme()->db()->raw(1))->from( 'product' )->whereColumn( 'product.product_id', 'ocme_filter_property_value_to_product.product_id');
						});
					} else if( $type == self::TYPE_ATTRIBUTE ) {
						$q->orWhereNotExists(function($q) {
							$q->select(ocme()->db()->raw(1))->from( 'attribute' )->whereColumn( 'attribute.attribute_id', 'ocme_filter_property_value_to_product.attribute_id');
						});
					} else if( $type == self::TYPE_ATTRIBUTE_VALUE ) {
						$q->orWhereNotExists(function($q) {
							$q->select(ocme()->db()->raw(1))->from( 'attribute_value' )->whereColumn( 'attribute_value.attribute_value_id', 'ocme_filter_property_value_to_product.attribute_value_id');
						});
					} else if( $type == self::TYPE_OPTION ) {
						$q->orWhereNotExists(function($q) {
							$q->select(ocme()->db()->raw(1))->from( 'option' )->whereColumn( 'option.option_id', 'ocme_filter_property_value_to_product.option_id');
						});
					} else if( $type == self::TYPE_OPTION_VALUE ) {
						$q->orWhereNotExists(function($q) {
							$q->select(ocme()->db()->raw(1))->from( 'option_value' )->whereColumn( 'option_value.option_value_id', 'ocme_filter_property_value_to_product.option_value_id');
						});
					} else if( $type == self::TYPE_FILTER_GROUP ) {
						$q->orWhereNotExists(function($q) {
							$q->select(ocme()->db()->raw(1))->from( 'filter_group' )->whereColumn( 'filter_group.filter_group_id', 'ocme_filter_property_value_to_product.filter_group_id');
						});
					} else if( $type == self::TYPE_FILTER ) {
						$q->orWhereNotExists(function($q) {
							$q->select(ocme()->db()->raw(1))->from( 'filter' )->whereColumn( 'filter.filter_id', 'ocme_filter_property_value_to_product.filter_id');
						});
					}
				}
			});
	}
	
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
	public function ocme_filter_property_value() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterPropertyValue', 'ocme_filter_property_value_id', 'id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product() {
		return $this->belongsTo('\Ocme\Model\Product', 'product_id', 'product_id');
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
	
}