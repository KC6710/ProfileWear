<?php namespace Ocme\OpenCart\Admin\Model;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use Ocme\Model\Product,
	Ocme\Model\ProductAttribute,
	Ocme\Model\ProductAttributeValue,
	Ocme\Model\FilterGroup,
	Ocme\Model\ProductDescription,
	Ocme\Model\OcmeFilterCondition,
	Ocme\Model\ProductToTag,
	Ocme\Model\OcmeFilterPropertyValueToProduct;

class Filter {
	
	/**
	 * @var array
	 */
	protected $filter_base_conditions = array(
		'base_attributes' => array(
			'type' => OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE,
			'names' => array( 
				'price', 'search', 'manufacturer', 'category', 'tags', 'availability',
			)
		),
		'properties' => array(
			'type' => OcmeFilterCondition::CONDITION_TYPE_PROPERTY,
			'names' => array(
				'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 'length', 'width', 'height', 'weight', 'quantity',
			)
		)
	);
	
	public function filterBaseConditions( $key = null ) {
		if( is_null( $key ) ) {
			return $this->filter_base_conditions;
		}
		
		return ocme()->arr()->get( $this->filter_base_conditions, $key );
	}
	
	public function eventAfterEditProduct( $args, $output = null ) {
		return $this->updateProduct( $args[0], $args[1] );
	}
	
	public function eventAfterAddProduct( $args, $output = null ) {
		return $this->updateProduct( $output, $args[0] );
	}
	
	public function eventAfterCopyProduct( $args, $output = null ) {
		return $this->updateProduct( $args[0], array() );
	}
	
	public function eventBeforeDeleteProduct( $args ) {
		ProductToTag::where('product_id', $args[0])->delete();
		OcmeFilterPropertyValueToProduct::where('product_id', $args[0])->delete();
	}
	
	public function eventAfterDeleteProduct( $args ) {}
	
	public function eventBeforeDeleteFilter( $args ) {
		/* @var $filter_group FilterGroup */
		if( null != ( $filter_group = FilterGroup::find( $args[0] ) ) ) {		
			FilterGroup::eventDeleted( $filter_group );
		}
	}
	
	public function updateProduct( $product_id, array $data ) {
		$this->reIndexProduct($product_id, $data);
	}
	
	public function reIndexProduct( $product_id, array $data = null ) {
		/* @var $product_attribute ProductAttribute */
		foreach( ProductAttribute::where('product_id', $product_id)->get() as $product_attribute ) {
			$product_attribute->reCreate();
		}
		
		/* @var $product_description ProductDescription */
		foreach( ProductDescription::where('product_id', $product_id)->get() as $product_description ) {
			$product_description->reIndexTags();
		}
		
		/* @var $product_attribute_value ProductAttributeValue */
		foreach( ProductAttributeValue::query()
			->addFromAlias('`pav`')
			->where('`pav`.product_id', $product_id)
			->where(function($q){
				$q->orWhereNotExists(function($q){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_attribute AS `pa`')
						->whereColumn('`pav`.product_id', '`pa`.product_id')
						->whereColumn('`pav`.attribute_id', '`pa`.attribute_id');
				})->orWhereNotExists(function($q){
					$q
						->select( ocme()->db()->raw(1) )
						->from('attribute AS `a`')
						->whereColumn('`pav`.attribute_id', '`a`.attribute_id');
				})->orWhereNotExists(function($q){
					$q
						->select( ocme()->db()->raw(1) )
						->from('attribute_value AS `av`')
						->whereColumn('`pav`.attribute_value_id', '`av`.attribute_value_id');
				});
			})
			->get() as $product_attribute_value 
		) {
			$product_attribute_value->delete();
		}
		
		Product::where('product_id', $product_id)->update(array(
			'ocme_filter_indexed_at' => ocme()->db()->raw( 'NOW()' ),
		));
	}
	
}