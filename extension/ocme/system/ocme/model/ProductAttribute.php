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
 * @property int $product_id
 * @property int $attribute_id
 * @property int $language_id
 * @property string $text
 * @property int $sort_order
 * 
 * @property Product $product
 * @property Attribute $attribute
 * @property Language $language
 * @property OcmeFilterProperty $ocme_filter_property
 * @property ProductAttributeValue[] $product_attribute_values
 */

class ProductAttribute extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_attribute';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = null;

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = array(
		'sort_order' => 0,
	);

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
		'product_id', 'attribute_id', 'language_id', 'text', 'sort_order',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $product_attribute ProductAttribute */
		self::created(function( $product_attribute ){
			/* @var $product_attribute ProductAttribute */
		});
		
		/* @var $product_attribute ProductAttribute */
		self::deleted(function( $product_attribute ){
			/* @var $product_attribute ProductAttribute */
			
			if( $product_attribute->ocme_filter_property ) {
				$product_attribute->ocme_filter_property->delete();
			}
			
			/* @var $product_attribute_value ProductAttributeValue */
			foreach( $product_attribute->product_attribute_values as $product_attribute_value ) {
				$product_attribute_value->delete();
			}
		});
	}
	
	// Functions ///////////////////////////////////////////////////////////////
	
	/**
	 * 
	 */
	public function reCreate() {
		/* @var $parts array */
		$parts = array_unique( array_filter( array_map( function( $part ){
			return trim( $part );
		}, explode( ocme()->variable()->get('attribute.values_separator', ','), $this->text ) ) ) );
		
		if( $parts ) {
			/* @var $values AttributeValueDescription */
			$values = ocme()->collection()->make( AttributeValueDescription::query()
				->select('`avd`.*')
				->addFromAlias('`avd`')
				->join('attribute_value AS `av`', '`av`.attribute_value_id', '=', '`avd`.attribute_value_id')
				->where('`av`.attribute_id', $this->attribute_id)
				->whereIn('`avd`.name', $parts)
				->get()
			)->map(function($v){
				return array_replace($v, array(
					'name' => ocme()->str()->lower( $v['name'] ),
				));
			})->groupBy('name');
			
			/* @var $attribute_value_ids array */
			$attribute_value_ids = array();

			/* @var $part string */
			foreach( $parts as $part ) {
				/* @var $key string */
				$key = ocme()->str()->lower( $part );
				
				/* @var $attribute_value_id int */
				$attribute_value_id = $values->has( $key ) ?
					ocme()->arr()->get( $values->get( $key )->first(), 'attribute_value_id' ) : AttributeValue::create(array( 'attribute_id' => $this->attribute_id ))->attribute_value_id;
				
				if( ! $values->has( $key ) || $values->get( $key )->where( 'language_id', $this->language_id )->isEmpty() ) {
					AttributeValueDescription::create(array(
						'attribute_value_id' => $attribute_value_id,
						'language_id' => $this->language_id,
						'name' => $part,
					));
				}
				
				$attribute_value_ids[] = $attribute_value_id;
			}
			
			if( null != ( $attribute_value_ids = array_unique( $attribute_value_ids ) ) ) {
				/* @var $attribute_value_id int */
				foreach( $attribute_value_ids as $attribute_value_id ) {
					ProductAttributeValue::firstOrCreate(array(
						'product_id' => $this->product_id,
						'attribute_id' => $this->attribute_id,
						'attribute_value_id' => $attribute_value_id,
					));
				}
			}
		}
	}

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        $query->where( 'product_id', $this->product_id )->where( 'attribute_id', $this->attribute_id )->where( 'language_id', $this->language_id );

        return $query;
    }
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeMissing( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->where('text', '!=', '')
			->whereNotExists(function($q) {
				$q->select(ocme()->db()->raw(1))
					->from('product_attribute_value')
					->whereColumn('product_attribute.attribute_id', 'product_attribute_value.attribute_id');
			});
	}
	
	public function scopeRedundant( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->where(function($q){
				$q->whereNotExists(function($q) {
					$q->select(ocme()->db()->raw(1))
						->from('product')
						->whereColumn('product.product_id', 'product_attribute.product_id');
				})->orWhereNotExists(function($q) {
					$q->select(ocme()->db()->raw(1))
						->from('attribute')
						->whereColumn('attribute.attribute_id', 'product_attribute.attribute_id');
				});
			});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
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
	public function language() {
		return $this->belongsTo('\Ocme\Model\Language', 'language_id', 'language_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_property() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterProperty', 'attribute_id', 'attribute_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function product_attribute_values() {
		return $this->belongsTo('\Ocme\Model\ProductAttributeValues', 'attribute_id', 'attribute_id');
	}
	
}