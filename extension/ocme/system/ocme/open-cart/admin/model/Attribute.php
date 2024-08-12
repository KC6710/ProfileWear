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

use Ocme\Model\Attribute as AttributeModel,
	Ocme\Model\AttributeGroup,
	Ocme\Model\AttributeValue,
	Ocme\Model\AttributeValueDescription,
	Ocme\Model\Product,
	Ocme\Model\ProductAttribute,
	Ocme\Model\ProductAttributeValue,
	Ocme\Model\OcmeFilterProperty,
	Ocme\Model\Language;

class Attribute {
	
	use \Ocme\Database\Helper\ApplySortOrder;
	use \Ocme\Database\Helper\ApplyPagination;
	
	////////////////////////////////////////////////////////////////////////////
	
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
		ProductAttributeValue::where('product_id', $args[0])->delete();
	}
	
	public function updateProduct( $product_id, array $data ) {
		/* @var $removed_attribute_ids array */
		if( null != ( $removed_attribute_ids = ocme()->arr()->get( $data, 'ocme_removed_product_attribute_ids' ) ) ) {
			/* @var $product_attribute ProductAttribute */
			foreach( ProductAttribute::where('product_id', $product_id)->whereIn('attribute_id', $removed_attribute_ids)->get() as $product_attribute ) {
				$product_attribute->delete();
			}
		}
		
		/* @var $ocme_removed_product_attribute_value_ids array */
		if( null != ( $removed_attribute_value_ids = ocme()->arr()->get( $data, 'ocme_removed_product_attribute_value_ids' ) ) ) {
			/* @var $product_attribute_value ProductAttributeValue */
			foreach( ProductAttributeValue::where('product_id', $product_id)->whereIn('product_attribute_value_id', $removed_attribute_value_ids)->get() as $product_attribute_value ) {
				$product_attribute_value->delete();
			}
		}
		
		/* @var $attributes array */
		if( null != ( $attributes = ocme()->arr()->get( $data, 'ocme_product_attribute' ) ) ) {
			/* @var $sort_orders array */
			$sort_orders = array();
			
			/* @var $languages array */
			$languages = Language::query()->statusEnabled()->get();
			
			/**
			 * @var $attribute_id int
			 * @var $attribute array
			 */
			foreach( $attributes as $attribute_id => $attribute ) {
				/* @var $values array */
				if( null != ( $values = ocme()->arr()->get( $attribute, 'values' ) ) ) {
					/**
					 * @var $sort_order int
					 * @var $value_id int
					 */
					foreach( $values as $sort_order => $value_id ) {
						/* @var $product_attribute_value ProductAttributeValue */
						$product_attribute_value = ProductAttributeValue::firstOrNew(array(
							'product_id' => $product_id,
							'attribute_id' => $attribute_id,
							'attribute_value_id' => $value_id,
						));
						
						$product_attribute_value->fill(array(
							'sort_order' => $sort_order,
						))->save();
					}
				}
				
				$sort_orders[$attribute_id] = ocme()->arr()->get( $attribute, 'sort_order', 0 );
			}
			
			/* @var $descriptions array */
			$descriptions = array();
			
			foreach( ProductAttributeValue::query()
				->select(array(
					'`avd`.*',
					'`pav`.attribute_id',
				))
				->addFromAlias('`pav`')
				->join('attribute_value_description AS `avd`', '`avd`.attribute_value_id', '=', '`pav`.attribute_value_id')
				->where('product_id', $product_id)
				->orderBy('`pav`.sort_order')
				->get() as $product_attribute_value 
			) {
				$descriptions[$product_attribute_value->attribute_id][$product_attribute_value->language_id][] = $product_attribute_value->name;
			}
			
			/**
			 * @var $attribute_id int
			 * @var $sort_order int
			 */
			foreach( $sort_orders as $attribute_id => $sort_order ) {
				/* @var $language Language */
				foreach( $languages as $language ) {
					ProductAttribute::firstOrNew(array(
						'product_id' => $product_id,
						'attribute_id' => $attribute_id,
						'language_id' => $language->language_id,
					))->fill(array(
						'text' => isset( $descriptions[$attribute_id][$language->language_id] ) ? implode( ', ', $descriptions[$attribute_id][$language->language_id] ) : '',
						'sort_order' => $sort_order,
					))->save();
				}
			}
		}
		
		Product::where('product_id', $product_id)->update(array(
			'ocme_filter_indexed_at' => ocme()->db()->raw( 'NOW()' ),
		));
	}
	
	/**
	 * @param array $data
	 * @return AttributeValue
	 */
	public function addValue( array $data ) {
		/* @var $attributes array */
		$attributes = array_replace(
			array_fill_keys( array( 'image', 'color' ), null ), 
			ocme()->arr()->only( $data, array( 'attribute_id', 'image', 'sort_order', 'color', 'vinteger', 'vfloat' ) )
		);
		
		/* @var $attribute_value AttributeValue */
		$attribute_value = AttributeValue::create( $attributes );
		
		/* @var $language_id int */
		/* @var $desc array */
		foreach( ocme()->arr()->get( $data, 'descriptions', array() ) as $language_id => $desc ) {
			AttributeValueDescription::create(array(
				'attribute_value_id' => $attribute_value->attribute_value_id,
				'language_id' => $language_id,
				'name' => trim( ocme()->arr()->get( $desc, 'name' ) ),
			));
		}
		
		return $attribute_value;
	}
	
	/**
	 * @param int $attribute_value_id
	 * @param array $data
	 * @return AttributeValue|null
	 */
	public function editValue( $attribute_value_id, array $data ) {
		/* @var $attribute_value AttributeValue */
		if( null == ( $attribute_value = AttributeValue::find( $attribute_value_id ) ) ) {
			return null;
		}
		
		$attribute_value->fill(array_replace(
			array_fill_keys( array( 'image', 'color' ), null ), 
			ocme()->arr()->only( $data, array( 'image', 'sort_order', 'color' ) )
		))->save();
		
		/* @var $language_id int */
		/* @var $desc array */
		foreach( ocme()->arr()->get( $data, 'descriptions' ) as $language_id => $desc ) {
			AttributeValueDescription::firstOrNew(array(
				'attribute_value_id' => $attribute_value->attribute_value_id,
				'language_id' => $language_id,
			))->fill(array(
				'name' => trim( ocme()->arr()->get( $desc, 'name' ) ),
				'seo_url' => null,
			))->save();
		}
		
		return $attribute_value;
	}
	
	public function deleteValue( $attribute_value_id ) {
		/* @var $attribute_value AttributeValue */
		if( null != ( $attribute_value = AttributeValue::find( $attribute_value_id ) ) ) {
			AttributeValueDescription::where('attribute_value_id', $attribute_value_id)->delete();
			
			$attribute_value->delete();
		}
		
		return false;
	}
	
	/**
	 * @param string $event
	 * @return bool
	 */
	public function hasEvent( $event ) {
		return method_exists( $this, $event );
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function createAttributeValuesQuery() {
		return AttributeValue::query()
			->addFromAlias('`av`')
			->withDescription('`avd`');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function createAttributeGroupsQuery() {
		return AttributeGroup::query()
			->select(array(
				'`ag`.*',
				'`agd`.*',
			))
			->addFromAlias('`ag`')
			->withDescription('`agd`');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function createAttributesQuery() {
		return AttributeModel::query()
			->select(array(
				'`a`.*',
				'`ad`.*',
			))
			->addFromAlias('`a`')
			->withDescription('`ad`');
	}
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $conditions
	 * @return $this
	 */
	public function applyAttributeConditions( \Illuminate\Database\Eloquent\Builder $query, $conditions ) {
		/* @var $name string */
		if( null != ( $name = ocme()->arr()->get( $conditions, 'filter_name' ) ) ) {
			$query->where('`ad`.name', 'LIKE', '%' . $name . '%');
		}
		
		/* @var $attribute_group_id int */
		if( null != ( $attribute_group_id = ocme()->arr()->get( $conditions, 'filter_attribute_group_id' ) ) ) {
			$query->where('`a`.attribute_group_id', $attribute_group_id);
		}

		/* @var $except_attribute_ids array */
		if( null !== ( $except_attribute_ids = ocme()->arr()->get( $conditions, 'filter_except_attribute_ids' ) ) ) {
			if( ! is_array( $except_attribute_ids ) ) {
				$except_attribute_ids = explode(',', $except_attribute_ids);
			}
			
			$query->whereNotIn('`a`.attribute_id', $except_attribute_ids);
		}
		
		return $this;
	}
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $conditions
	 * @return $this
	 */
	public function applyAttributeValueConditions( \Illuminate\Database\Eloquent\Builder $query, $conditions ) {
		/* @var $name string */
		if( null != ( $name = ocme()->arr()->get( $conditions, 'filter_name' ) ) ) {
			$query->where('`avd`.name', 'LIKE', '%' . $name . '%');
		}
		
		/* @var $attribute_id int */
		if( null != ( $attribute_id = ocme()->arr()->get( $conditions, 'filter_attribute_id' ) ) ) {
			$query->where('`av`.attribute_id', $attribute_id);
		}

		/* @var $except_attribute_value_ids array */
		if( null !== ( $except_attribute_value_ids = ocme()->request()->post('filter_except_attribute_value_ids' ) ) ) {
			$query->whereNotIn('`av`.attribute_value_id', explode(',', $except_attribute_value_ids));
		}
		
		return $this;
	}
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $conditions
	 * @return $this
	 */
	public function applyAttributeGroupConditions( \Illuminate\Database\Eloquent\Builder $query, $conditions ) {
		/* @var $name string */
		if( null != ( $name = ocme()->arr()->get( $conditions, 'filter_name' ) ) ) {
			$query->where('`agd`.name', 'LIKE', '%' . $name . '%');
		}
		
		return $this;
	}
	
	/**
	 * getTotalAttributes
	 * 
	 * @param array $args
	 * @return int
	 */
	public function eventBeforeGetTotalAttributes( &$args ) {
		/** @var array $data */
		$data = isset( $args[0] ) ? $args[0] : array();

		/** @var \Illuminate\Database\Eloquent\Builder $query */
		$query = $this->createAttributesQuery()
			->withAttributeGroupDescription('`agd`');
		
		$this
			->applyAttributeConditions($query, $this->addExtraAttributesFilterData($data));
		
		return $query->count();
	}
	
	/**
	 * getTotalAttributeGroups
	 * 
	 * @return int
	 */
	public function eventBeforeGetTotalAttributeGroups() {		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->createAttributeGroupsQuery();
		
		$this
			->applyAttributeGroupConditions($query, $this->addExtraAttributeGroupsFilterData());
		
		return $query->count();
	}
	
	public function addExtraAttributesFilterData( array $data = array() ) {
		if( in_array( ocme()->request()->ocQueryRoute(), array( 'catalog/attribute' ) ) ) {
			/* @var $filter_name string */
			if( null !== ( $filter_name = ocme()->request()->query('filter_name') ) ) {
				$data['filter_name'] = $filter_name;
			}
			
			/* @var $filter_attribute_group_id int */
			if( null !== ( $filter_attribute_group_id = ocme()->request()->query('filter_attribute_group_id') ) ) {
				$data['filter_attribute_group_id'] = (int) $filter_attribute_group_id;
			}
		}
		
		return $data;
	}
	
	public function addExtraAttributeGroupsFilterData( array $data = array() ) {
		if( in_array( ocme()->request()->ocQueryRoute(), array( 'catalog/attribute_group' ) ) ) {
			/* @var $filter_name string */
			if( null !== ( $filter_name = ocme()->request()->query('filter_name') ) ) {
				$data['filter_name'] = $filter_name;
			}
		}
		
		return $data;
	}
	
	/**
	 * getAttributes
	 * 
	 * @param array $arts
	 * @return array
	 */
	public function eventBeforeGetAttributes( &$args ) {
		/** @var array $data */
		$data = isset( $args[0] ) ? $args[0] : array();

		/** @var \Illuminate\Database\Eloquent\Builder $query */
		$query = $this->createAttributesQuery()
			->addSelect(array(
				'`agd`.name AS attribute_group'
			))
			->withAttributeGroupDescription('`agd`');
			
		$this
			->applyAttributeConditions($query, $this->addExtraAttributesFilterData($data))
			->applyPagination( $query, ocme()->arr()->get( $data, 'start' ), ocme()->arr()->get( $data, 'limit' ) )
			->applySortOrder( $query, $data, array(
				'`ad`.name', 'attribute_group', '`a`.sort_order',
			), array(
				'attribute_group', '`ad`.name',
			));
		
		return $query->get();
	}
	
	/**
	 * getAttributeGroups
	 * 
	 * @param array $args
	 * @return array
	 */
	public function eventBeforeGetAttributeGroups( & $args ) {
		/** @var array $data */
		$data = isset( $args[0] ) ? $args[0] : array();

		/** @var \Illuminate\Database\Eloquent\Builder $query */
		$query = $this->createAttributeGroupsQuery();
		
		$this
			->applyAttributeGroupConditions($query, $this->addExtraAttributeGroupsFilterData($data))
			->applyPagination( $query, ocme()->arr()->get( $data, 'start' ), ocme()->arr()->get( $data, 'limit' ) )
			->applySortOrder( $query, $data, array(
				'`agd`.name', '`ag`.sort_order',
			), array(
				'`agd`.name',
			));
		
		return $query->get();
	}
	
	/**
	 * Update attribute
	 * 
	 * @param int $attribute_id
	 * @param array $data
	 */
	public function updateAttribute( $attribute_id, $data ) {
		/* @var $attribute AttributeModel */
		if( null != ( $attribute = AttributeModel::find( $attribute_id ) ) ) {
			$attribute->fill(array(
				'with_image' => ocme()->arr()->get( $data, 'with_image', '0' ),
				'with_color' => ocme()->arr()->get( $data, 'with_color', '0' ),
				'displayed_values_separator' => ocme()->arr()->get( $data, 'displayed_values_separator', null ),
				'values_type' => ocme()->arr()->get( $data, 'values_type', 'string' ),
			))->save();
		}
		
		return $attribute;
	}
	
	public function eventAfterEditAttribute( &$args, &$output = null ) {
		/* @var $attribute AttributeModel */
		if( null != ( $attribute = $this->updateAttribute( $args[0], $args[1] ) ) ) {
			AttributeModel::eventSaved( $attribute );
		}
	}
	
	public function eventAfterAddAttribute( &$args, &$output = null ) {
		/* @var $attribute AttributeModel */
		if( null != ( $attribute = $this->updateAttribute( $output, $args[0] ) ) ) {		
			AttributeModel::eventCreated( $attribute );
		}
	}
	
	public function eventBeforeDeleteAttribute( &$args ) {
		/* @var $attribute AttributeModel */
		if( null != ( $attribute = AttributeModel::find( $args[0] ) ) ) {		
			AttributeModel::eventDeleted( $attribute );
		}
	}
	
	public function eventBeforeDeleteAttributeGroup( &$args ) {
		/* @var $attribute_group AttributeGroup */
		if( null != ( $attribute_group = AttributeGroup::find( $args[0] ) ) ) {		
			AttributeGroup::eventDeleted( $attribute_group );
		}
	}
	
	public function eventAfterDeleteProduct( &$args ) {}
	
}