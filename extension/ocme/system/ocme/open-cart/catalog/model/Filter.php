<?php namespace Ocme\OpenCart\Catalog\Model;

use Ocme\Model\Product,
	Ocme\Model\CategoryPath,
	Ocme\Model\ProductToCategory,
	Ocme\Model\OcmeFilterCondition,
	Ocme\Model\OcmeVariable,
	Ocme\Model\Manufacturer,
	Ocme\Model\Category,
	Ocme\Model\ProductFilter,
	Ocme\Model\ProductOptionValue,
	Ocme\Model\ProductAttributeValue,
	Ocme\Model\ProductTag,
	Ocme\Model\AttributeGroup,
	Ocme\ModuleSetting,
	Ocme\Module\Filter\Condition;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class Filter {
	
	use \Ocme\Database\Helper\ApplyPagination;
	
	/**
	 * @var int
	 */
	protected $config_customer_group_id;
	
	/**
	 * @var int
	 */
	protected $config_language_id;
	
	/**
	 * @var int
	 */
	protected $config_store_id;
	
	/**
	 * @var bool
	 */
	protected $config_tax;

	/**
	 * @var string
	 */
	protected $origin_route;
	
	/**
	 * @var array
	 */
	protected static $filter_data = null;
	
	/**
	 * @var array|null
	 */
	protected static $pagination = null;
	
	/**
	 * @var array
	 */
	protected static $attribute_groups = array();
	
	/**
	 * @var array
	 */
	protected static $cache = array();
	
	/**
	 * @var string
	 */
	protected $conditions_list = 'first';
	
	/**
	 * @var bool
	 */
	protected $with_layout = false;
	
	/**
	 * @var array
	 */
	protected static $url_parameters = null;
	
	/**
	 * @var array
	 */
	protected static $url_values = array();
	
	/**
	 * @var array
	 */
	protected static $url_parameters_current = null;
	
	/**
	 * @var array
	 */
	protected static $conditions_params = null;
	
	/**
	 * @var array
	 */
	protected static $SORT_PRODUCTS = array(
		'pd.name' => '`pd`.name',
		'p.model' => '`p`.model',
		'p.quantity' => '`p`.quantity',
		'p.price' => '`p`.price',
		'rating' => 'rating',
		'p.sort_order' => '`p`.sort_order',
		'p.date_added' => '`p`.date_added'
	);

	/**
	 * @var array
	 */
	protected static $SORT_SPECIAL_PRODUCTS = array(
		'pd.name' => '`pd`.name',
		'p.model' => '`p`.model',
		'ps.price' => 'special',
		'rating' => 'rating',
		'p.sort_order' => '`p`.sort_order',
	);
	
	////////////////////////////////////////////////////////////////////////////

	public function __construct() {
		$this->config_customer_group_id = (int) ( ocme()->oc()->registry()->get('customer')->isLogged() ? ocme()->oc()->registry()->get('customer')->getGroupId() : ocme()->oc()->registry()->get('config')->get('config_customer_group_id') );
		$this->config_language_id = (int) ocme()->oc()->registry()->get('config')->get('config_language_id');
		$this->config_store_id = (int) ocme()->oc()->registry()->get('config')->get('config_store_id');
		$this->config_tax = (bool) ocme()->oc()->registry()->get('config')->get('config_tax');
		
		if( is_null( self::$url_parameters ) ) {
			self::$url_parameters = $this->parseUrlParameters();
		}
		
		if( is_null( self::$url_parameters_current ) ) {
			self::$url_parameters_current = $this->parseUrlParameters( 'ocmef_current' );
		}
		
		if( is_null( self::$conditions_params ) ) {
			self::$conditions_params = $this->parseConditionsParams();
		}
	}
	
	public function getUrlParameters() {
		return self::$url_parameters;
	}
	
	public function getConditionsParams() {
		return self::$conditions_params;
	}
	
	public function getUrlValues() {
		return self::$url_values;
	}
	
	/**
	 * @return array
	 */
	protected function parseConditionsParams() {
		/* @var $params array */
		$params = array();
		
		if( null != ( $ocmef_conditions_params = ocme()->request()->input( 'ocmef_conditions_params' ) ) ) {
			/* @var $key string */
			/* @var $values array */
			foreach( $ocmef_conditions_params as $key => $values ) {
				if( ! is_array( $values ) ) continue;
				
				if( strpos( $key, '_' ) !== false ) {
					list( $vtype, $vid ) = explode( '_', $key, 2 );
					
					if( ! in_array( $vtype, array( 'attribute', 'option', 'filter', 'feature' ) ) ) continue;
				}
				
				$params[$key] = $values;
			}
		}
		
		return $params;
	}
	
	protected function urlParameterName() {
		return ocme()->variable()->get( OcmeVariable::TYPE_FILTER_SEO_CONFIG . '.url_parameter_name' );
	}
	
	/**
	 * @param string $url_parameter_name
	 * @return array
	 */
	protected function parseUrlParameters( $url_parameter_name = null ) {
		if( is_null( $url_parameter_name ) ) {
			$url_parameter_name = $this->urlParameterName();
		}
		
		if( is_null( $url_parameter_name ) ) {
			return array();
		}
		
		/* @var $url_parameters array */
		$url_parameters = array();
		
		/* @var $ocmef string */
		if( null != ( $ocmef = ocme()->request()->input( $url_parameter_name ) ) ) {
			/* @var $filters array */
			$filters = array_filter(array_map(function( $v ){
				return preg_replace( '/-$/', '', $v );
			}, explode( 'F-', $ocmef )), function( $v ){
				return $v !== '';
			});
			
			/* @var $filter string */
			foreach( $filters as $filter ) {
				/* @var $url_parameter array */
				$url_parameter = array();
				
				/* @var $key string */
				$key = 'values';
				
				/* @var $values array */
				$values = array();
				
				if( strpos( $filter, '-ORDT' ) !== false ) {
					$values = explode( '-ORDT', $filter );
					$key = 'option_value_datetime_range';
				} else if( strpos( $filter, '-ORD' ) !== false ) {
					$values = explode( '-ORD', $filter );
					$key = 'option_value_date_range';
				} else if( strpos( $filter, '-ORT' ) !== false ) {
					$values = explode( '-ORT', $filter );
					$key = 'option_value_time_range';
				} else if( strpos( $filter, '-ORDT' ) !== false ) {
					$values = explode( '-ORDT', $filter );
					$key = 'option_value_datetime_range';
				} else if( strpos( $filter, '-OV' ) !== false ) {
					$values = explode( '-OV', $filter );
					$key = 'option_value';
				} else if( strpos( $filter, '-OT' ) !== false ) {
					$values = explode( '-OT', $filter );
					$key = 'option_value_text';
				} else if( strpos( $filter, '-AV' ) !== false ) {
					$values = explode( '-AV', $filter );
					$key = 'all_values';
				} else if( strpos( $filter, '-V' ) !== false ) {
					$values = explode( '-V', $filter );
				} else if( strpos( $filter, '-I' ) !== false ) {
					$values = explode( '-I', $filter );
					$key = 'integer_range';
				} else if( strpos( $filter, '-R' ) !== false ) {
					$values = explode( '-R', $filter );
					$key = 'float_range';
				} else if( strpos( $filter, '-T' ) !== false ) {
					$values = explode( '-T', $filter );
					$key = 'text';
				}
				
				$url_parameter = array(
					'name' => array_shift( $values ),
					$key => $values,
				);
				
				if( $url_parameter['name'] ) {
					if( preg_match( '/^(attribute|option|filter|feature)-[0-9]+$/', $url_parameter['name'] ) ) {
						/* @var $parts array */
						$parts = explode( '-', $url_parameter['name'] );

						$url_parameter['id'] = $parts[1];
						$url_parameter['name'] = $parts[0];
					}
					
					$url_parameters[] = $url_parameter;
				}
			}
		}
		
		return $url_parameters;
	}
	
	public function paginationData() {		
		/* @var $page int */
		$page = (int) ocme()->request()->query('page', 1);
		
		/* @var $limit int */
		if( null == ( $limit = (int) ocme()->request()->query('limit', ocme()->oc()->registry()->get('config')->get('theme_' . ocme()->oc()->registry()->get('config')->get('config_theme') . '_product_limit')) ) ) {
			$limit = (int) ocme()->oc()->registry()->get('config')->get('config_pagination');
		}
		
		return array(
			'page' => $page,
			'limit' => $limit,
		);
	}
	
	/**
	 * @param array $url_parameters
	 * @return array
	 */
	public function pagination( array $url_parameters = array() ) {
		if( ! is_null( self::$pagination ) ) {
			return self::$pagination;
		}
		
		/* @var $route string */
		if( null === ( $route = ocme()->request()->query( 'ocmef_route', ocme()->request()->ocQueryRoute() ) ) ) {
			return null;
		}
		
		/* @var $data array */
		$data = $this->paginationData();
		
		/* @var $total int */
		$total = $this->getTotalProducts();
		
		/* @var $page int */
		$page = ocme()->arr()->get( $data, 'page' );
		
		/* @var $limit int */
		$limit = ocme()->arr()->get( $data, 'limit' );

		/* @var $url array */
		$url = array();
		
		/* @var $key string */
		foreach( array_merge( array( 'path', 'filter', 'sort', 'order', 'limit' ), $url_parameters ) as $key ) {
			if( null !== ( $val = ocme()->request()->query( $key ) ) ) {
				$url[] = $key . '=' . $val;
			}
		}
		
		/* @var $pagination_html string */
		$pagination_html = '';
		
		if( version_compare( VERSION, '4', '>=' ) ) {
			$pagination_html = ocme()->oc()->registry()->get('load')->controller('common/pagination', array(
				'total' => $total,
				'page'  => $page,
				'limit' => $limit,
				'url'   => ocme()->oc()->registry()->get('url')->link( $route, implode( '&', $url ) . '&page={page}')
			));
		} else {		
			/* @var $pagination \Pagination */
			$pagination = new \Pagination();
			$pagination->total = $total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = ocme()->oc()->registry()->get('url')->link( $route, implode( '&', $url ) . '&page={page}');
			
			$pagination_html = $pagination->render();
		}
		
		return self::$pagination = array(
			'total' => $total,
			'page' => $page,
			'limit' => $limit,
			'html' => $pagination_html,
			'text' => sprintf(ocme()->trans('module::filter.text_pagination'), ($total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit), $total, ceil($total / $limit)),
		);
	}
	
	/**
	 * @param bool $with_layout
	 * @return $this
	 */
	public function setWithLayout( $with_layout ) {
		$this->with_layout = $with_layout;
		
		return $this;
	}
	
	/**
	 * @param string $conditions_list
	 * @return $this
	 */
	public function setConditionsList( $conditions_list ) {
		$this->conditions_list = $conditions_list;
		
		return $this;
	}
	
	protected function conditionColumnLabelName( Condition $condition, $alias = null, array $options = array() ) {
		/* @var $name string|null */
		$name = null;
		
		switch( $condition->getConfig('condition_type' ) ) {
			case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
				if( in_array( $condition->getConfig('option_type'), array( 'checkbox', 'radio', 'select' ) ) ) {
					$name = '`ovd`.name';
				} else {
					$name = '`po`.value';
				}
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE :
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
				$name = '`avd`.name';
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
				$name = '`fd`.name';
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : {
				$name = '`pv`.' . $condition->getConfig('name');
				
				break;
			}
		}
		
		if( is_null( $name ) ) {
			return $name;
		}
		
		if( ! is_null( $alias ) ) {
			$name .= ' AS ' . $alias;
		}
		
		return $name;
	}
	
	protected function conditionColumnIdName( Condition $condition, $alias = null, array $options = array() ) {
		/* @var $name string|null */
		$name = null;
		
		/* @var $raw bool */
		$raw = false;
		
		switch( $condition->getConfig('condition_type') ) {
			case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
				if( in_array( $condition->getConfig('option_type'), array( 'checkbox', 'radio', 'select' ) ) ) {
					$name = '`pov`.option_value_id';
				} else {
					$name = '`po`.value';
				}
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE :
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
				$name = '`pav`.attribute_value_id';
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
				$name = '`f`.filter_id';
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : {
				$name = '`p`.' . $condition->getConfig('name');
				/*
				switch( $condition->getConfig('name') ) {
					case 'width' :
					case 'height' :
					case 'length' :
					case 'weight' : {
						$name = '`p`.' . $condition->getConfig('name');
						
						break;
					}
					default : {
						$name = '`pv`.' . $condition->getConfig('name');
					}
				}*/
						
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
				switch( $condition->getConfig('name') ) {
					case 'manufacturer' : {
						$name = '`m`.manufacturer_id';
							
						break;
					}
					case 'category'	: {
						$name = '`c`.category_id';
						
						break;
					}
					case 'tags' : {
						$name = '`pt`.product_tag_id';
						
						break;
					}
				}
				
				break;
			}
		}
		
		if( is_null( $name ) ) {
			return $name;
		}
		
		if( ! is_null( $alias ) ) {
			$name .= ' AS ' . $alias;
		}
		
		if( $raw ) {
			return ocme()->db()->raw( $name );
		}
		
		return $name;
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function buildConditionTypeValuesQuery( Condition $condition, $options ) {
		/* @var $vid int */
		$vid = ocme()->arr()->get( $options, 'vid', $condition->getConfig( 'vid', $condition->getConfig('id') ) );
		
		/* @var $type string */
		//$type = ocme()->arr()->get( $options, 'type', $condition->getConfig('type') );
		
		switch( $condition->getConfig('condition_type' ) ) {
			case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
				if( in_array( $condition->getConfig('option_type'), array( 'checkbox', 'radio', 'select' ) ) ) {
					return $this->createProductsQuery()
						->select(array(
							'`pov`.*',
							$this->conditionColumnIdName( $condition, 'id', $options ),
							$this->conditionColumnLabelName( $condition, 'label' ),
							'`ov`.image',
							'`ov`.color',
						))
						->leftJoin('product_option_value AS `pov`', '`pov`.product_id', '=', '`p`.product_id')
						->leftJoin('option_value AS `ov`', '`ov`.option_value_id', '=', '`pov`.option_value_id')
						->leftJoin('option_value_description AS `ovd`', '`ovd`.option_value_id', '=', '`pov`.option_value_id')
						->where('`ovd`.language_id', $this->config_language_id)
						->where('`pov`.option_id', $vid);
				}

				/* @var $query \Illuminate\Database\Eloquent\Builder */
				$query = $this->createProductsQuery()
					->select(array(
						'`po`.*',
						$this->conditionColumnIdName( $condition, 'id', $options ),
						$this->conditionColumnLabelName( $condition, 'label' ),
					))
					->leftJoin('product_option AS `po`', '`po`.product_id', '=', '`p`.product_id')
					->leftJoin('option AS `o`', '`o`.option_id', '=', '`po`.option_id')
					->leftJoin('option_description AS `od`', '`od`.option_id', '=', '`po`.option_id')
					->where('`od`.language_id', $this->config_language_id)
					->where('`po`.option_id', $vid)
					->where('`po`.value', '!=', '');
				
				if( ocme()->arr()->get( $options, 'destination' ) != 'range' ) {
					$query->groupBy('`po`.value');
				}
				
				return $query;
			}
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE :
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
				return $this->createProductsQuery()
					->select(array(
						'`pav`.*',
						$this->conditionColumnIdName( $condition, 'id', $options ),
						$this->conditionColumnLabelName( $condition, 'label' ),
						'`av`.image',
						'`av`.color',
					))
					->leftJoin('product_attribute_value AS `pav`', '`pav`.product_id', '=', '`p`.product_id')
					->leftJoin('attribute_value AS `av`', '`av`.attribute_value_id', '=', '`pav`.attribute_value_id')
					->leftJoin('attribute_value_description AS `avd`', '`avd`.attribute_value_id', '=', '`pav`.attribute_value_id')
					->where('`avd`.language_id', $this->config_language_id)
					->where('`av`.attribute_id', $vid);
			}
			case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
				return $this->createProductsQuery()
					->select(array(
						'`pf`.*',
						$this->conditionColumnIdName( $condition, 'id', $options ),
						$this->conditionColumnLabelName( $condition, 'label' ),
					))
					->leftJoin('product_filter AS `pf`', '`pf`.product_id', '=', '`p`.product_id')
					->leftJoin('filter AS `f`', '`f`.filter_id', '=', '`pf`.filter_id')
					->leftJoin('filter_description AS `fd`', '`fd`.filter_id', '=', '`pf`.filter_id')
					->where('`fd`.language_id', $this->config_language_id)
					->where('`f`.filter_group_id', $vid);
			}
			case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : {
				return Product::query()
					->select(array(
						$this->conditionColumnIdName( $condition, 'id', $options ),
						$this->conditionColumnLabelName( $condition, 'label' ),
					))
					->addFromAlias('`pv`')
					->groupBy('label');
			}
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
				switch( $condition->getConfig('name') ) {
					case 'manufacturer' : return $this->getManufacturerValuesQuery( $condition );
					case 'availability' : return $this->getAvailabilityValuesQuery( $condition );
					case 'category'	: return $this->getCategoryValuesQuery( $condition );
				}
				
				break;
			}
		}
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getCategoryValuesQuery( Condition $condition ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = Category::query()
			->select(array(
				ocme()->db()->raw('MAX(`c`.`category_id`) AS `id`'),
				ocme()->db()->raw('MAX(`cd`.`name`) AS `label`'),
				ocme()->db()->raw('MAX(`c`.`image`) AS `image`'),
			))
			->addFromAlias('`c`')
			->leftJoin('category_description AS `cd`', '`cd`.category_id', '=', '`c`.category_id')
			->leftJoin('category_to_store AS `c2s`', '`c2s`.category_id', '=', '`c`.category_id')
			->leftJoin('product_to_category AS `p2c`', '`p2c`.category_id', '=', '`c`.category_id')
			->leftJoin('product AS `p`', '`p`.product_id', '=', '`p2c`.product_id')
			->where('`cd`.language_id', $this->config_language_id)
			->where('`c2s`.store_id', $this->config_store_id)
			->where('`c`.status', '1')
			->groupBy('`c`.category_id');
		
		/* @var $filter_category_parent_id int */
		if( ! empty( $filter_category_id = ocme()->arr()->get( self::$filter_data, 'filter_category_id' ) ) ) {
			$query->where('`c`.parent_id', (int)$filter_category_id);
		}
		
		return $query;
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getAvailabilityValuesQuery( Condition $condition ) {
		return Product::select(array(
				ocme()->db()->raw('IF( `p`.`quantity` > 0, "in_stock", "out_of_stock") AS `label`'),
			))
			->addFromAlias('`p`')
			->groupBy( 'label' );
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getManufacturerValuesQuery( Condition $condition ) {
		return Manufacturer::query()
			->select(array(
				ocme()->db()->raw('MAX(`m`.`manufacturer_id`) AS `id`'),
				ocme()->db()->raw('MAX(`m`.`name`) AS `label`'),
				ocme()->db()->raw('MAX(`m`.`image`) AS `image`'),
			))
			->addFromAlias('`m`')
			->leftJoin('manufacturer_to_store AS `m2s`', '`m`.manufacturer_id', '=', '`m2s`.manufacturer_id')
			->leftJoin('product AS `p`', '`p`.manufacturer_id', '=', '`m`.manufacturer_id')
			->leftJoin('product_to_store AS `p2s`', '`p`.product_id', '=', '`p2s`.product_id')
			->where('`m2s`.store_id', $this->config_store_id)
			->where('`p2s`.store_id', $this->config_store_id);
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getSearchValuesQuery( Condition $condition ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->createProductsQuery()
			->select(array(
				ocme()->db()->raw('MAX(`p`.`product_id`) AS `id`'),
				ocme()->db()->raw('MAX(`pd`.`name`) AS `label`'),
				ocme()->db()->raw('MAX(`p`.`image`) AS `image`'),
			))
			->groupBy('`p`.product_id');
		
		return $query;
	}
	
	/**
	 * @param ModuleSetting $setting
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function getConditionsQuery( ModuleSetting $setting ) {
		return OcmeFilterCondition::where('module_id', $setting->get('module_id'))
			->orderBy(ocme()->db()->raw('IF(`sort_order` IS NULL, 0, `sort_order`)'))
			->with(array(
				'attribute' => function($q){
					$q->withDescription();
				}, 
				'attribute_group' => function($q){
					$q->withDescription();
				}, 
				'filter_group' => function($q){
					$q->withDescription();
				}, 
				'option' => function($q){
					$q->withDescription();
				},
			));
	}
	
	/**
	 * @param string $condition_type
	 * @return string|null
	 */
	public function conditionKey( $condition_type ) {
		if( $condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP ) {
			return OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE;
		}
		
		if( in_array( $condition_type, array(
				OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE,
				OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
				OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
				OcmeFilterCondition::CONDITION_TYPE_OPTION,
				OcmeFilterCondition::CONDITION_TYPE_PROPERTY,
				OcmeFilterCondition::CONDITION_TYPE_FEATURE
			))
		) {
			return $condition_type;
		}
		
		return null;
	}
	
	protected function conditionGlobalKey( $condition_type ) {
		/* @var $condition_key string */
		if( null != ( $condition_key = $this->conditionKey( $condition_type ) ) ) {
			if( $condition_type == OcmeFilterCondition::CONDITION_TYPE_PROPERTY ) {
				$condition_key = 'properties';
			} else {
				$condition_key .= 's';
			}
			
			return 'conditions.' . $condition_key . '.global';
		}
		
		return null;
	}
	
	protected function _prepareConditionsList( \Illuminate\Database\Eloquent\Collection $conditions, ModuleSetting $setting ) {
		/* @var $attribute_group_ids array */
		$attribute_group_ids = array();
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		foreach( $conditions as $ocme_filter_condition ) {
			switch( $ocme_filter_condition->condition_type ) {
				/*case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
					$attribute_group_ids[] = $ocme_filter_condition->record_id;
					
					break;
				}*/
				case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
					self::$attribute_groups[$ocme_filter_condition->record_id] = $this->createCondition($setting, $ocme_filter_condition);
					
					break;
				}
			}
		}
		
		if( $attribute_group_ids ) {
			$attribute_group_ids = array_unique( $attribute_group_ids );
			$attribute_group_ids = array_diff( $attribute_group_ids, array_keys( self::$attribute_groups ) );
			
			if( $attribute_group_ids ) {
				foreach( $this->getConditionsQuery( $setting )
					->where('condition_type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP)
					->whereIn('record_id', $attribute_group_ids)
					->get() as $ocme_filter_condition 
				) {
					self::$attribute_groups[$ocme_filter_condition->record_id] = $this->createCondition($setting, $ocme_filter_condition);
				}
			}
		}
		
		return $conditions;
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Collection | OcmeFilterCondition[]
	 */
	public function prepareConditions( ModuleSetting $setting ) {
		/* @var $conditions \Illuminate\Database\Eloquent\Collection */
		$conditions = ocme()->cache()->db( $this->getConditionsQuery( $setting )
			/*->whereNotIn('condition_type', array(
				OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
				OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP,
				OcmeFilterCondition::CONDITION_TYPE_OPTION,
				OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
			))*/
			->whereIn('status', OcmeFilterCondition::getStatuses()), function( $query ){
				return $query->get();
			});
		
		return $this->_prepareConditionsList( $conditions, $setting, 'include' );
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Collection | OcmeFilterCondition[]
	 */
	public function prepareExcludeConditions( ModuleSetting $setting ) {		
		/* @var $conditions \Illuminate\Database\Eloquent\Collection */
		$conditions = ocme()->cache()->db( $this->getConditionsQuery( $setting )->whereIn('condition_type', array(
			OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
			OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP,
			OcmeFilterCondition::CONDITION_TYPE_OPTION,
			OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
		))->whereNotIn('status', OcmeFilterCondition::getStatuses()), function( $query ){
			return $query->get();
		});
		
		return $this->_prepareConditionsList( $conditions, $setting, 'exclude' );
	}
	
	private function createMultilevelOrderBy( $order_by ) {
		/* @var $orders array */
		$orders = array();
		
		foreach( $order_by as $order ) {
			$orders[] = 'IF( ' . $order . ' IS NOT NULL, ' . $order . ', ';
		}
		
		$orders[] = '0';
		
		return ocme()->db()->raw( implode( ' ', $orders ) . str_repeat( ' )', count( $orders ) - 1 ) );
	}
	
	/**
	 * @param ModuleSetting $setting
	 * @param array $options
	 * @return \Illuminate\Database\Eloquent\Builder|null
	 */
	public function buildConditionsQuery( ModuleSetting $setting, array $options = array() ) {
		/* @var $queries array */
		$queries = array();
		
		/* @var $include array */
		$include = array();
		
		/* @var $exclude array */
		$exclude = array();
		
		/* @var $without_includes bool */
		$without_includes = false;
		
		if( ocme()->arr()->has( $options, 'include' ) ) {
			$include = ocme()->arr()->get( $options, 'include' );
			$without_includes = true;
		} else {
			/* @var $ocme_filter_condition OcmeFilterCondition */
			foreach( $this->prepareConditions( $setting ) as $ocme_filter_condition ) {
				if( ! is_null( $ocme_filter_condition->record_id ) ) {
					$include[$ocme_filter_condition->condition_type][] = $ocme_filter_condition->record_id;
				}
			}
		}
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		foreach( $this->prepareExcludeConditions( $setting ) as $ocme_filter_condition ) {
			if( ! is_null( $ocme_filter_condition->record_id ) ) {
				$exclude[$ocme_filter_condition->condition_type][] = $ocme_filter_condition->record_id;
			}
		}
		
		/* @var $enabled array */
		$enabled = array();
		
		/* @var $condition_type string */
		foreach( array( 
			OcmeFilterCondition::CONDITION_TYPE_OPTION,
			OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
			OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
			OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP,
		) as $condition_type ) {
			if( ocme()->arr()->get( $options, 'conditions.vtype' ) ) {
				if( ocme()->arr()->get( $options, 'conditions.vtype' ) != $condition_type ) {
					continue;
				}
			}
			
			if( 
				$setting->get($this->conditionGlobalKey( $condition_type ) . '.status') 
					|| 
				( $condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP && ! empty( $include[$condition_type] ) )
					||
				$condition_type == ocme()->arr()->get( $options, 'conditions.vtype' )
			) {
				/* @var $temp_queries array */
				$temp_queries = array();
				
				switch( $condition_type ) {
					case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE :
					case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
						if( ! isset( $queries[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE] ) ) {
							$temp_queries[$condition_type] = ProductAttributeValue::query()
								->select(array(
									'product_id',
									'attribute_id AS id',
								));
						}
						
						break;
					}
					case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
						$temp_queries[$condition_type.'_value'] = ProductOptionValue::query()
							->select(array(
								'product_id',
								'option_id AS id',
							));
						
						$temp_queries[$condition_type] = \Ocme\Model\ProductOption::query()
							->select(array(
								'product_id',
								'option_id AS id',
							));
						
						break;
					}
					case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
						$temp_queries[$condition_type] = ProductFilter::query()
							->select(array(
								'product_id',
								'filter_group_id AS id',
							))
							->join('filter', 'filter.filter_id', '=', 'product_filter.filter_id');
						
						break;
					}
				}
				
				foreach( $temp_queries as $temp_query_key => & $temp_query ) {
					if( $without_includes && $condition_type != OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP && ! empty( $include[$condition_type] ) ) {
						$temp_query
							->whereNotIn($condition_type . '_id', $include[$condition_type]);
					}

					//if( isset( $queries[$condition_type] ) ) {						
						$temp_query
							->addSelect( ocme()->db()->raw('"' . ( $condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP ? OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : $condition_type ) . '" AS `type`') );
					//}
					
					$queries[$temp_query_key] = $temp_query;
				}
			}
			
			if( $setting->get($this->conditionGlobalKey( $condition_type ) . '.status') ) {
				$enabled[] = $condition_type;
			}
		}
		
		if( ! $queries ) {
			return null;
		}
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = array_shift( $queries );

		while( $queries ) {
			$query->union( array_shift( $queries )->getQuery() );
		}

		/* @var $order_by array */
		$order_by = array( '`ofc`.`sort_order`' );
		
		/* @var $keys array */
		$keys = array(
			'id', 'module_id', 'condition_type', 'name', 'record_id', 'status', 'type', 'sort_order', 'setting',
		);

		$query = ocme()->db()->newQuery()
			->select(array_merge(
				array_map(function($v){
					return '`ofc`.' . $v . ' AS `ofc:' . $v . '`';
				}, $keys),
				array(
					'`v`.id AS vid',
					'`v`.type AS vtype',
				)
			))
			->from( ocme()->db()->raw( '(' . ocme()->db()->queryToRawSql($query) . ') AS `v`' ) )
			->whereExists(function($q) {
				/* @var $builder \Illuminate\Database\Query\Builder */
				$builder = $this->createProductsQuery()->getQuery();				

				$this->applyOpenCartFilters( $builder );
				
				if( ! ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_conditions' ) ) {
				//	$this->applyConditions( $builder );
				}

				$q->columns = array( ocme()->db()->raw(1) );
				$q->from = $builder->from;
				$q->joins = $builder->joins;
				$q->wheres = $builder->wheres;
				$q->groups = $builder->groups;
				$q->havings = $builder->havings;
				$q->mergeBindings( $builder );

				$q->whereColumn('`p`.product_id', '`v`.product_id');
			})
			->leftJoin('ocme_filter_condition AS `ofc`', function($q) use( $setting ){
				$q->on('`ofc`.record_id', '=', '`v`.id')->on('`ofc`.condition_type', '=', '`v`.type')->where('`ofc`.module_id', '=', $setting->get('module_id'));
			})
			->groupBy(ocme()->db()->raw("CONCAT(`v`.`id`,':',`v`.`type`)"));
			
		foreach( ocme()->arr()->get( $options, 'conditions', array() ) as $key => $value ) {
			switch( $key ) {
				case 'vid' : $query->where('`v`.id', $value); break;
				case 'vtype' : $query->where('`v`.type', $value); break;
				case 'record_id' : $query->where('`ofc`.record_id', $value); break;
				case 'condition_type' : $query->where('`ofc`.condition_type', $value); break;
			}
		}

		if( 
			in_array( OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE, $enabled ) 
				|| 
			in_array( OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP, $enabled ) 
				|| 
			! empty( $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE] ) 
				|| 
			! empty( $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP] ) 
				|| 
			! empty( $exclude[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE] ) 
				|| 
			! empty( $exclude[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP] ) 
		) {
			$query
				->addSelect(
					array_map(function($v){
						return '`ofcg`.' . $v . ' AS `ofcg:' . $v . '`';
					}, $keys)
				)
				->leftJoin('attribute AS `a`', function($q){
					$q->on('`a`.attribute_id', '=', '`v`.id')->where('`v`.type', '=', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE);
				})
				->leftJoin('ocme_filter_condition AS `ofcg`', function($q) use( $setting ){
					$q->on('`ofcg`.record_id', '=', '`a`.attribute_group_id')->where('`ofcg`.condition_type', '=', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP)->where('`ofcg`.module_id', '=', $setting->get('module_id'));
				})
				->addSelect('`a`.values_type')
				->where(function($q) use( $enabled ){
					$q->whereNotNull('`ofc`.id')->orWhereNotNull('`ofcg`.id');
					
					if( $enabled ) {
						$q->orWhereIn('`v`.type', $enabled);
					}
				});

			$order_by[] = '`a`.`sort_order`';
		} else {
			//$query->whereNotNull('`ofc`.id');
		}
		
		if( 
			in_array( OcmeFilterCondition::CONDITION_TYPE_OPTION, $enabled ) 
				|| 
			! empty( $include[OcmeFilterCondition::CONDITION_TYPE_OPTION] ) 
				|| 
			! empty( $exclude[OcmeFilterCondition::CONDITION_TYPE_OPTION] ) 
		) {
			$query
				->leftJoin('option AS `o`', function($q){
					$q->on('`o`.option_id', '=', '`v`.id')->where('`v`.type', '=', OcmeFilterCondition::CONDITION_TYPE_OPTION);
				})
				->addSelect('`o`.values_type');

			$order_by[] = '`o`.`sort_order`';
		}

		if( 
			in_array( OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP, $enabled ) 
				|| 
			! empty( $include[OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP] ) 
				|| 
			! empty( $exclude[OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP] ) 
		) {
			$query
				->leftJoin('filter_group AS `fg`', function($q){
					$q->on('`fg`.filter_group_id', '=', '`v`.id')->where('`v`.type', '=', OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP);
				})
				->addSelect('`fg`.values_type');

			$order_by[] = '`fg`.`sort_order`';
		}

		$query->orderBy( $this->createMultilevelOrderBy( $order_by ) );
		
		if( $exclude || $include || $enabled ) {
			$query->where(function($q) use( $exclude, $include, $enabled ){
				foreach( $exclude as $condition_type => $ids ) {
					switch( $condition_type ) {
						case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
							$q->orWhere(function($q) use( $ids ){
								$q->whereNull('`a`.attribute_id')->orWhereNotIn('`a`.attribute_id', $ids);
							});

							break;
						}
						case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
							if( ! empty( $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE] ) ) {
								$q->orWhere(function($q) use( $ids, $include ) {
									$q
										->whereNull('`a`.attribute_id')
										->orWhereNotIn('`a`.attribute_group_id', $ids)
										->orWhereIn('`a`.attribute_id', $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE]);
								});
							} else {
								$q->orWhere(function($q) use( $condition_type, $ids ){
									$q->where('`ofc`.condition_type', $condition_type)->whereNotIn('`a`.attribute_group_id', $ids);
								});
							}

							break;
						}
						case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
							$q->orWhere(function($q) use( $condition_type, $ids ){
								$q->where('`ofc`.condition_type', $condition_type)->whereNotIn('`o`.option_id', $ids);
							});

							break;
						}
						case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
							$q->orWhere(function($q) use( $condition_type, $ids ) {
								$q->where('`ofc`.condition_type', $condition_type)->whereNotIn('`fg`.filter_group_id', $ids);
							});

							break;
						}
					}
				}
		
				if( $include || $enabled ) {
					$q->orWhere(function($q) use( $include, $enabled ){
						if( $enabled ) {
							$q->whereIn('`v`.type', ocme()->collection()->make( $enabled )->reject( OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP )->all() );
						}
						
						foreach( array( 
							OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE, 
							OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP, 
							OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP, 
							OcmeFilterCondition::CONDITION_TYPE_OPTION 
						) as $condition_type ) {
							if( ! in_array( $condition_type, $enabled ) && ! empty( $include[$condition_type] ) ) {
								$q->orWhere(function($q) use( $condition_type, $include ){
									/* @var $columns array */
									$columns = array(
										OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE => '`a`.attribute_id',
										OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP => '`a`.attribute_group_id',
										OcmeFilterCondition::CONDITION_TYPE_OPTION => '`o`.option_id',
										OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP => '`o`.option_id',
									);
									
									$q
										->where(function($q) use( $columns, $condition_type, $include ){
											$q
												->whereNull( $columns[$condition_type] )
												->orWhereIn( $columns[$condition_type], $include[$condition_type] );
										});
									
									switch( $condition_type ) {
										case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
											if( isset( $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE] ) ) {
												$q->where(function($q) use( $include ){
													$q->whereNull('`a`.attribute_id')->orWhereNotIn('`a`.attribute_id', $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE]);
												});
											}
											
											break;
										}
										case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
											if( isset( $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP] ) ) {
												$q->whereNotIn('`a`.attribute_group_id', $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP]);
											}
											
											break;
										}
									}
								});
							}
						}
					});
				}
			});
			
			if( isset( $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE] ) && ocme()->arr()->get( $options, 'conditions.vtype' ) != OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE ) {
				$query->where(function($q) use( $include ){
					$q
						->whereNull('`a`.attribute_id')
						->orWhere(function($q) use( $include ){
							$q->where('`v`.type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE)->whereNotIn('`a`.attribute_id', $include[OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE]);
						});
				});
			}
		}
		
		return $query;
	}
	
	protected function fillConditionData( Condition $condition ) {
		if( $this->skipCondition( $condition ) ) {
			return $condition;
		}
		
		if( $condition->isValuable() ) {
			/* @var $where_conditions array */
			$where_conditions = array();
			
			/* @var $extra_params array */
			if( null != ( $extra_params = ocme()->arr()->get( self::$filter_data, 'ocmef_conditions_params.' . $condition->paramKeyName() ) ) ) {
				/* @var $phrase string */
				if( '' != ( $phrase = ocme()->arr()->get( $extra_params, 'phrase' ) ) ) {
					$where_conditions['phrase'] = $phrase;
				}
			}
			
			/* @var $values mixed */
			if( null !== ( $values = $this->getConditionValues( $condition, array(), $where_conditions ) ) && ocme()->arr()->get( $values, 'items' ) ) {
				$condition->setData( 'values', $values );
			}
		} else if( $condition->isRange() ) {
			/* @var $range mixed */
			if( null !== ( $range = $this->getConditionRange( $condition ) ) ) {
				$condition->setData( 'range', $range );
			}
		} else 
		/* @var $url_parameter array|null */
		if( null != ( $url_parameter = $this->urlParameter( $condition ) ) ) {
			/* @var $texts array */
			if( null != ( $texts = ocme()->arr()->get( $url_parameter, 'text' ) ) ) {
				self::$url_values[$condition->key()] = array(
					'title' => $condition->getConfig('setting.title'),
					'values' => array_map(function( $text ){
						return array( 'id' => $text, 'label' => $text );
					}, $texts),
				);
			}
		}
		
		return $condition;
	}
	
	public function fillConditionsData( array $conditions ) {
		return array_map(function( $condition ){
			return $this->fillConditionData( $condition );
		}, $conditions);
	}
	
	public function getConditions( \Ocme\Module\Filter $filter, ModuleSetting $setting ) {
		/* @var $conditions array */
		$conditions = array();
		
		/* @var $include array */
		$include = array();
		
		/* @var $max_conditions int */
		$max_conditions = $this->with_layout ? 100 : null;//(int) $setting->get( 'configuration.max_conditions', 10 );
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		foreach( $this->prepareConditions( $setting ) as $ocme_filter_condition ) {
			if( $ocme_filter_condition->condition_type != OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP ) {
				/* @var $condition array */
				if( null != ( $condition = $this->createCondition( $setting, $ocme_filter_condition ) ) ) {
					$conditions[] = new Condition( $filter, $setting, $condition );
				}
			}
			
			if( ! is_null( $ocme_filter_condition->record_id ) ) {
				$include[$ocme_filter_condition->condition_type][] = $ocme_filter_condition->record_id;
			}
		}
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		if( null == ( $query = $this->buildConditionsQuery( $setting, compact( 'include' ) ) ) ) {
			return $this->convertConditionsToGroups( $setting, $this->fillConditionsData( $conditions ) );
		}
		
		if( $max_conditions ) {
			$query->limit( $max_conditions );
		}
		
		/* @var $condition Condition */
		foreach( $this->setUpConditions( $filter, $setting, $query ) as $condition ) {
			$conditions[] = $condition;
		}
		
		return $this->convertConditionsToGroups( $setting, $this->fillConditionsData( $conditions ) );
	}
	
	public function setUpConditions( \Ocme\Module\Filter $filter, ModuleSetting $setting, $query ) {
		/* @var $conditions array */
		$conditions = array();
		
		/* @var $relations array */
		$relations = array();
		
		/* @var $rows array */
		$rows = ocme()->cache()->db( $query, function( $query ) { return $query->get(); } );
		
		/* @var $row array */
		foreach( $rows as $row ) {
			$relations[ocme()->arr()->get( $row, 'vtype' )][] = ocme()->arr()->get( $row, 'vid' );
		}
		
		$relations = ocme()->arr()->build( $relations, function( $key, $ids ){
			/* @var $class string */
			$class = '\\Ocme\\Model\\' . ocme()->str()->studly( $key );
			
			/* @var $builder \Illuminate\Database\Eloquent\Builder */
			$builder = call_user_func( array( $class, 'query' ) );
			
			/* @var $key_name string */
			$key_name = $builder->getModel()->getKeyName();
			
			return array( $key, $builder->whereIn($key . '.' . $key_name, $ids)->withDescription()->get()->keyBy( $key_name ) );
		});
		
		/* @var $row */
		foreach( $rows as $row ) {
			/* @var $key string|null */
			$key = null;

			if( ocme()->arr()->get( $row, 'ofc:id' ) ) {
				$key = 'ofc';
			} else if( ocme()->arr()->get( $row, 'ofcg:id' ) ) {
				$key = 'ofcg';
			}

			/* @var $params null|array */
			$params = null;

			/* @var $vtype string */
			$vtype = ocme()->arr()->get( $row, 'vtype' );

			/* @var $item_id int */
			$vid = ocme()->arr()->get( $row, 'vid' );
			
			if( $key ) {
				/* @var $attributes array */
				$attributes = ocme()->arr()->build( ocme()->arr()->where( $row, function( $v, $k ) use( $key ) {
					return ocme()->str()->startsWith( $k, $key . ':' );
				}), function( $k, $v ) use( $key ){
					return array( str_replace( $key . ':', '', $k ), $v );
				});

				/* @var $ocme_filter_condition OcmeFilterCondition */
				$ocme_filter_condition = new OcmeFilterCondition( $attributes );

				$ocme_filter_condition->exists = true;
				$ocme_filter_condition->setRelation( $vtype, $relations[$vtype][$vid] );

				$params = $this->createCondition( $setting, $ocme_filter_condition, array(
					'vtype' => $vtype,
					'vid' => $vid,
				));
			} else {				
				$params = $this->makeCondition( $setting, $vtype, $vid, array(
					'relations' => array(
						$vtype => $relations[$vtype][$vid],
					),
				));
				
				$params['values_type'] = ocme()->arr()->get( $row, 'values_type' );
			}
			
			if( $params ) {
				$conditions[] = new Condition( $filter, $setting, $params );
			}
		}
		
		return $conditions;
	}
	
	public function makeCondition( ModuleSetting $setting, $vtype, $vid, array $options = array() ) {
		/* @var $default_condition array */
		$default_condition = ocme()->model('filter')->createGeneralCondition( $setting, $vtype );
					
		/* @var $ocme_filter_condition OcmeFilterCondition */
		$ocme_filter_condition = new OcmeFilterCondition( $default_condition + array(
			'record_id' => $vid,
		));
		
		$ocme_filter_condition->exists = true;
		
		/* @var $relations array */
		if( null != ( $relations = ocme()->arr()->get( $options, 'relations' ) ) ) {
			$ocme_filter_condition->setRelations( $relations );
		}

		return ocme()->model('filter')->createCondition( $setting, $ocme_filter_condition, array(
			'vtype' => $vtype,
			'vid' => $vid,
		));
	}
	
	protected function skipCondition( Condition $condition ) {
		if( empty( self::$filter_data['ocmef_conditions'] ) || ! is_array( self::$filter_data['ocmef_conditions'] ) ) {
			return false;
		}
		
		return isset( self::$filter_data['ocmef_conditions'][$condition->key()] );
	}
	
	protected function convertConditionsToGroups( $setting, array $conditions ) {
		/* @var $groups array */
		$groups = array();
		
		/* @var $max_conditions int */
		$max_conditions = $this->with_layout ? 100 : (int) $setting->get( 'configuration.max_conditions', 10 );
		
		/* @var $total int */
		$total = 0;
		
		/* @var $last_key string|null */
		$last_key = null;
		
		/* @var $condition Condition */
		foreach( $conditions as $condition ) {
			// check if condition has required data
			if(
				$this->skipCondition( $condition )
					||
				( $condition->isValuable() && ! $condition->getData( 'values' ) )
					||
				( $condition->isRange() && ! $condition->getData( 'range' ) )
			) {
				continue;
			}
			
			/* @var $key string|null */
			$key = null;
			
			/* @var $display_in_groups bool */
			if( null === ( $display_in_groups = $condition->getConfig('setting.display_in_groups') ) ) {
				if( isset( self::$attribute_groups[$condition->getConfig('condition_group_id')] ) ) {
					$display_in_groups = ocme()->arr()->get( self::$attribute_groups[$condition->getConfig('condition_group_id')], 'setting.display_in_groups' );
				}
				
				if( is_null( $display_in_groups ) ) {
					$display_in_groups = $setting->get( 'conditions.' . $condition->globalKeyName() . '.global.display_in_groups' );
				}
			}
			
			if( ! $this->with_layout && $display_in_groups ) {
				$key = $condition->getConfig('vtype') . '.' . $condition->getConfig('condition_group_id');
			}
			
			if( ! isset( $groups[$key] ) ) {
				switch( $condition->getConfig('vtype') ) {
					case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
						$groups[$key] = array(
							'data' => self::$attribute_groups[$condition->getConfig('condition_group_id')],
						);
						
						if( empty( $groups[$key]['data'] ) ) {
							$groups[$key]['data'] = array(
								'setting' => array(
									'title' => AttributeGroup::withDescription()->find( $condition->getConfig('condition_group_id') )->name,
								),
								'sort_order' => $condition->getConfig('sort_order'),
							);
						}
						
						break;
					}
					default : {
						$groups[$key] = array(
							'data' => array(),
						);
					}
				}
				
				$groups[$key]['sort_order'] = min( array( ocme()->arr()->get( $groups[$key], 'data.sort_order', 0 ), $condition->getConfig('sort_order') ) );
			} else {
				$groups[$key]['sort_order'] = min( array( ocme()->arr()->get( $groups[$key], 'sort_order' ), $condition->getConfig('sort_order') ) );
			}
			
			if( $last_key != $key || ( ! ( $this->conditions_list == 'first' && $total >= $max_conditions ) ) ) {
				$groups[$key]['conditions'][] = $condition;
			}
			
			$total++;
			$last_key = $key;
		}
		
		uasort( $groups, function( $a, $b ){
			return ocme()->arr()->get( $a, 'sort_order' ) - ocme()->arr()->get( $b, 'sort_order' );
		});
		
		return array(
			'items' => $groups,
			'left' => $this->with_layout ? 0 : ( $total > $max_conditions ? $total - $max_conditions : 0 ),
		);
	}
	
	public function getConditionValuesQuery( Condition $condition, $options = array() ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = null;
		
		if( ! $condition->isValuable() && ! $condition->withAutocomplete() ) {
			return null;
		}
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = null;
		
		switch( $condition->getConfig('vtype') ) {
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
				switch( $condition->getConfig('name') ) {
					case 'manufacturer' : {
						$query = $this->getManufacturerValuesQuery( $condition );
						
						break;
					}
					case 'category' : {
						$query = $this->getCategoryValuesQuery( $condition );
						
						break;
					}
					case 'tags' : {
						$query = $this->getTagValuesQuery( $condition );
						
						break;
					}
					case 'availability' : {
						$query = $this->getAvailabilityValuesQuery( $condition );
						
						break;
					}
					case 'search' : {
						$query = $this->getSearchValuesQuery( $condition );
						
						break;
					}
				}
				
				if( ! is_null( $query ) ) {
					if( in_array( $condition->getConfig('name'), array( 'manufacturer', 'tags', 'availability' ) ) ) {
						$this->addRelations( $query, $condition->getConfig('name') );
					}
				}
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : {
				$query = $this->getPropertyValuesQuery( $condition );
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE :
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP :
			case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP :
			case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
				$query = $this->buildConditionTypeValuesQuery( $condition, $options );
				
				break;
			}
		}
		
		return $query;
	}
	
	public function getConditionValues( Condition $condition, $options = array(), array $where_conditions = array() ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->getConditionValuesQuery( $condition, $options );
		
		if( ! is_null( $query ) ) {
			return $this->prepareValues( $condition, $query, $where_conditions, $options );
		}
		
		return null;
	}
	
	public function getConditionRange( Condition $condition ) {
		switch( $condition->getConfig( 'vtype' ) ) {
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
				switch( $condition->getConfig( 'name' ) ) {
					case 'price' : return $this->getPriceRange( $condition );
				}
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : {
				return $this->getPropertyRange( $condition );
			}
			case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP :
			case OcmeFilterCondition::CONDITION_TYPE_OPTION :
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
				return $this->getRange( $condition );
			}
		}
	}
	
	public function createGeneralCondition( $setting, $condition_type, array $except = array() ) {
		/* @var $condition_global_key string */
		if( null == ( $condition_global_key = $this->conditionGlobalKey( $condition_type ) ) ) {
			return null;
		}
		
		/* @var $params array */
		$params = $setting->get( $condition_global_key );
		
		/* @var $keys array */
		//$keys = array( 'type', 'status', 'sort_order' );
		
		return /*ocme()->arr()->only( $params, $keys ) + */array(
			'setting' => ocme()->arr()->except( $params, $except ),
			'condition_type' => $condition_type,
		);
	}
	
	protected function attributeGroup( ModuleSetting $setting, $attribute_group_id, $type = 'include' ) {
		if( isset( self::$attribute_groups[$attribute_group_id] ) ) {
			if( self::$attribute_groups[$attribute_group_id] ) {
				return self::$attribute_groups[$attribute_group_id];
			}
			
			return null;
		}
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		$ocme_filter_condition = $this->getConditionsQuery( $setting )
			->where('condition_type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP)
			->where('record_id', $attribute_group_id)
			->first();
		
		if( $ocme_filter_condition ) {
			self::$attribute_groups[$attribute_group_id] = $this->createCondition( $setting, $ocme_filter_condition );
		} else {
			self::$attribute_groups[$attribute_group_id] = false;
		}
		
		if( self::$attribute_groups[$attribute_group_id] ) {
			return self::$attribute_groups[$attribute_group_id];
		}
		
		return null;
	}
	
	/**
	 * @param ModuleSetting $setting
	 * @param OcmeFilterCondition $ocme_filter_condition
	 * @param bool $first
	 * @return array
	 */
	public function createCondition( ModuleSetting $setting, OcmeFilterCondition $ocme_filter_condition, array $params = array() ) {
		/* @var $condition array */
		$condition = null;
		
		if( $ocme_filter_condition->condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP ) {
			if( isset( self::$attribute_groups[$ocme_filter_condition->record_id] ) ) {
				$condition = $this->attributeGroup($setting, $ocme_filter_condition->record_id);
			}
		}
		
		if( ! $condition ) {
			$condition = $this->createGeneralCondition( $setting, $ocme_filter_condition->condition_type, array( 'type', 'status', 'sort_order' ) );
		}
		
		if( $params ) {
			$condition = array_replace( $condition, $params );
		}
		
		switch( $ocme_filter_condition->condition_type ) {
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
				switch( $ocme_filter_condition->name ) {
					case 'price' : {
						ocme()->arr()->set( $condition, 'component', 'slider' );
						
						break;
					}
					case 'search' : {
						ocme()->arr()->set( $condition, 'type', 'search' );
						ocme()->arr()->set( $condition, 'component', $ocme_filter_condition->name );
						
						break;
					}
				}
				
				break;
			}
		}
		
		/* @var $inherit_namespaces array */
		$inherit_namespaces = array( 'global.conditions', 'global.conditions.setting' );
		
		/* @var $trans array */
		$trans = array();
		
		if( ! is_null( $ocme_filter_condition->name ) ) {
			ocme()->arr()->set( $condition, 'name', $ocme_filter_condition->name );
			
			array_unshift( $inherit_namespaces, 'global.conditions.' . $ocme_filter_condition->name );
				
			if( ocme()->arr()->has( OcmeVariable::getFilterGlobal(), 'trans.conditions.' . $ocme_filter_condition->condition_type . '.' . $ocme_filter_condition->name ) ) {
				$trans = ocme()->arr()->get( OcmeVariable::getFilterGlobal(), 'trans.conditions.' . $ocme_filter_condition->condition_type . '.' . $ocme_filter_condition->name );
			}
		}
		
		array_unshift( $inherit_namespaces, 'conditions.global' );
		array_unshift( $inherit_namespaces, $this->conditionGlobalKey( $ocme_filter_condition->condition_type ) );
		
		if( $ocme_filter_condition->condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE ) {
			array_unshift( $inherit_namespaces, 'conditions.attributes.groups.' . $ocme_filter_condition->attribute->attribute_group_id );
		}
		
		foreach( array( 'id' => 'condition_id', 'type', 'status', 'setting', 'record_id' => 'id' ) as $src_key => $key ) {
			if( is_int( $src_key ) ) {
				$src_key = $key;
			}
			
			if( ! is_null( $ocme_filter_condition->{$src_key} ) ) {
				ocme()->arr()->set( $condition, $key, $ocme_filter_condition->{$src_key} );
			}
		}
		
		ocme()->arr()->set( $condition, 'sort_order', (int) $ocme_filter_condition->sort_order );
		
		foreach( $inherit_namespaces as $namespace ) {
			/* @var $default_values null|array */
			$default_values = null;
			
			/* @var $global bool */
			$global = false;
			
			if( ocme()->str()->startsWith( $namespace, 'global.' ) ) {
				$namespace = substr( $namespace, strpos( $namespace, '.' ) + 1 );
				
				$default_values = ocme()->arr()->get( OcmeVariable::getFilterGlobal(), $namespace );
				
				$global = true;
			} else if( ocme()->str()->contains( $namespace, 'conditions.attributes.groups.' ) ) {
				$default_values = $this->attributeGroup( $setting, $ocme_filter_condition->attribute->attribute_group_id );
			} else {
				$default_values = ocme()->arr()->get( $setting->getSettings(), $namespace );
			}
			
			if( ! is_null( $default_values ) ) {
				foreach( array( 'type', 'status', 'sort_order' ) as $key ) {
					if( isset( $default_values[$key] ) ) {
						if( ! isset( $condition[$key] ) || $condition[$key] === null ) {
							$condition[$key] = $default_values[$key];
						}
					}
				}

				if( $global ) {
					if( isset( $default_values['setting'] ) ) {
						$this->initializeConditionDefaultSetting($namespace, $condition['setting'], $default_values['setting']);
					} else {
						$this->initializeConditionDefaultSetting($namespace, $condition['setting'], $default_values, 'setting');
					}
				} else {
					$this->initializeConditionDefaultSetting($namespace, $condition['setting'], $default_values);
				}
			}
		}
		
		if( ! in_array( ocme()->arr()->get( $condition, 'status' ), OcmeFilterCondition::getStatuses() ) ) {
			return null;
		}
		
		if( $trans ) {
			foreach( $trans as $key => $value ) {
				if( ocme()->arr()->get( $condition, $key ) === null ) {
					ocme()->arr()->set( $condition, $key, $value );
				}
			}
		}
		
		/**
		 * Title
		 * 
		 * @var $title string|array
		 */
		if( null != ( $title = ocme()->arr()->get( $condition, 'setting.title' ) ) ) {
			if( is_array( $title ) ) {
				$title = ocme()->arr()->get( $title, $this->config_language_id );
			} else {
				$title = '';
			}
		} else {
			$title = '';
		}
		
		if( 
			$title === '' 
				||
			(
				ocme()->arr()->get( $condition, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE
					&&
				ocme()->arr()->get( $condition, 'condition_type', $ocme_filter_condition->condition_type ) == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP
			)
		) {
			switch( ocme()->arr()->get( $condition, 'vtype', ocme()->arr()->get( $condition, 'condition_type', $ocme_filter_condition->condition_type ) ) ) {
				case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
					$title = $ocme_filter_condition->attribute->name;

					break;
				}
				case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
					$title = $ocme_filter_condition->attribute_group->name;

					break;
				}
				case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
					$title = $ocme_filter_condition->filter_group->name;

					break;
				}
				case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
					$title = $ocme_filter_condition->option->name;

					break;
				}
				case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : {
					$title = ocme()->trans( 'module::filter.text_property_' . ocme()->arr()->get( $condition, 'name' ) );

					break;
				}
				case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
					$title = ocme()->trans( 'module::filter.text_base_attribute_' . ocme()->arr()->get( $condition, 'name' ) );

					break;
				}
			}
		}
		
		ocme()->arr()->set( $condition, 'setting.title', $title );
		
		/**
		 * Generate breakpoints classes for CSS
		 */
		if( ocme()->arr()->get( $condition, 'setting.layout') == 'grid' && ocme()->arr()->has( $condition, 'setting.layout_cols' ) ) {
			$condition['setting']['wrap_classes'] = $this->generateBreakpointsClassesForCss( $condition['setting']['layout_cols'], 'filter_global.conditions.setting.layout_cols' );
		}
		
		switch( ocme()->arr()->get( $condition, 'vtype', ocme()->arr()->get( $condition, 'condition_type', $ocme_filter_condition->condition_type ) ) ) {
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
				ocme()->arr()->set( $condition, 'condition_group_id', $ocme_filter_condition->attribute->attribute_group_id );
				ocme()->arr()->set( $condition, 'values_type', $ocme_filter_condition->attribute->values_type );

				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
				ocme()->arr()->set( $condition, 'condition_group_id', $ocme_filter_condition->attribute_group->attribute_group_id );

				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
				/* @var $values_type string */
				$values_type = $ocme_filter_condition->option->values_type;
				
				if( in_array( $ocme_filter_condition->option->type, array( 'date', 'time', 'datetime' ) ) ) {
					$values_type = $ocme_filter_condition->option->type;
				}
				
				ocme()->arr()->set( $condition, 'values_type', $values_type );
				ocme()->arr()->set( $condition, 'option_type', $ocme_filter_condition->option->type );
				
				break;
			}
			case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
				ocme()->arr()->set( $condition, 'values_type', $ocme_filter_condition->filter_group->values_type );
				
				break;
			}
		}
		
		switch( ocme()->arr()->get( $condition, 'condition_type' ) ) {
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
				switch( ocme()->arr()->get( $condition, 'name' ) ) {
					case 'price' : {
						if( ocme()->arr()->get( $condition, 'setting.show_currency' ) ) {
							/* @var $currency string */
							if( null != ( $currency = ocme()->arr()->get( ocme()->oc()->registry()->get('session')->data, 'currency' ) ) ) {
								ocme()->arr()->set( $condition, 'data.symbol_left', ocme()->oc()->registry()->get('currency')->getSymbolLeft( $currency ) );
								ocme()->arr()->set( $condition, 'data.symbol_right', ocme()->oc()->registry()->get('currency')->getSymbolRight( $currency ) );
							}
						}
						
						break;
					}
				}
				
				break;
			}
		}
		
		return $condition;
	}
	
	public function generateBreakpointsClassesForCss( $cols, $default_key ) {
		/* @var $default_breakpoint \Ocme\Model\OcmeVariable */
		if( null != ( $default_breakpoint = ocme()->variable()->breakpoints()->where('value', 'default')->first() ) ) {
			if( isset( $cols[$default_breakpoint->id] ) ) {
				$cols['default'] = $cols[$default_breakpoint->id];

				unset( $cols[$default_breakpoint->id] );
			}
		}

		return implode(' ', ocme()->collection()->make( $cols )
			->except( 'default' )
			->mapWithKeys(function($v, $k) use( $cols, $default_key ){
				if( $v == 'default' ) {
					$v = ocme()->arr()->get( $cols, 'default', ocme()->variable()->get( $default_key ) );
				}

				return array( $k => 'ocme-mfp-f-breakpoint-' . $k . '-' . $v );
			})->all() );
	}
	
	protected function initializeConditionDefaultSetting( $namespace, & $value, & $default_values, $key = '' ) {
		if( is_array( $value ) ) {
			foreach( $value as $k => & $v ) {
				$this->initializeConditionDefaultSetting( $namespace, $v, $default_values, ( $key ? $key . '.' : '' ) . $k );
			}
		} else if( $value === null && null !== ( $default_value = ocme()->arr()->get( $default_values, $key ) ) ) {
			$value = $default_value;
		}
	}
	
	public function getCurrencyValue() {
		return ocme()->oc()->registry()->get('currency')->getValue( ocme()->arr()->get( ocme()->oc()->registry()->get('session')->data, 'currency' ) );
	}
	
	protected function getCurrentPage( $per_page, $total ) {
		/* @var $page int */
		$page = (int) ocme()->request()->input('ocmef_page', 1);
		
		if( $page < 1 ) {
			$page = 1;
		} else if( $per_page > 0 && $total ) {
			/* @var $last_page int */
			$last_page = ceil( $total / $per_page );
			
			if( $page > $last_page ) {
				$page = $last_page;
			}
		}
		
		return $page;
	}
	
	protected function offsetItems( Condition $condition ) {
		if( 
			$condition->isVirtualList()
				&&
			$condition->getConfig( 'setting.display_list_of_items' ) == 'with_more_button' 
		) {
			return max( array( 0, (int) ocme()->request()->input( 'ocmef_offset', $condition->getConfig( 'setting.limit_of_items' ) ) ) );
		}
		
		return null;
	}
	
	protected function limitItems( Condition $condition ) {
		/* @var $params array */
		if( null != ( $params = ocme()->arr()->get( self::$conditions_params, $condition->paramKeyName() ) ) ) {
			if( 0 < ( $limit = (int) ocme()->arr()->get( $params, 'limit' ) ) ) {
				return min( $limit, 1000 );
			}
		}
		
		/* @var $offset int */
		if( $this->conditions_list == 'first' && null != ( $offset = $this->offsetItems( $condition ) ) ) {
			return $offset;
		}
		
		if( $condition->withAutocomplete() ) {
			return (int) $condition->getConfig('setting.autocomplete.max_suggestions');
		}
		
		return (int) $condition->getConfig( 'setting.max_items' );
	}
	
	protected function applyMaxItems( Condition $condition, \Illuminate\Database\Eloquent\Builder $query, $total = null ) {		
		/* @var $per_page int */
		if( null != ( $per_page = $this->limitItems( $condition ) ) && $per_page > 0 ) {
			$query->limit( $per_page );
			
			/* @var $offset int */
			$offset = 0;
			
			if( ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' )  ) {
				$offset += $this->offsetItems( $condition );
			}
			
			if( 1 < ( $page = $this->getCurrentPage( $per_page, $total ) ) ) {
				$offset += $per_page * ( $page - 1 );
			}
			
			if( $offset ) {
				$query->offset( $offset );
			}
		}
		
		return $this;
	}
	
	protected function applyCalculateCount( Condition $condition, \Illuminate\Database\Eloquent\Builder $query ) {
		if( $condition->getConfig( 'setting.calculate_count' ) ) {			
			$query
				->addSelect( ocme()->db()->raw( 'COUNT(DISTINCT `p`.`product_id`) AS `total`' ) )
				->having('total', '>', 0);
			
			if( ! $query->getQuery()->groups ) {
				/* @var $alias string */
				if( null == ( $alias = $query->getQuery()->getFromAlias() ) ) {
					$alias = $query->getQuery()->from;
				}
				
				/* @var $column string */
				if( $query->getModel() instanceof ProductAttributeValue ) {
					$column = 'attribute_value_id';
					$alias = '`av`';
				} else {
					switch( $condition->getConfig('vtype') ) {
						case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
							$column = 'option_value_id';
							$alias = '`pov`';
							
							break;
						}
						case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE :
						case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP : {
							$column = 'attribute_value_id';
							$alias = '`pav`';
							
							break;
						}
						case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
							$column = 'filter_id';
							$alias = '`pf`';
							
							break;
						}
						default : {
							$column = $query->getModel()->getKeyName();
					
							if( in_array( $column, array( 'id', 'label' ) ) ) {
								$alias = null;
							}
							
							break;
						}
					}
				}
				
				$query->groupBy( ( $alias ? $alias . '.' : '' ) . $column );
			}
		}
		
		return $this;
	}
	
	/**
	 * @param Condition $condition
	 * @return array
	 */
	public function getTagValuesQuery( Condition $condition ) {		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = ProductTag::query()
			->select(array(
				ocme()->db()->raw('MAX(`pt`.`product_tag_id`) AS `id`'),
				ocme()->db()->raw('MAX(`pt`.`name`) AS `label`'),
			))
			->addFromAlias('`pt`')
			->leftJoin('product_to_tag AS `p2t`', '`p2t`.product_tag_id', '=', '`pt`.product_tag_id')
			->leftJoin('product AS `p`', '`p`.product_id', '=', '`p2t`.product_id')
			->where('`pt`.language_id', $this->config_language_id);
		
		return $query;
	}
	
	protected function valuesSortOrder( Condition $condition ) {
		/* @var $sort string */
		if( 'number_of_products' == ( $sort = $condition->getConfig( 'setting.values_sort_order' ) ) ) {
			if( ! $condition->getConfig( 'setting.calculate_count' ) ) {
				$sort = 'default';
			}
		}
		
		/* @var $order string */
		$order = null;
		
		if( $condition->getConfig( 'setting.hide_inactive_values' ) && $condition->getConfig( 'setting.calculate_count' ) ) {
			$sort = 'number_of_products';
			$order = 'DESC';
		}
		
		if( 
			$condition->getConfig('condition_type') == OcmeFilterCondition::CONDITION_TYPE_PROPERTY 
				&&
			in_array( $condition->getConfig( 'name' ), array( 'length', 'width', 'height', 'weight' ) )
		) {
			$sort = 'natural_numeric';
			$order = null;
		}
		
		if( is_null( $order ) ) {
			$order = $condition->getConfig( 'setting.values_sort_order_type', 'ASC' );
		}
		
		return array( $sort, $order );
	}
	
	protected function prepareValuesQuery( Condition $condition, \Illuminate\Database\Eloquent\Builder $query, array $where_conditions = array() ) {		
		/* @var $phrase string */
		if( '' !== ( $phrase = ocme()->arr()->get( $where_conditions, 'phrase', '' ) ) ) {
			$this->applyAsSeparateWords($query, 'label', $phrase, 'having');
		}
		
		if( $condition->withCalculateCount() || $condition->withAutocompleteCalculateCount() ) {
			$this->applyCalculateCount( $condition, $query );
		}
		
		if( $condition->getConfig('setting.display_selected_values_first') ) {
			/* @var $parameter array */
			if( null != ( $parameter = $this->urlParameter( $condition ) ) ) {
				/* @var $parameter_values array */
				if( null != ( $parameter_values = ocme()->arr()->get( $parameter, 'values' ) ) ) {					
					$query->orderBy( ocme()->db()->raw( sprintf(
						'IF( %s IN(%s), 0, 1 )',
						$this->conditionColumnIdName( $condition ),
						implode(',', $this->convertToInteger( array_slice( $parameter_values, 0, 100 ) ) )
					)));
				}
			}
		}
		
		list( $sort, $order ) = $this->valuesSortOrder( $condition );
		
		$this
			->applyOrderBy( $condition, $query, $condition->getConfig( 'values_type', $condition->getConfig( 'setting.values_type' ) ), 'asc' )
			->applyOrderBy( $condition, $query, $sort, $order );
		
		return $this;
	}
	
	protected function applyOrderBy( Condition $condition, \Illuminate\Database\Eloquent\Builder $query, $sort, $order ) {
		if( is_null( $sort ) ) {
			switch( $condition->getConfig('condition_type') ) {
				case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
					switch( $condition->getConfig('name') ) {
						case 'manufacturer' : {
							$sort = 'label';
							
							break;
						}
					}
					
					break;
				}
			}
		}
		
		switch( $sort ) {
			case 'default' : {
				switch( $condition->getConfig('condition_type') ) {
					case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : {
						switch( $condition->getConfig('name') ) {
							case 'manufacturer' : {
								$query->orderBy('`m`.sort_order', $order);
								
								break;
							}
							case 'category' : {
								$query->orderBy('`c`.sort_order', $order);
								
								break;
							}
						}
						
						break;
					}
					case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
						$query->orderBy('`av`.sort_order', $order);
					
						break;
					}
					case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
						if( in_array( $condition->getConfig('option_type'), array( 'checkbox', 'radio', 'select' ) ) ) {
							$query->orderBy('`ov`.sort_order', $order);
						} else {
							$query->orderBy('`o`.sort_order', $order);
						}
					
						break;
					}
					case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : {
						$query->orderBy('`f`.sort_order', $order);
					
						break;
					}
				}
				
				break;
			}
			case 'number_of_products' : {
				$query->orderBy( 'total', $order );
				
				break;
			}
			case 'integer' :
			case 'float' :
			case 'numeric' : {
				/* @var $column_name string */
				$column_name = '`label`';
				
				/* @var $column mixed */
				foreach( $query->getQuery()->columns as $column ) {
					if( preg_match( '/\((.+)\) AS `?label`?/', (string) $column, $matches ) ) {
						$column_name = $matches[1];
						
						break;
					}
				}
				
				$query->orderBy( ocme()->db()->raw( '(' . $column_name . ' + 0)' ), $order );
				
				break;
			}
			case 'label' :
			case 'string': 
			case 'natural_numeric' : {
				$query->orderBy( 'label', $order );
				
				break;
			}
		}
		
		return $this;
	}
	
	/**
	 * @param Condition $condition
	 * @param string $type
	 * @param array $rows
	 * @return array
	 */
	protected function prepareValuesList( Condition $condition, $type, & $rows ) {
		list( $sort, $order ) = $this->valuesSortOrder( $condition, $type );
		
		if( ! in_array( $sort, array( 'numeric', 'natural_numeric', 'number_of_products' ) ) ) {
			usort( $rows, function($a, $b) use( $order ) {
				return $order == 'asc' ? strnatcmp( $a['label'], $b['label'] ) : strnatcmp( $b['label'], $a['label'] );
			});
		}
		
		return $rows;
	}
	
	protected function applyAvailabilityCondition( $item, \Illuminate\Database\Eloquent\Builder $query, $postfix = '', $type = null ) {
		if( $item instanceof Condition ) {
			if( $item->getConfig('name') == 'availability' ) {
				return $this;
			}
		}
		
		/* @var $availability array */
		$availability = $this->getAvailability( $postfix );
		
		if( $item instanceof Condition ) {
			if( null != $availability ) {
				if( in_array( 'in_stock', $availability ) ) {
					$query->where('`p`.quantity', '>', 0);
				} else if( in_array( 'out_of_stock', $availability ) ) {
					$query->where('`p`.quantity', '<=', 0);
				}
			} else if( ocme()->variable()->get('filter_global.configuration.other.only_available') == '1' ) {
				$query->where('`p`.quantity', '>', 0);
			}
			
			if( $item->getConfig('vtype') == OcmeFilterCondition::CONDITION_TYPE_OPTION ) {
				if( null != $availability ) {
					if( in_array( 'in_stock', $availability ) ) {
						if( ocme()->variable()->get('filter_global.configuration.other.only_available') != '1' ) {
							$query->where('`p`.quantity', '>', 0);
						}
						
						$query->where('`pov`.quantity', '>', 0);
					} else if( in_array( 'out_of_stock', $availability ) ) {
						$query
							->where('`p`.quantity', '<=', 0)
							->where('`pov`.quantity', '<=', 0);
					}
				} else if( ocme()->variable()->get('filter_global.configuration.other.only_available_option') == '1' ) {
					$query->where('`pov`.quantity', '>', 0);
				}
			}
		} else if( null != $availability ) {
			if( in_array( 'in_stock', $availability ) ) {
				$query->where('`p`.quantity', '>', 0);
			} else if( in_array( 'out_of_stock', $availability ) ) {
				$query->where('`p`.quantity', '<=', 0);
			}
		} else if( 
			(
				! in_array( $type, array( 'getTotalProducts', 'getProducts' ) ) 
					||
				self::$url_parameters
			)
				&&
			ocme()->variable()->get('filter_global.configuration.other.only_available') == '1' 
		) {
			$query->where('`p`.quantity', '>', 0);
		}
		
		return $this;
	}
	
	/**
	 * @param Condition $condition
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $where_conditions
	 * @param array $options
	 * @return array
	 */
	protected function prepareValues( Condition $condition, \Illuminate\Database\Eloquent\Builder $query, array $where_conditions = array(), array $options = array() ) {		
		/* @var $items array */
		$items = array();
		
		/* @var $postfix string */
		$postfix = '';
		
		if( 
			(
				! empty( self::$url_parameters_current ) && ocme()->arr()->get( self::$filter_data, 'ocmef_live_filter' )
			)
				||
			ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_conditions' ) 
				|| 
			ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' ) 
				|| 
			ocme()->arr()->get( self::$filter_data, 'ocmef_refreshing_values' )
		) {
			$postfix = 'current';
		}
		
		$this
			//->applyConditions( $query, $condition->getConfig('name') )
			->applyAvailabilityCondition( $condition, $query, '', 'prepareValues' )
			->applyOpenCartFilters( $query, $condition->getConfig('name'), $postfix, true );
		
		$this->prepareValuesQuery( $condition, $query, $where_conditions );
		
		/* @var $total_values int */
		$total_values = ocme()->cache()->db( sprintf( 'SELECT COUNT(*) AS `total` FROM( %s ) AS `tmp`', ocme()->db()->queryToRawSql( $query ) ), function( $sql ){
			return ocme()->arr()->get( ocme()->db()->connection()->selectOne( $sql ), 'total' );
		});
		
		/* @var $limit int */
		$limit = $this->limitItems( $condition );
		
		if( ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' )  ) {
			if( 0 > ( $total_values -= $this->offsetItems( $condition ) ) ) {
				$total_values = 0;
			}
		}
		
		/* @var $pagination array */
		$pagination = array(
			'current_page' => $this->getCurrentPage( $limit, $total_values ),
			'last_page' => ceil( $total_values / $limit ),
			'total' => $total_values,
			'per_page' => $limit,
		);
				
		if( 
			! ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' )
				&&
			$condition->getConfig( 'setting.hide_if_one_value' ) 
				&& 
			ocme()->arr()->get( $pagination, 'current_page' ) == 1 
				&&
			 ocme()->arr()->get( $pagination, 'total' ) < 2
		) {
			return null;
		}
		
		/* @var $complete bool */
		$complete = ocme()->arr()->get( $pagination, 'current_page' ) >= ocme()->arr()->get( $pagination, 'last_page' );
		
		/* @var $soft_complete bool */
		$soft_complete = $complete;

		$this->applyMaxItems( $condition, $query, $total_values );
		
		if( $total_values ) {
			/* @var $url_parameter array */
			$url_parameter = $this->urlParameter( $condition );
			
			/* @var $row_value \Ocme\Database\Model */
			foreach( ocme()->cache()->db( $query, function( $query ){ return $query->get(); } ) as $row_value ) {
				/* @var $value array */
				$value = $this->createValue( $condition, $row_value );
				
				if( $url_parameter && in_array( $value['id'], ocme()->arr()->get( $url_parameter, 'values', array() ) ) ) {
					if( ! isset( self::$url_values[$condition->key()] ) ) {
						self::$url_values[$condition->key()] = array(
							'title' => $condition->getConfig('setting.title'),
							'values' => array()
						);
					}
					
					self::$url_values[$condition->key()]['values'][] = array(
						'id' => $value['id'],
						'label' => $value['label'],
					);
				}
				
				$items[] = $value;
			}
		}
		
		/* @var $force_prepare_values_with_conditions bool */
		$force_prepare_values_with_conditions = 
			$condition->getConfig('vtype') == OcmeFilterCondition::CONDITION_TYPE_OPTION
				&&
			ocme()->variable()->get('filter.include_option_prices')
				&&
			(
				$this->hasAnyOptionConditions( $postfix, $options )
					||
				ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_price', $postfix ) )
			);
		
		if( $this->hasAnyConditions() || $force_prepare_values_with_conditions ) {
			$this->prepareValuesWithConditions($items, $condition, $query, array_replace($options, array(
				'force_prepare_values_with_conditions' => $force_prepare_values_with_conditions
			)), $postfix);
		}
		
		if( ! $soft_complete && $condition->getConfig('setting.hide_inactive_values') ) {
			if( ocme()->arr()->get( ocme()->arr()->last( $items ), 'total_with_conditions' ) === 0 ) {
				$soft_complete = true;
			}			
		}
		
		return compact( 'items', 'pagination', 'complete', 'soft_complete' );
	}
	
	protected function hasAnyConditions() {
		if( ! self::$filter_data ) {
			return false;
		}
		
		foreach( array_keys( self::$filter_data ) as $k ) {
			if( strpos( $k, 'ocme_' ) === 0 ) {
				return true;
			}
		}
		
		return false;
	}
	
	protected function prepareValuesWithConditions( array & $items, Condition $condition, \Illuminate\Database\Eloquent\Builder $query, array $options = array(), $postfix = '' ) {
		/* @var $query_with_conditions \Illuminate\Database\Eloquent\Builder */
		$query_with_conditions = $this->getConditionValuesQuery( $condition, $options );

		$this
			->applyCalculateCount( $condition, $query_with_conditions )
			->applyOpenCartFilters( $query_with_conditions, $condition->getConfig('name', $condition->getConfig('condition_type')), $postfix )
			->applyConditions( $query_with_conditions, $condition->getConfig('name', $condition->getConfig('condition_type')), $postfix, array(
				'vtype' => $condition->getConfig('vtype'),
				'vid' => $condition->getConfig('vid'),
				'condition_between_values' => $condition->getConfig('setting.condition_between_values'),
			))
			->applyAvailabilityCondition( $condition, $query_with_conditions, $postfix, 'prepareValuesWithConditions' );
		
		if( 
			ocme()->arr()->get($options, 'force_prepare_values_with_conditions')
				||
			array_diff( $this->serializeWheres( $query_with_conditions->getQuery()->wheres ), $this->serializeWheres( $query->getQuery()->wheres ) ) 
		) {
			/* @var $ids array */
			if( null != ( $ids = array_fetch( $items, 'id' ) ) ) {
				/* @var $column_id_name string|null */
				if( null != ( $column_id_name = $this->conditionColumnIdName( $condition ) ) ) {
					$query_with_conditions->whereIn( $column_id_name, $ids );
				} else {
					$query_with_conditions->havingRaw( '`label` IN(' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids );
				}
				
				/* @var $items_with_conditions array */
				$items_with_conditions = array();
				
				/* @var $item \stdClass */
				foreach( ocme()->cache()->db( $query_with_conditions, function( $query_with_conditions ) { return $query_with_conditions->get(); } ) as $item ) {
					$items_with_conditions[ is_null( $item->id ) ? mb_strtolower( $item->label, 'utf8' ) : $item->id ] = $item->total;
				}
			}
			
			foreach( $items as & $item ) {
				ocme()->arr()->set( $item, 'total_with_conditions', ocme()->arr()->get( $items_with_conditions, ocme()->arr()->get( $item, 'id' ), 0 ) );
			}
		}
	}
	
	protected function serializeWheres( $wheres ) {
		return array_map(function($v){
			if( ocme()->arr()->get( $v, 'query' ) instanceof \Illuminate\Database\Query\Builder ) {
				$v = ocme()->db()->queryToRawSql(ocme()->arr()->get( $v, 'query' ));
			}

			return serialize( $v );
		}, $wheres);
	}
	
	/**
	 * @param Condition $condition
	 * @param \Ocme\Database\Model $row
	 * @return array
	 */
	protected function createValue( Condition $condition, $row ) {
		/* @var $display array */
		$display = (array) $condition->getConfig( 'setting.display' );
		
		/* @var $option array */
		$value = array(
			'id' => is_null( $row->id ) ? htmlspecialchars_decode( mb_strtolower( $row->label, 'utf8' ) ) : $row->id,
			'label' => $row->label,
			'total' => $row->total,
			'total_with_conditions' => null,
		);

		if( in_array( 'image', $display ) ) {
			$value['image_width'] = $condition->getConfig( 'setting.image_width' );
			$value['image_height'] = $condition->getConfig( 'setting.image_height' );
			
			if( null != ( $value['image'] = $row->image ) ) {
				$value['image'] = ocme()->model('tool/image')->resize( $value['image'], $value['image_width'], $value['image_height'] );
			}
		}

		if( in_array( 'color', $display ) ) {
			$value['color'] = $row->color;
			$value['color_width'] = $condition->getConfig( 'setting.color_width' );
			$value['color_height'] = $condition->getConfig( 'setting.color_height' );
		}
		
		if( $condition->getConfig('condition_type') == OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE && $condition->getConfig('name') == 'availability' ) {
			if( in_array( ocme()->arr()->get( $value, 'id' ), array( 'in_stock', 'out_of_stock' ) ) ) {
				$value['label'] = ocme()->trans( 'module::filter.text_' . $value['label'] );
				
				/* @var $custom_label string */
				if( '' != ( $custom_label = $condition->getConfig( sprintf('setting.%s_text.%s', ocme()->arr()->get( $value, 'id' ), ocme()->oc()->registry()->get('config')->get('config_language_id') ) ) ) ) {
					$value['label'] = $custom_label;
				}
			}
		}
		
		return $value;
	}
	
	protected function propertyColumn( $name, \Illuminate\Database\Eloquent\Builder $query ) {
		/* @var $column string */
		$column = '`p`.' . $name;
		
		if( in_array( $name, array( 'weight', 'width', 'height', 'length' ) ) ) {
			/* @var $alias string */
			$alias = 'cls';
			
			/* @var $joins array */
			$joins = array_map(function($v){
				$parts = explode('AS', $v->table);
				
				return trim( array_shift( $parts ) );
			}, $query->getQuery()->joins);			
			
			switch( $name ) {
				case 'weight' : {
					$alias .= 'w';
					
					if( ! in_array( 'weight_class', $joins ) ) {
						$query->leftJoin('weight_class AS `' . $alias . '`', '`' . $alias . '`.weight_class_id', '=', '`p`.weight_class_id');
					}

					break;
				}
				case 'width' :
				case 'height' :
				case 'length' : {
					$alias .= 'l';
					
					if( ! in_array( 'length_class', $joins ) ) {
						$query->leftJoin('length_class AS `' . $alias . '`', '`' . $alias . '`.length_class_id', '=', '`p`.length_class_id');
					}

					break;
				}
			}
			
			$column = sprintf( '`p`.`%s` / COALESCE(`' . $alias . '`.`value`, 1)', $name );
		}
		
		return $column;
	}
	
	/**
	 * @param Condition $condition
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return string
	 */
	protected function conditionPropertyColumn( Condition $condition, \Illuminate\Database\Eloquent\Builder $query ) {
		if( $condition->getConfig( 'condition_type' ) != OcmeFilterCondition::CONDITION_TYPE_PROPERTY ) {
			return null;
		}
		
		/* @var $column string */
		$column = $this->propertyColumn( $condition->getConfig('name'), $query );
		
		if( in_array( $condition->getConfig( 'name' ), array( 'weight', 'width', 'height', 'length' ) ) ) {			
			$column = $this->columnRoundingValues( $condition, $column );
		}
		
		return $column;
	}
	
	protected function columnRoundingValues( Condition $condition, $column ) {
		return $this->roundingValues( $condition->getConfig('setting.values_rounding'), $column );
	}
	
	protected function roundingValues( $values_rounding, $column ) {
		switch( $values_rounding ) {
			case 'integer' : $column = 'ROUND( ' . $column . ', 0 )'; break;
			case '1d' : $column = 'ROUND( ' . $column . ', 1 )'; break;
			case '2d' : $column = 'ROUND( ' . $column . ', 2 )'; break;
			case '3d' : $column = 'ROUND( ' . $column . ', 3 )'; break;
			case '4d' : $column = 'ROUND( ' . $column . ', 4 )'; break;
		}
		
		return $column;
	}
	
	public function getRange( Condition $condition, $without_conditions = false ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->buildConditionTypeValuesQuery( $condition, array(
			'destination' => 'range',
		));
		
		/* @var $column string */
		$column = null;
		
		switch( $condition->getConfig('values_type') ) {
			case 'integer' : $column = '`vinteger`'; break;
			case 'float' : $column = '`vfloat`'; break;
			default: $column = $this->conditionColumnLabelName( $condition );
		}
		
		$query->select(array(
			ocme()->db()->raw('MIN(' . $column . ') AS `min`'),
			ocme()->db()->raw('MAX(' . $column . ') AS `max`')
		));
		
		$this
			->applyCurrentOpenCartFiltersIfPossible( $query, $condition->getConfig('name', $condition->getConfig('condition_type')) );
		
		if( ! $without_conditions ) {
			$this->applyCurrentConditionsIfPossible( $condition, $query );
		}
		
		/* @var $row \Ocme\Database\Model */
		if( null == ( $row = ocme()->cache()->db( $query, function( $query ) { return $query->first(); } ) ) ) {
			return array(
				'min' => null,
				'max' => null,
			);
		}
		
		if( is_null( $row->min ) && is_null( $row->max ) ) {
			if( ! $without_conditions ) {
				/* @var $pure_range array|null */
				if( null != ( $pure_range = $this->getRange( $condition, true ) ) ) {
					return array(
						'min' => null,
						'max' => null,
					);
				}
			}
			
			return null;
		}
		
		return $this->createRange( $condition, $row->min, $row->max, $condition->isRange() );
	}
	
	public function getPropertyRange( Condition $condition, $without_conditions = false ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->createProductsQuery();
		
		/* @var $column string */
		if( null == ( $column = $this->conditionPropertyColumn($condition, $query) ) ) {
			return null;
		}
		
		$query
			->selectRaw('MIN(' . $column . ') AS `min`')
			->selectRaw('MAX(' . $column . ') AS `max`');
		
		$this
			->applyCurrentOpenCartFiltersIfPossible( $query, $condition->getConfig('name', $condition->getConfig('condition_type')) );
		
		if( ! $without_conditions ) {
			$this->applyCurrentConditionsIfPossible( $condition, $query );
		}
		
		/* @var $row \Ocme\Database\Model */
		$row = ocme()->cache()->db( $query, function( $query ){
			return $query->first();
		});
		
		if( is_null( $row->min ) && is_null( $row->max ) ) {
			if( ! $without_conditions ) {
				/* @var $pure_range array|null */
				if( null != ( $pure_range = $this->getPropertyRange( $condition, true ) ) ) {
					return array(
						'min' => null,
						'max' => null,
					);
				}
			}
			
			return null;
		}
		
		return $this->createRange( $condition, $row->min, $row->max, $condition->isRange() );
	}
	
	public function getPropertyValuesQuery( Condition $condition ) {		
		/* @var $query \Illuminate\Database\Query\Builder */
		$query = $this->createProductsQuery();
		
		/* @var $column string */
		$column = $this->conditionPropertyColumn( $condition, $query );
		
		$query
			->addSelect( ocme()->db()->raw( sprintf('(%s) AS `label`', $column) ) )
			->having('label', '!=', '')
			->groupBy('label');
		
		return $query;
	}
	
	public function buildPriceColumn( $callback, array $options = array() ) {
		/* @var $special_query \Illuminate\Database\Query\Builder */
		$special_query = $this->buildSpecialColumnQuery( ocme()->db()->newQuery() );
		
		/* @var $discount_query \Illuminate\Database\Query\Builder */
		$discount_query = $this->buildDiscountColumnQuery( ocme()->db()->newQuery() );
		
		/* @var $sql string */
		$sql = sprintf('COALESCE((%s), (%s), `p`.`price`)', ocme()->db()->queryToRawSql( $special_query ), ocme()->db()->queryToRawSql( $discount_query ));
		
		/* @var $bindings array */
		$bindings = array();
		
		/* @var $type string */
		$type = ocme()->arr()->get( $options, 'type' );
		
		if( in_array( $type, array( 'MIN', 'MAX' ) ) ) {
			/* @var $option_price_query \Illuminate\Database\Query\Builder */
			$option_price_query = $this->buildOptionPriceQuery($type, ocme()->db()->newQuery(), $options);
			
			$sql = sprintf('(%s + (%s))', $sql, $option_price_query->toSql());
			
			$bindings = array_merge( $bindings, $option_price_query->getBindings() );
		}
		
		if( $this->config_tax ) {
			/* @var $percent_tax_query */
			$percent_tax_query = $this->buildTaxColumnQuery( ocme()->db()->newQuery(), 'P' );
			
			/* @var $fixed_tax_query */
			$fixed_tax_query = $this->buildTaxColumnQuery( ocme()->db()->newQuery(), 'F' );
			
			$sql = sprintf(
				'(%s * ( 1 + IFNULL((%s), 0) / 100) + IFNULL((%s), 0) )', 
				$sql, 
				ocme()->db()->queryToRawSql( $percent_tax_query ), 
				ocme()->db()->queryToRawSql( $fixed_tax_query )
			);
		}
		
		/* @var $currency_value float */
		if( 1 != ( $currency_value = $this->getCurrencyValue() ) ) {
			$sql = sprintf('( %s * %s )', $sql, $currency_value);
		}
		
		if( $type == 'range' ) {
			$sql = sprintf( '(%s)', $this->buildOptionPriceSql( $sql, ocme()->arr()->get( $options, 'range' ), ocme()->db()->newQuery(), $options ) );
		}
		
		$callback( $sql, $bindings );
	}
	
	protected function shouldUseCurrentPostifx() {
		return 
			ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' ) 
				|| 
			ocme()->arr()->get( self::$filter_data, 'ocmef_refreshing_values' );
	}
	
	public function applyCurrentOpenCartFiltersIfPossible( $query, $type ) {
		if( $this->shouldUseCurrentPostifx() ) {
			return $this->applyOpenCartFilters( $query, $type, 'current' );
		}
		
		return $this->applyOpenCartFilters( $query, $type );
	}
	
	public function applyCurrentConditionsIfPossible( Condition $condition, $query ) {
		/* @var $type string */
		$type = $condition->getConfig('name', $condition->getConfig('condition_type'));
		
		if( $this->shouldUseCurrentPostifx() ) {
			return $this->applyConditions( $query, $type, 'current', array(
				'vtype' => $condition->getConfig('vtype'),
				'vid' => $condition->getConfig('vid'),
			))->applyAvailabilityCondition( $condition, $query, 'current', 'applyCurrentConditionsIfPossible' );
		}
		
		return $this->applyConditions( $query, $type, '', array(
			'vtype' => $condition->getConfig('vtype'),
			'vid' => $condition->getConfig('vid'),
		))->applyAvailabilityCondition( $condition, $query, '', 'applyCurrentConditionsIfPossible' );
	}
	
	/**
	 * @return array|null
	 */
	public function getPriceRange( Condition $condition, $without_conditions = false ) {		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->createProductsQuery();
		
		if( ocme()->variable()->get('filter.include_option_prices') ) {
			$this->buildPriceColumn(function( $sql, $bindings ) use( $query, $condition ){
				$query->selectRaw($this->sqlRange( $condition, 'MIN(' . $sql . ')' ) . ' as `omin`', $bindings);
			}, array(
				'type' => 'MIN',
				'postfix' => ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' ) || ocme()->arr()->get( self::$filter_data, 'ocmef_refreshing_values' ) ? 'current' : '',
			));
			
			$this->buildPriceColumn(function( $sql, $bindings ) use( $query, $condition ){
				$query->selectRaw($this->sqlRange( $condition, 'MAX(' . $sql . ')' ) . ' as `omax`', $bindings);
			}, array(
				'type' => 'MAX',
				'postfix' => ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' ) || ocme()->arr()->get( self::$filter_data, 'ocmef_refreshing_values' ) ? 'current' : '',
			));
		} 
		
		$this->buildPriceColumn(function( $sql, $bindings ) use( $query, $condition ){			
			$query
				->selectRaw($this->sqlRange( $condition, 'MIN(' . $sql . ')' ) . ' as `min`', $bindings)
				->selectRaw($this->sqlRange( $condition, 'MAX(' . $sql . ')' ) . ' as `max`', $bindings);
		});
		
		$this
			->applyCurrentOpenCartFiltersIfPossible( $query, 'price' );
		
		if( ! $without_conditions ) {
			$this->applyCurrentConditionsIfPossible( $condition, $query );
		}
		
		//echo ocme()->db()->queryToRawSql($query);exit;
		
		/* @var $row \Ocme\Database\Model */
		$row = ocme()->cache()->db( $query, function( $query ){
			return $query->first();
		});
		
		/* @var $min float */
		$min = $row->min;
		
		/* @var $max float */
		$max = $row->max;
		
		if( ocme()->variable()->get('filter.include_option_prices') ) {
			if( $this->hasAnyOptionConditions( $this->shouldUseCurrentPostifx() ? 'current' : '' ) ) {
				$min = $row->omin;
				$max = $row->omax;
			} else {
				$min = min(array( $row->omin, $min ));
				$max = max(array( $row->omax, $max ));
			}
		}
		
		if( ! $min && ! $max ) {
			if( ! $without_conditions ) {
				/* @var $pure_range array|null */
				if( null != ( $pure_range = $this->getPriceRange( $condition, true ) ) ) {
					return array(
						'min' => null,
						'max' => null,
					);
				}
			}
			
			return null;
		}
		
		return $this->createRange( $condition, $min, $max, true );
	}
	
	protected function valuesRounding( Condition $condition ) {
		switch( $condition->getConfig('setting.values_rounding') ) {
			case 'integer' : {
				return 0;
			}
			case '1d' :
			case '2d' :
			case '3d' :
			case '4d' : {
				return (int) str_replace('d', '', $condition->getConfig('setting.values_rounding'));
			}
			case 'max' : {				
				return 5;
			}
		}
	}
	
	protected function sqlRange( Condition $condition, $sql ) {
		return 'ROUND(' . $sql . ', ' . $this->valuesRounding( $condition ) . ')';
	}
	
	/**
	 * @param Condition $condition
	 * @return array|null
	 */
	protected function urlParameter( Condition $condition ) {		
		return ocme()->arr()->first( self::${'url_parameters'.$this->postfix()}, function( $item ) use( $condition ){
			if( $condition->getConfig('vid') ) {
				return ocme()->arr()->get( $item, 'name' ) == $condition->getConfig('vtype') && ocme()->arr()->get( $item, 'id' ) == $condition->getConfig('vid');
			}
			
			return ocme()->arr()->get( $item, 'name' ) == $condition->getConfig('name');
		});
	}
	
	protected function createRange( Condition $condition, $min, $max, $to_float ) {
		if( in_array( $condition->getConfig('values_type'), array( 'date', 'time', 'datetime' ) ) ) {
			return array(
				'min' => strtotime( $min ),
				'max' => strtotime( $max ),
			);
		}
		
		/* @var $dec int|null */
		$dec = $this->valuesRounding( $condition );
		
		switch( $dec ) {
			case 0 : {
				if( ! is_null( $min ) ) {
					$min = floor( $min );
				}
				
				if( ! is_null( $max ) ) {
					$max = ceil( $max );
				}
				
				break;
			}
			default : {
				if( ! is_null( $min ) ) {
					$min = number_format( $min, $dec, '.', '' );
				}
				
				if( ! is_null( $max ) ) {
					$max = number_format( $max, $dec, '.', '' );
				}
				
				break;
			}
		}
		
		if( $to_float ) {
			if( ! is_null( $min ) ) {
				$min = (float) $min;
			}
			
			if( ! is_null( $max ) ) {
				$max = (float) $max;
			}
		}
		
		/* @var $url_parameter array */
		if( null != ( $url_parameter = $this->urlParameter( $condition ) ) ) {
			/* @var $values array */
			if( null == ( $values = ocme()->arr()->get( $url_parameter, 'values' ) ) ) {
				if( null == ( $values = ocme()->arr()->get( $url_parameter, 'integer_range' ) ) ) {
					$values = ocme()->arr()->get( $url_parameter, 'float_range' );
				}
			}
			
			self::$url_values[$condition->key()] = array(
				'title' => $condition->getConfig('setting.title'),
				'values' => array(array(
					'id' => implode( '-', $values ),
					'label' => sprintf( '%s%s%s', $condition->getConfig('data.symbol_left'), implode( ' - ', array_slice( $values, 0, 2 ) ), $condition->getConfig('data.symbol_right') )
				))
			);
		}
		
		return array(
			'min' => $min,
			'max' => $max
		);
	}
	
	protected function postfixForProductQueries() {
		/* @var $postfix string */
		$postfix = '';
		
		if(
			ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' ) 
				|| 
			ocme()->arr()->get( self::$filter_data, 'ocmef_refreshing_values' )
		) {
			$postfix = 'current';
		}
		
		return $postfix;
	}
	
	public function getTotalProducts() {
		/* @var $postfix string */
		$postfix = $this->postfixForProductQueries();
		
		/* @var $cache_key string */
		$cache_key = __FUNCTION__ . '.' . $postfix . '.' . $this->cacheKey();
		
		if( isset( self::$cache[$cache_key] ) ) {
			return self::$cache[$cache_key];
		}
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->createProductsQuery()
			->select( ocme()->db()->raw( 'COUNT(DISTINCT `p`.`product_id`) AS `total`' ) );		
		
		$this
			->applyOpenCartFilters( $query, 'getTotalProducts', $postfix )
			->applyConditions( $query, 'getTotalProducts', $postfix )
			->applyAvailabilityCondition( null, $query, $postfix, 'getTotalProducts' );
		
		/* @var $row */
		if( null != ( $row = $query->first() ) ) {
			self::$cache[$cache_key] = $row->total;
		} else {
			self::$cache[$cache_key] = 0;
		}
		
		return self::$cache[$cache_key];
	}

	public function setOriginRoute( $origin_route ) {
		$this->origin_route = $origin_route;

		return $this;
	}
	
	public function getProducts() {
		/* @var $postfix string */
		$postfix = $this->postfixForProductQueries();
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = $this->createProductsQuery()
			->select('`p`.product_id')
			->selectSub(function($q){
				$this->buildRatingColumnQuery($q);
			}, 'rating')
			->selectSub(function($q){
				$this->buildDiscountColumnQuery($q);
			}, 'discount')
			->selectSub(function($q){
				$this->buildSpecialColumnQuery($q);
			}, 'special')
			->groupBy('`p`.product_id');
		
		/** @var array $sorts */
		$sorts = self::$SORT_PRODUCTS;

		if( in_array( $this->origin_route, array( 'catalog/product/getProductSpecials' ) ) ) {
			$sorts = self::$SORT_SPECIAL_PRODUCTS;
		}
		
		$this
			->applyOpenCartFilters( $query, 'getProducts', $postfix )
			->applyConditions( $query, 'getProducts', $postfix )
			->applyAvailabilityCondition( null, $query, $postfix, 'getProducts' )
			->applySortOrder( $query, $sorts, ocme()->arr()->get( self::$filter_data, 'sort', ocme()->request()->query('sort') ), ocme()->arr()->get( self::$filter_data, 'order', ocme()->request()->query('order', 'ASC') ) )
			->applyPagination( $query, ocme()->arr()->get( self::$filter_data, 'start' ), ocme()->arr()->get( self::$filter_data, 'limit' ) );
			
		return $query->get()->map( function( $product ){
			return ocme()->oc()->registry()->get('model_catalog_product')->getProduct( $product->product_id );
		});
	}
	
	protected function cacheKey() {
		return md5( serialize( self::$filter_data ) );
	}
	
	protected function applyOpenCartFilters( $query, $type = null, $postfix = '', $force_filter_category = false ) {		
		$query
			->where('`p`.status', '1')
			->where('`p`.date_available', '<=', ocme()->db()->raw('NOW()'));
		
		if( in_array( ocme()->arr()->get( self::$filter_data, 'ocmef_source_route' ), array( 'product/special' ) ) ) {
			$query->whereExists(function($q){
				$q
					->select( ocme()->db()->raw(1) )
					->from('product_special AS `ps`')
					->whereColumn('`p`.product_id', '`ps`.product_id')
					->where('`ps`.customer_group_id', $this->config_customer_group_id)
					->where(function($q){
						$q->where('`ps`.date_start', '0000-00-00')->orWhereRaw('`ps`.date_start < NOW()');
					})
					->where(function($q){
						$q->where('`ps`.date_end', '0000-00-00')->orWhereRaw('`ps`.date_end > NOW()');
					});
			});
		}
		
		if( $type != 'category' ) {
			/* @var $filter_category_ids array */
			if( 
				// overwrite selected category in a filter, but only for getProducts or getTotalProducts
				//in_array( $type, array( 'getProducts', 'getTotalProducts' ) ) 
				//	&& 
				! $force_filter_category
					&&
				! empty( $filter_category_ids = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_category_ids', $postfix ) ) ) 
			) {
//				if( null != ocme()->arr()->get( self::$filter_data, 'filter_sub_category' ) ) {
//					$query->whereIn('`cp`.path_id', $filter_category_ids);
//				} else {
//					$query->whereIn('`p2c`.category_id', $filter_category_ids);
//				}
			} else
			/* @var $filter_category_id int */
			if( ! empty( $filter_category_id = ocme()->arr()->get( self::$filter_data, 'filter_category_id' ) ) ) {
				if( ! empty( ocme()->arr()->get( self::$filter_data, 'filter_sub_category' ) ) ) {
					$query->where('`cp`.path_id', (int) $filter_category_id);
				} else {
					$query->where('`p2c`.category_id', (int) $filter_category_id);
				}
			}
			
			/* @var $filter_filter string */
			if( null != ( $filter_filter = ocme()->arr()->get( self::$filter_data, 'filter_filter' ) ) ) {
				/* @var $filter_ids array */
				$filter_ids = array_filter(array_map(function($id){
					return (int) $id;
				}, explode(',', $filter_filter)), function($v){
					return $v !== '';
				});
				
				if( $filter_ids ) {
					$query->whereIn('`pf`.filter_id', $filter_ids);
				}
			}
		}
		
		/* @var $filter_name string */
		$filter_name = ocme()->arr()->get( self::$filter_data, 'filter_name' );
		
		/* @var $filter_tag string */
		$filter_tag = ocme()->arr()->get( self::$filter_data, 'filter_tag' );
		
		if( ! empty( $filter_name ) || ! empty( $filter_tag ) ) {
			/* @var $joins array */
			$joins = array();
			
			if( $query instanceof \Illuminate\Database\Query\Builder ) {
				$joins = $query->joins;
			} else {
				$joins = $query->getQuery()->joins;
			}
			
			if( ocme()->arr()->first( (array) $joins, function( $join ){ return strpos( ocme()->arr()->get( $join, 'table', '' ), 'product_description' ) !== false; } ) ) {
				$this->applySearchFilter( $query, $filter_name, $filter_tag );
			} else {
				$this->applySearchConditions( $query, $type, '', array(
					'filter_name' => $filter_name,
					'filter_tag' => $filter_tag,
				));
			}
		}
		
//		if( null != $filter_name || null != $filter_tag ) {
//			$query->where(function($q) use( $filter_name, $filter_tag ){
//				if( ! empty( $filter_name ) ) {
//					$q
//						->orWhere(function($q) use( $filter_name ){
//							$this->applyAsSeparateWords($q, '`pd`.name', $filter_name);
//						})
//						->orWhere('`pd`.description', 'LIKE', '%' . $filter_name . '%');
//					
//					/* @var $column string */
//					foreach( array( 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn' ) as $column ) {
//						$q->orWhere('`p`.' . $column, ocme()->str()->lower( $filter_name ));
//					}
//				}
//				
//				if( ! empty( $filter_tag ) ) {
//					$q->orWhere(function($q) use( $filter_tag ){
//						$this->applyAsSeparateWords($q, '`pd`.tag', $filter_tag);
//					});
//				}
//			});
//		}
		
		/* @var $filter_manufacturer_id int */
		if( null != ( $filter_manufacturer_id = ocme()->arr()->get( self::$filter_data, 'filter_manufacturer_id' ) ) ) {
			$query->where('`p`.manufacturer_id', (int) $filter_manufacturer_id);
		}
		
		return $this;
	}
	
	protected function convertToInteger( $values ) {
		if( ! is_array( $values ) ) {
			$values = explode( ',', $values );
		}
		
		return array_map(function( $v ){
			return (int) $v;
		}, array_filter( $values, function( $v ){
			return is_numeric( $v );
		}));
	}
	
	protected function convertToFloat( $values ) {
		if( ! is_array( $values ) ) {
			$values = explode( ',', $values );
		}
		
		return array_map(function( $v ){
			return (float) $v;
		}, array_filter( $values, function( $v ){
			return is_numeric( $v );
		}));
	}
	
	protected function createPostfixName( $name, $postfix ) {
		if( $postfix ) {
			$postfix = '_' . $postfix;
		}
		
		return $name . $postfix;
	}
	
	protected function applyAttributeCondtions( $query, $type = null, $postfix = '', array $options = array() ) {
		/* @var $attribute_ids \stdClass|null */
		if( null != ( $attribute_ids = $this->getConditionAttributeIds( $postfix, $options ) ) ) {			
			/* @var $attribute_id int */
			/* @var $attribute_value_ids array */
			foreach( $attribute_ids->ids as $attribute_id => $attribute_value_ids ) {
				$this->applyWhereExists( $query, function($q, $attribute_value_ids) use( $attribute_id ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_attribute_value AS `pav`')
						->whereColumn('`p`.product_id', '`pav`.product_id')
						->where('`pav`.attribute_id', $attribute_id)
						->whereIn('`pav`.attribute_value_id', $attribute_value_ids);
				}, $attribute_value_ids, $attribute_ids->boolean);
			}
		}
		
		/* @var $attribute_texts array */
		if( null != ( $attribute_texts = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_attribute_text', $postfix ) ) ) ) {
			if( ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE ) {
				$attribute_texts = ocme()->arr()->except( $attribute_texts, ocme()->arr()->get( $options, 'vid' ) );
			}
			
			/* @var $attribute_id int */
			/* @var $attribute_value_texts array */
			foreach( $attribute_texts as $attribute_id => $attribute_value_texts ) {
				$query->whereExists(function($q) use( $attribute_id, $attribute_value_texts ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_attribute_value AS `pav`')
						->leftJoin('attribute_value_description AS `avd`', '`avd`.attribute_value_id', '=', '`pav`.attribute_value_id')
						->whereColumn('`p`.product_id', '`pav`.product_id')
						->where('`avd`.language_id', $this->config_language_id)
						->where('`pav`.attribute_id', $attribute_id)
						->where(function($q) use( $attribute_value_texts ){
							foreach( $attribute_value_texts as $v ) {
								$q->orWhere('`avd`.name', 'LIKE', '%' . $v . '%');
							}
						});
				});
			}
		}
		
		/* @var $key string */
		foreach( array( 'integer', 'float' ) as $key ) {
			/* @var $attribute_range array */
			if( null != ( $attribute_range = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_attribute_' . $key . '_range', $postfix ) ) ) ) {
				if( ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE ) {
					$attribute_range = ocme()->arr()->except( $attribute_range, ocme()->arr()->get( $options, 'vid' ) );
				}
			
				/* @var $attribute_id int */
				/* @var $range array */
				foreach( $attribute_range as $attribute_id => $range ) {
					if( count( $range ) >= 2 ) {
						$query->whereExists(function($q) use( $key, $attribute_id, $range ){
							$q
								->select( ocme()->db()->raw(1) )
								->from('product_attribute_value AS `pav`')
								->leftJoin('attribute_value AS `av`', '`pav`.attribute_value_id', '=', '`av`.attribute_value_id')
								->whereColumn('`p`.product_id', '`pav`.product_id')
								->where('`pav`.attribute_id', $attribute_id)
								->whereBetween('`av`.v' . $key, array_slice( $range, 0, 2 ));
						});
					}
				}
			}
		}
		
		return $this;
	}

	protected function getAvailability( $postfix = '' ) {
		/* @var $availability array */
		if( null != ( $availability = (array) ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_availability', $postfix ) ) ) ) {
			if( count( $availability ) != 1 ) {
				$availability = array();
			}
		}

		return $availability;
	}
	
	protected function getQuantityRange( $type, $postfix ) {
		/* @var $quantity_range array */
		if( $type != 'quantity' && null != ( $quantity_range = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_quantity_range', $postfix ) ) ) && count( $quantity_range ) >= 2 ) {
			return array_slice( $quantity_range, 0, 2 );
		}
		
		return array();
	}
	
	protected function applyQuantityConditions( $query, $type = null, $postfix = '', array $options = array() ) {
		/* @var $quantity_range array */
		if( null != ( $quantity_range = $this->getQuantityRange( $type, $postfix ) ) ) {
			$query
				->leftJoin('product_option_value AS `povq`', '`p`.product_id', '=', '`povq`.product_id')
				->leftJoin('product_option AS `poq`', '`povq`.product_option_id', '=', '`poq`.product_option_id')
				->where(function($q) use( $quantity_range ){
					$q
						->whereBetween('`p`.quantity', array_slice( $quantity_range, 0, 2 ))
						->orWhere(function($q) use( $quantity_range ){
							$q->whereNull('`povq`.product_option_value_id')->orWhere('`poq`.required', '0')->orWhere(function($q) use( $quantity_range ){
								$q->where('`poq`.required', '1')->whereBetween('`povq`.quantity', $quantity_range);
							});
						});
				});
		}
		
		return $this;
	}
	
	/**
	 * @param string $type
	 * @param string $postfix
	 * @param array $options
	 * @return \stdClass|null
	 */
	protected function getConditionIds( $type, $postfix, array $options ) {
		/* @var $boolean string */
		$boolean = 'OR';
		
		/* @var $key string */
		$key = $type == OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP ? 'filter' : $type;
		
		/* @var $ids array */
		if( null == ( $ids = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_' . $key . '_ids', $postfix ) ) ) ) {
			$boolean = 'AND';
			
			if( null == ( $ids = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_' . $key . '_all_ids', $postfix ) ) ) ) {
				return null;
			}
		}
			
		if( $boolean == 'OR' && ocme()->arr()->get( $options, 'vtype' ) == $type ) {
			$ids = ocme()->arr()->except( $ids, ocme()->arr()->get( $options, 'vid' ) );
		}
		
		return (object) array( 'ids' => $ids, 'boolean' => $boolean );
	}
	
	/**
	 * @param string $postfix
	 * @param array $options
	 * @return \stdClass|null
	 */
	protected function getConditionAttributeIds( $postfix = '', array $options = array() ) {
		return $this->getConditionIds( OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE, $postfix, $options );
	}
	
	/**
	 * @param string $postfix
	 * @param array $options
	 * @return \stdClass|null
	 */
	protected function getConditionOptionIds( $postfix = '', array $options = array() ) {
		return $this->getConditionIds( OcmeFilterCondition::CONDITION_TYPE_OPTION, $postfix, $options );
	}
	
	/**
	 * @param string $postfix
	 * @param array $options
	 * @return \stdClass|null
	 */
	protected function getConditionFilterIds( $postfix = '', array $options = array() ) {
		return $this->getConditionIds( OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP, $postfix, $options );
	}
	
	protected function getConditionOptionTexts( $postfix = '', array $options = array() ) {		
		/* @var $option_texts array */
		if( null != ( $option_texts = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_option_text', $postfix ) ) ) ) {
			if( ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_OPTION ) {
				$option_texts = ocme()->arr()->except( $option_texts, ocme()->arr()->get( $options, 'vid' ) );
			}
		}
		
		return $option_texts;
	}
	
	protected function getConditionOptionValues( $postfix = '', array $options = array() ) {		
		/* @var $option_values array */
		if( null != ( $option_values = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_option_value', $postfix ) ) ) ) {
			if( ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_OPTION ) {
				$option_values = ocme()->arr()->except( $option_values, ocme()->arr()->get( $options, 'vid' ) );
			}
		}
		
		return $option_values;
	}
	
	protected function getConditionOptionValueTexts( $postfix = '', array $options = array() ) {
		/* @var $option_value_texts array */
		if( null != ( $option_value_texts = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_option_value_text', $postfix ) ) ) ) {
			if( ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_OPTION ) {
				$option_value_texts = ocme()->arr()->except( $option_value_texts, ocme()->arr()->get( $options, 'vid' ) );
			}
		}
		
		return $option_value_texts;
	}
	
	protected function getConditionOptionValueRanges( $postfix = '', array $options = array() ) {
		/* @var $ranges array */
		$ranges = array();
		
		/* @var $range_type string */
		foreach( array( 'date', 'time', 'datetime' ) as $range_type ) {
			/* @var $option_value_ranges array */
			if( null != ( $option_value_ranges = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_option_value_' . $range_type . '_range', $postfix ) ) ) ) {
				if( ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_OPTION ) {
					$option_value_ranges = ocme()->arr()->except( $option_value_ranges, ocme()->arr()->get( $options, 'vid' ) );
				}				
				
				if( count( $option_value_ranges ) >= 2 ) {
					$ranges[$range_type] = array_slice( $option_value_ranges, 0, 2 );
				}
			}
		}
		
		return $ranges;
	}
	
	protected function hasAnyOptionConditions( $postfix = '', array $options = array() ) {
		return 
			$this->getConditionOptionIds( $postfix, $options )
				||
			$this->getConditionOptionTexts( $postfix, $options )
				||
			$this->getConditionOptionValues( $postfix, $options )
				||
			$this->getConditionOptionValueTexts( $postfix, $options )
				||
			$this->getConditionOptionValueRanges( $postfix, $options );
	}
	
	public function applyConditionSubQueryOptionIds( callable $sub_query, $postfix = '', array $options = array() ) {	
		/* @var $option_ids \stdClass */
		if( null != ( $option_ids = $this->getConditionOptionIds( $postfix, $options ) ) ) {			
			/* @var $option_id int */
			/* @var $option_value_ids array */
			foreach( $option_ids->ids as $option_id => $option_value_ids ) {
				$sub_query( $option_id, $option_value_ids, $option_ids->boolean );
			}
		}
		
		return $this;
	}
	
	public function applyConditionSubQueryOptionTexts( callable $sub_query, $postfix = '', array $options = array() ) {
		/* @var $option_texts array */
		if( null != ( $option_texts = $this->getConditionOptionTexts( $postfix, $options ) ) ) {			
			/* @var $option_id int */
			/* @var $option_value_texts array */
			foreach( $option_texts as $option_id => $option_value_texts ) {
				$sub_query( $option_id, $option_value_texts );
			}
		}
		
		return $this;
	}
	
	public function applyConditionSubQueryOptionValues( callable $sub_query, $postfix = '', array $options = array() ) {
		/* @var $option_values array */
		if( null != ( $option_values = $this->getConditionOptionValues( $postfix, $options ) ) ) {			
			/* @var $option_id int */
			/* @var $option_value array */
			foreach( $option_values as $option_id => $option_value ) {
				$sub_query( $option_id, $option_value );
			}
		}
		
		return $this;
	}
	
	public function applyConditionSubQueryOptionValueTexts( callable $sub_query, $postfix = '', array $options = array() ) {
		/* @var $option_value_texts array */
		if( null != ( $option_value_texts = $this->getConditionOptionValueTexts( $postfix, $options ) ) ) {			
			/* @var $option_id int */
			/* @var $option_value_text array */
			foreach( $option_value_texts as $option_id => $option_value_text ) {
				$sub_query( $option_id, $option_value_text );
			}
		}
		
		return $this;
	}
	
	public function applyConditionSubQueryOptionValueRanges( callable $sub_query, $postfix = '', array $options = array() ) {
		/* @var $range_type string */
		/* @var $option_value_ranges array */
		foreach( $this->getConditionOptionValueRanges( $postfix, $options ) as $range_type => $option_value_ranges ) {				
			/* @var $option_id int */
			/* @var $option_value_range array */
			foreach( $option_value_ranges as $option_id => $option_value_range ) {
				$sub_query( $option_id, $option_value_range, $range_type );
			}
		}
	}
	
	protected function applyWhereExists( $query, \Closure $callback, $value_ids, $boolean = 'OR' ) {
		if( $boolean == 'OR' ) {
			$query->whereExists(function($q) use( $callback, $value_ids ){
				$callback( $q, $value_ids );
			});
		} else {
			foreach( $value_ids as $value_id ) {
				$query->whereExists(function($q) use( $callback, $value_id ){
					$callback( $q, array( $value_id ) );
				});
			}
		}
	}
	
	protected function applyOptionConditions( $query, $type = null, $postfix = '', array $options = array() ) {
		/* @var $quantity_range array */
		$quantity_range = $this->getQuantityRange($type, $postfix);
		
		$this
			->applyConditionSubQueryOptionIds(function( $option_id, $option_value_ids, $boolean ) use( $query, $quantity_range, $postfix ){
				$this->applyWhereExists( $query, function($q, $option_value_ids) use( $option_id, $quantity_range, $postfix ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_option_value AS `pov`')
						->whereColumn('`p`.product_id', '`pov`.product_id')
						->where('`pov`.option_id', $option_id)
						->whereIn('`pov`.option_value_id', $option_value_ids);

					if( null != ( $availability = $this->getAvailability( $postfix ) ) ) {
						if( in_array( 'in_stock', $availability ) ) {
							$q->where('`pov`.quantity', '>', 0);
						} else if( in_array( 'out_of_stock', $availability ) ) {
							$q->where('`pov`.quantity', '<=', 0);
						}
					} else if( $quantity_range && count( $quantity_range ) >= 2 ) {
						$q->whereBetween('`pov`.quantity', $quantity_range);
					} else if( ocme()->variable()->get('filter_global.configuration.other.only_available_option') == '1' ) {
						$q->where('`pov`.quantity', '>', 0);
					}
				}, $option_value_ids, $boolean );
			}, $postfix, $options)
			->applyConditionSubQueryOptionTexts(function( $option_id, $option_value_texts ) use( $query ){
				$query->whereExists(function($q) use( $option_id, $option_value_texts ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_option_value AS `pov`')
						->leftJoin('option_value_description AS `ovd`', '`ovd`.option_value_id', '=', '`pov`.option_value_id')
						->whereColumn('`p`.product_id', '`pov`.product_id')
						->where('`ovd`.language_id', $this->config_language_id)
						->where('`pov`.option_id', $option_id)
						->where(function($q) use( $option_value_texts ){
							foreach( $option_value_texts as $v ) {
								$q->orWhere('`ovd`.name', 'LIKE', '%' . $v . '%');
							}
						});
				});
			}, $postfix, $options)
			->applyConditionSubQueryOptionValues(function( $option_id, $option_values ) use( $query ){
				$query->whereExists(function($q) use( $option_id, $option_values ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_option AS `po`')
						->whereColumn('`p`.product_id', '`po`.product_id')
						->where('`po`.option_id', $option_id)
						->whereIn('`po`.value', $option_values);
				});
			}, $postfix, $options)
			->applyConditionSubQueryOptionValueTexts(function( $option_id, $option_value_texts ) use( $query ){
				$query->whereExists(function($q) use( $option_id, $option_value_texts ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_option AS `po`')
						->whereColumn('`p`.product_id', '`po`.product_id')
						->where('`po`.option_id', $option_id)
						->where(function($q) use( $option_value_texts ){
							foreach( $option_value_texts as $v ) {
								$q->orWhere('`po`.value', 'LIKE', '%' . $v . '%');
							}
						});
				});
			}, $postfix, $options)
			->applyConditionSubQueryOptionValueRanges(function( $option_id, $option_value_range, $range_type ) use( $query ){
				$query->whereExists(function($q) use( $option_id, $option_value_range, $range_type ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_option AS `po`')
						->whereColumn('`p`.product_id', '`po`.product_id')
						->where('`po`.option_id', $option_id)
						->whereBetween('`po`.v' . $range_type, $option_value_range );
				});
			}, $postfix, $options);
		
		return $this;
	}
	
	protected function applyFilterConditions( $query, $type = null, $postfix = '', array $options = array() ) {		
		/* @var $filter_ids \stdClass|null */
		if( null != ( $filter_ids = $this->getConditionFilterIds( $postfix, $options ) ) ) {
			/* @var $filter_group_id int */
			/* @var $filter_value_ids array */
			foreach( $filter_ids->ids as $filter_group_id => $filter_value_ids ) {
				$this->applyWhereExists( $query, function( $q, $filter_value_ids ) use( $filter_group_id ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_filter AS `pf`')
						->leftJoin('filter AS `f`', '`f`.filter_id', '=', '`pf`.filter_id')
						->whereColumn('`p`.product_id', '`pf`.product_id')
						->where('`f`.filter_group_id', $filter_group_id)
						->whereIn('`pf`.filter_id', $filter_value_ids);
				}, $filter_value_ids, $filter_ids->boolean);
			}
		}
		
		/* @var $filter_texts array */
		if( null != ( $filter_texts = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_filter_text', $postfix ) ) ) ) {
			if( ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP ) {
				$filter_texts = ocme()->arr()->except( $filter_texts, ocme()->arr()->get( $options, 'vid' ) );
			}
			
			/* @var $filter_group_id int */
			/* @var $filter_value_texts array */
			foreach( $filter_texts as $filter_group_id => $filter_value_texts ) {
				$query->whereExists(function($q) use( $filter_group_id, $filter_value_texts ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_filter AS `pf`')
						->leftJoin('filter_description AS `fd`', '`fd`.filter_id', '=', '`pf`.filter_id')
						->whereColumn('`p`.product_id', '`pf`.product_id')
						->where('`fd`.language_id', $this->config_language_id)
						->where('`fd`.filter_group_id', $filter_group_id)
						->where(function($q) use( $filter_value_texts ){
							foreach( $filter_value_texts as $v ) {
								$q->orWhere('`fd`.name', 'LIKE', '%' . $v . '%');
							}
						});
				});
			}
		}
		
		return $this;
	}
	
	protected function applyPriceConditions( $query, $type = null, $postfix = '', array $options = array() ) {
		/* @var $price_range array */
		if( $type != 'price' && null != ( $price_range = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_price', $postfix ) ) ) ) {
			$this->buildPriceColumn(function( $sql, $bindings) use( $price_range, $query ){
				if( ocme()->variable()->get('filter.include_option_prices') ) {
					$query->whereRaw(sprintf('EXISTS(%s)', $sql), $bindings);
				} else {
					$query->whereRaw( $sql . ' BETWEEN ? AND ?', array_merge( $bindings, array_slice( array_values( $price_range ), 0, 2 ) ) );
				}
			}, ocme()->variable()->get('filter.include_option_prices') ? array_replace( $options, array(
				'type' => 'range',
				'range' => $price_range,
				'postfix' => $postfix,
				'buildBaseOptionPriceQuery' => array(
					'alias' => 'pov2',
				),
				'buildOptionPriceSql' => array(
					'alias' => 'pov2',
				)
			)) : $options);
		}
		
		return $this;
	}
	
	protected function applyManufacturerConditions( $query, $type = null, $postfix = '', array $options = array() ) {		
		/* @var $manufacturer_ids array */
		if( $type != 'manufacturer' ) {
			if( null != ( $manufacturer_ids = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_manufacturer_ids', $postfix ) ) ) ) {
				$query->whereIn('`p`.manufacturer_id', $manufacturer_ids);
			}
			
			if( null != ( $manufacturer_texts = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_manufacturer_text', $postfix ) ) ) ) {
				$query->whereExists(function($q) use( $manufacturer_texts ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('manufacturer AS `m`')
						->whereColumn('`p`.manufacturer_id', '`m`.manufacturer_id')
						->whereIn('`m`.name', $manufacturer_texts);
				});
			}
		}
		
		return $this;
	}
	
	protected function applyTagsConditions( $query, $type = null, $postfix = '', array $options = array() ) {		
		/* @var $tags array */
		if( $type != 'tags' && null != ( $tag_ids = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_tags_ids', $postfix ) ) ) ) {
			$query->whereExists(function($q) use( $tag_ids ){
				$q
					->select( ocme()->db()->raw(1) )
					->from('product_to_tag AS `p2t`')
					->whereColumn('`p`.product_id', '`p2t`.product_id')
					->whereIn('`p2t`.product_tag_id', $tag_ids);
			});
		}
		
		return $this;
	}
	
	protected function applyCategoryConditions( $query, $type = null, $postfix = '', array $options = array() ) {		
		if( $type != 'category' && null != ( $filter_category_ids = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_category_ids', $postfix ) ) ) ) {
			if( ! empty( ocme()->arr()->get( self::$filter_data, 'filter_sub_category' ) ) ) {
				$query->whereIn('`cp`.path_id', $filter_category_ids);
			} else {
				$query->whereIn('`p2c`.category_id', $filter_category_ids);
			}
		}
		
		return $this;
	}
	
	protected function applySearchFilter( $query, $filter_name, $filter_tag ) {
		$query->where(function($q) use( $filter_name, $filter_tag ) {
			if( ! empty( $filter_name ) ) {
				$q
					->orWhere(function($q) use( $filter_name ){
						$this->applyAsSeparateWords($q, '`pd`.name', $filter_name);
					})
					->orWhere('`pd`.description', 'LIKE', '%' . $filter_name . '%');

				/* @var $column string */
				foreach( array( 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn' ) as $column ) {
					$q->orWhere('`p`.'.$column, '=', ocme()->str()->lower( $filter_name ));
				}
			}

			if( ! empty( $filter_tag ) ) {
				$q->orWhere(function($q) use( $filter_tag ) {
					$this->applyAsSeparateWords($q, '`pd`.tag', $filter_tag);
				});
			}
		});
		
		return $this;
	}
	
	protected function applySearchConditions( $query, $type = null, $postfix = '', array $options = array() ) {
		if( $type != 'search' ) {
			/* @var $filter_name string */
			if( null === ( $filter_name = ocme()->arr()->get( $options, 'filter_name' ) ) ) {
				/* @var $search array */
				if( null != ( $search = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_search_text', $postfix ) ) ) ) {
					$filter_name = array_shift( $search );
				}
			}
			
			/* @var $filter_tag string */
			$filter_tag = ocme()->arr()->get( $options, 'filter_tag' );
			
			/* @var $keyword string */
			if( ! empty( $filter_name ) || ! empty( $filter_tag ) ) {
				$query->whereExists(function($q) use( $filter_name, $filter_tag ){
					$q
						->select( ocme()->db()->raw(1) )
						->from('product_description AS `pd`')
						->whereColumn('`p`.product_id', '`pd`.product_id')
						->where('`pd`.language_id', $this->config_language_id);
					
					$this->applySearchFilter( $q, $filter_name, $filter_tag );
				});
			}
		}
		
//		if( $type != 'search' && null != ( $search = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_search_text', $postfix ) ) ) ) {
//			/* @var $keyword string */
//			if( null != ( $keyword = array_shift( $search ) ) ) {
//				
//				$query->whereExists(function($q) use( $keyword ){
//					$q
//						->select( ocme()->db()->raw(1) )
//						->from('product_description AS `pd`')
//						->whereColumn('`p`.product_id', '`pd`.product_id')
//						->where('`pd`.language_id', $this->config_language_id)
//						->where(function($q) use( $keyword ) {
//							$q->where(function($q) use( $keyword ){
//								$this->applyAsSeparateWords($q, '`pd`.name', $keyword);
//							});
//
//							$q->orWhere('`pd`.description', 'LIKE', '%' . $keyword . '%');
//
//							/* @var $column string */
//							foreach( array( 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn' ) as $column ) {
//								$q->orWhere('`p`.'.$column, '=', $keyword);
//							}
//						});
//				});
//			}
//		}
		
		return $this;
	}
	
	protected function applyPropertiesConditions( $query, $type = null, $postfix = '', array $options = array() ) {
		
		/* @var $key string */
		foreach( array( 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location' ) as $key ) {
			if( $type != $key ) {
				/* @var $strict bool */
				$strict = true;
				
				if( null == ( $values = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_' . $key, $postfix ) ) ) ) {
					$values = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_' . $key . '_text', $postfix ) );
					$strict = false;
				}
				
				if( null != $values ) {
					$query->where(function( $q ) use( $key, $values, $strict ){
						/* @var $value string */
						foreach( $values as $value ) {
							$q->orWhere('`p`.' . $key, 'LIKE', $strict ? $value : '%' . $value . '%');
						}
					});
				}
			}
		}
		
		/* @var $key string */
		foreach( array( 'length', 'width', 'height', 'weight' ) as $key ) {
			if( $type != $key ) {					
				if( null != ( $values = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_' . $key . '_range', $postfix ) ) ) ) {
					if( count( $values ) >= 2 ) {
						$query->whereBetween('`p`.' . $key, array_slice( $values, 0, 2 ));
					}
				} else if( null != ( $values = ocme()->arr()->get( self::$filter_data, $this->createPostfixName( 'ocme_' . $key, $postfix ) ) ) ) {
					/* @var $val string */
					$val = (string) current( $values );
					
					/* @var $decimals int */
					$decimals = strpos(strrev($val), ".");
					
					/* @var $column string */
					$column = $this->roundingValues( $decimals > 1 ? $decimals . 'd' : 'integer', $this->propertyColumn( $key, $query ) );
					
					if( null == ( $values = $this->{ $decimals > 0 ? 'convertToFloat' : 'convertToInteger' }( $values ) ) ) {
						$query->whereColumn('`p`.' . $key, '!=', '`p`.' . $key);
					} else {
						$query->whereRaw($column . ' IN(?)', array( $values ));
					}
				}
			}
		}
		
		return $this;
	}
	
	protected function applyConditions( $query, $type = null, $postfix = '', array $options = array() ) {
//		/* @var $availability array */
//		$availability = array();
//		
//		if( $type != 'availability' ) {
//			if( null != ( $availability = ocme()->arr()->get( self::$filter_data, 'ocme_availability' . $postfix ) ) && count( $availability ) == 1 ) {
//				if( in_array( 'in_stock', $availability ) ) {
//					$query->where('`p`.quantity', '>', 0);
//				} else if( in_array( 'out_of_stock', $availability ) ) {
//					$query->where('`p`.quantity', '<=', 0);
//				}
//			} 
//		}
		
		return $this
			->applyAttributeCondtions( $query, $type, $postfix, $options )
			->applyQuantityConditions( $query, $type, $postfix, $options )
			->applyOptionConditions( $query, $type, $postfix, $options )
			->applyFilterConditions( $query, $type, $postfix, $options )
			->applyPriceConditions( $query, $type, $postfix, $options )
			->applyManufacturerConditions( $query, $type, $postfix, $options )
			->applyTagsConditions( $query, $type, $postfix, $options )
			->applyCategoryConditions( $query, $type, $postfix, $options )
			->applySearchConditions( $query, $type, $postfix, $options )
			->applyPropertiesConditions( $query, $type, $postfix, $options );
	}
	
	protected function addRelations( $query, $type ) {		
		if( ! empty( self::$filter_data['filter_category_id'] ) || ! empty( self::$filter_data['ocme_category_ids' . $this->postfix()]) ) {
			$query->leftJoin('product_to_category AS `p2c`', '`p2c`.product_id', '=', '`p`.product_id');
			
			if( ! empty( self::$filter_data['filter_sub_category'] ) ) {
				$query->leftJoin('category_path AS `cp`', '`cp`.category_id', '=', '`p2c`.category_id');
			}
		}
		
		return $this;
	}
	
	protected function applySortOrder( $query, $sorts, $sort, $order ) {
		if( ! in_array( ocme()->str()->upper( $order ), array( 'ASC', 'DESC' ) ) ) {
			$order = 'ASC';
		}
		
		if( $sort && isset( $sorts[$sort] ) ) {
			/* @var $column string */
			$column = $sorts[$sort];
			
			switch( $sort ) {
				case 'pd.name' :
				case 'p.model' : {
					$query->orderBy( ocme()->db()->raw( 'LCASE(' . $column . ')' ), $order );
					
					break;
				}
				case 'p.price' : {
					$query->orderBy( ocme()->db()->raw( '(CASE WHEN `special` IS NOT NULL THEN `special` WHEN `discount` IS NOT NULL THEN `discount` ELSE `p`.`price` END)' ), $order );
					
					break;
				}
				default : {
					$query->orderBy( $column, $order );
					
					break;
				}
			}
		} else {
			$query->orderBy('`p`.sort_order', $order);
		}
		
		$query->orderBy( ocme()->db()->raw( 'LCASE(`pd`.`name`)' ), $order )->orderBy( '`p`.product_id' );
		
		return $this;
	}
	
	protected function applyAsSeparateWords( $query, $column, $phrase, $method = 'where' ) {
		/* @var $words array */
		$words = explode(' ', trim(preg_replace('/\s+/', ' ', $phrase)));
		
		/* @var $word string */
		foreach( $words as $word ) {
			$query->{$method}($column, 'LIKE', '%' . $word . '%');
		}
	}
	
	protected function postfix() {
		/* @var $postfix string */
		$postfix = '';
		
		if( 
			ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_conditions' ) 
				|| 
			ocme()->arr()->get( self::$filter_data, 'ocmef_remaining_values' ) 
				|| 
			ocme()->arr()->get( self::$filter_data, 'ocmef_refreshing_values' )
		) {
			$postfix = '_current';
		}
		
		return $postfix;
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function createProductsQuery() {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = null;
		
		if( ! empty( self::$filter_data['filter_category_id'] ) || ! empty( self::$filter_data['ocme_category_ids' . $this->postfix()]) ) {
			if( ! empty( self::$filter_data['filter_sub_category'] ) ) {
				$query = CategoryPath::query()
					->addFromAlias('`cp`')
					->leftJoin('product_to_category AS `p2c`', '`cp`.category_id', '=', '`p2c`.category_id');
			} else {
				$query = ProductToCategory::query()
					->addFromAlias('`p2c`');
			}
			
			if( ! empty( self::$filter_data['filter_filter'] ) ) {
				$query
					->leftJoin('product_filter AS `pf`', '`p2c`.product_id', '=', '`pf`.product_id')
					->leftJoin('product AS `p`', '`pf`.product_id', '=', '`p`.product_id');
			} else {
				$query
					->leftJoin('product AS `p`', '`p2c`.product_id', '=', '`p`.product_id');
			}
		} else {
			$query = Product::query()
				->addFromAlias('`p`');
		}
		
		$query
			->leftJoin('product_description AS `pd`', '`p`.product_id', '=', '`pd`.product_id')
			->leftJoin('product_to_store AS `p2s`', '`p`.product_id', '=', '`p2s`.product_id')
			->where('`pd`.language_id', $this->config_language_id)
			->where('`p2s`.store_id', $this->config_store_id);
		
		return $query;
	}
	
	protected function buildBaseOptionPriceQuery( $query, array $options = array() ) {
		/* @var $alias string */
		$alias = ocme()->arr()->get( $options, 'buildBaseOptionPriceQuery.alias', 'pov' );
		
		$query
			->from('product_option_value AS `' . $alias . '`')
			->whereColumn('`' . $alias . '`.product_id', '`p`.product_id')
			->limit(1);
		
		if( ocme()->variable()->get('filter.include_option_prices') && ocme()->arr()->get( $options, 'vtype' ) == OcmeFilterCondition::CONDITION_TYPE_OPTION ) {
			$query->whereColumn('`' . $alias . '`.product_option_value_id', '`pov`.product_option_value_id');
		}
		
		if( ocme()->variable()->get('filter.include_option_prices') == 'only_available' ) {
			$query->where('`' . $alias . '`.quantity', '>', 0);
		}
		
		$query->where(function($q) use( $options, $alias, $query ) {			
			$this
				->applyConditionSubQueryOptionIds(function( $option_id, $option_value_ids ) use( $q, $alias ){
					$q->orWhere(function($q) use( $option_id, $option_value_ids, $alias ){
						$q->where('`' . $alias . '`.option_id', $option_id)->whereIn('`' . $alias . '`.option_value_id', $option_value_ids);
					});
				}, ocme()->arr()->get( $options, 'postfix' ))
						
				->applyConditionSubQueryOptionTexts(function( $option_id, $option_value_texts ) use( $query, $q, $alias ){
					$query
						->leftJoin('option_value_description AS `ovd`', '`ovd`.option_value_id', '=', '`' . $alias . '`.option_value_id')
						->where('`ovd`.language_id', $this->config_language_id);
			
					$q
						//->leftJoin('option_value_description AS `ovd`', '`ovd`.option_value_id', '=', '`' . $alias . '`.option_value_id')
						//->where('`ovd`.language_id', $this->config_language_id)
						->where(function($q) use( $option_id, $option_value_texts, $alias ){
							$q
								->orWhere(function($q) use( $option_id, $option_value_texts, $alias ){
									$q->where('`' . $alias . '`.option_id', $option_id)->where(function($q) use( $option_value_texts ){
										foreach( $option_value_texts as $v ) {
											$q->orWhere('`ovd`.name', 'LIKE', '%' . $v . '%');
										}
									});
								});
						});
				}, ocme()->arr()->get( $options, 'postfix' ))
						
				->applyConditionSubQueryOptionValues(function( $option_id, $option_values ) use( $q ){
					$q->orWhere(function($q) use( $option_id, $option_values ) {
						$q->where('`po`.option_id', $option_id)->whereIn('`po`.value', $option_values);
					});
				}, ocme()->arr()->get( $options, 'postfix' ))
						
				->applyConditionSubQueryOptionValueTexts(function( $option_id, $option_value_texts ) use( $q ){
					$q->orWhere(function($q) use( $option_id, $option_value_texts ){
						$q->where('`po`.option_id', $option_id)->where(function($q) use( $option_value_texts ){
							foreach( $option_value_texts as $v ) {
								$q->orWhere('`po`.value', 'LIKE', '%' . $v . '%');
							}
						});
					});
				}, ocme()->arr()->get( $options, 'postfix' ))
						
				->applyConditionSubQueryOptionValueRanges(function( $option_id, $option_value_range, $range_type ) use( $q ){
					$q->orWhere(function($q) use( $option_id, $option_value_range, $range_type ){
						$q->where('`po`.option_id', $option_id)->whereBetween('`po`.v' . $range_type, $option_value_range );
					});
				}, ocme()->arr()->get( $options, 'postfix' ));
		});
		
		return $query;
	}
	
	public function buildOptionPriceQuery( $type, $query, array $options = array() ) {
		$this->buildBaseOptionPriceQuery( $query, $options )
			->selectRaw($type . "( IF(`price_prefix` = '+', `pov`.`price`, -`pov`.`price`) ) AS `opv_price`")
			->orderBy('opv_price', $type == 'MIN' ? 'ASC' : 'DESC');
		
		return $query;
	}
	
	public function buildOptionPriceSql( $sql, $range, $query, array $options = array() ) {
		/* @var $alias string */
		$alias = ocme()->arr()->get( $options, 'buildOptionPriceSql.alias', 'pov' );
		
		$this->buildBaseOptionPriceQuery( $query, $options )
			->selectRaw(1)
			->whereRaw(sprintf("( ( %s ) + IF(`price_prefix` = '+', `" . $alias . "`.`price`, -`" . $alias . "`.`price`) ) BETWEEN ? AND ?", $sql), array_values( $range ))
			->orderBy('price', 'ASC');
		
		return ocme()->db()->queryToRawSql( $query );
	}
	
	protected function buildSpecialColumnQuery( $query ) {
		return $query
			->select('price')
			->from('product_special AS `ps`')
			->whereColumn('`ps`.product_id', '`p`.product_id')
			->where('`ps`.customer_group_id', $this->config_customer_group_id)
			->where(function($q){
				$q->where('`ps`.date_start', '0000-00-00')->orWhere('`ps`.date_start', '<', ocme()->db()->raw('NOW()'));
			})
			->where(function($q){
				$q->where('`ps`.date_end', '0000-00-00')->orWhere('`ps`.date_end', '>', ocme()->db()->raw('NOW()'));
			})
			->orderBy('`ps`.priority', 'ASC')
			->orderBy('`ps`.price', 'ASC')
			->limit(1);
	}
	
	protected function buildRatingColumnQuery( $query ) {
		return $query
			->selectRaw('AVG(`rating`) AS `total`')
			->from('review AS `r1`')
			->whereColumn('`r1`.product_id', '`p`.product_id')
			->where('`r1`.status','1')
			->groupBy('`r1`.product_id');
	}
	
	protected function buildDiscountColumnQuery( $query ) {
		return $query
			->select('price')
			->from('product_discount AS `pd2`')
			->whereColumn('`pd2`.product_id', '`p`.product_id')
			->where('`pd2`.customer_group_id', $this->config_customer_group_id)
			->where('`pd2`.quantity', '1')
			->where(function($q){
				$q->where('`pd2`.date_start', '0000-00-00')->orWhere('`pd2`.date_start', '<', ocme()->db()->raw('NOW()'));
			})
			->where(function($q){
				$q->where('`pd2`.date_end', '0000-00-00')->orWhere('`pd2`.date_end', '>', ocme()->db()->raw('NOW()'));
			})
			->orderBy('`pd2`.priority', 'ASC')
			->orderBy('`pd2`.price', 'ASC')
			->limit(1);
	}
	
	public function buildTaxColumnQuery( $query, $type, $tax_class_id = null ) {
		$query
			->select('`tr2`.rate')
			->from('tax_rule AS `tr1`')
			->leftJoin('tax_rate AS `tr2`', '`tr1`.tax_rate_id', '=', '`tr2`.tax_rate_id')
			->leftJoin('tax_rate_to_customer_group AS `tr2cg`', '`tr2`.tax_rate_id', '=', '`tr2cg`.tax_rate_id')
			->leftJoin('zone_to_geo_zone AS `z2gz`', '`tr2`.geo_zone_id', '=', '`z2gz`.geo_zone_id')
			->where('`tr2`.type', $type)
			->where('`tr2cg`.customer_group_id', $this->config_customer_group_id)
			->limit(1);
					
		if( is_null( $tax_class_id ) ) {
			$query->whereColumn('`tr1`.tax_class_id', '`p`.tax_class_id');
		} else {
			$query->where('`tr1`.tax_class_id', $tax_class_id);
		}
		
		return $this->applyTaxConditions( $query );
	}
	
	protected function applyTaxConditions( $query ) {
		/* @var $conditions array */
		$conditions = array(
			'store' => array(
				'country_id' => (int) ocme()->oc()->registry()->get('config')->get('config_country_id'),
				'zone_id' => (int) ocme()->oc()->registry()->get('config')->get('config_zone_id'),
				'types' => array(),
			)
		);
		
		/* @var $type string */
		foreach( array( 'payment', 'shipping' ) as $type ) {
			/* @var $key string */
			foreach( array( 'country_id', 'zone_id' ) as $key ) {
				if( null == ( $conditions[$type][$key] = (int) ocme()->arr()->get( ocme()->oc()->registry()->get('session')->data, $type . '_' . $key ) ) ) {
					$conditions[$type][$key] = $conditions['store'][$key];
				}
			}
			
			if( $conditions[$type]['country_id'] == $conditions['store']['country_id'] && $conditions[$type]['zone_id'] == $conditions['store']['zone_id'] ) {
				$conditions['store']['types'][] = $type;
				
				unset( $conditions[$type] );
			}
		}
		
		return $query->where(function($q) use( $conditions ) {
			foreach( $conditions as $type => $condition ) {
				$q->orWhere(function($q) use( $type, $condition ){
					if( $condition['types'] ) {
						$q->whereIn('`tr1`.based', array_merge( array( $type ), $condition['types'] ));
					} else {
						$q->where('`tr1`.based', $type);
					}
		
					$q->where('`z2gz`.country_id', $condition['country_id'])->whereIn('`z2gz`.zone_id', array( 0, $condition['zone_id'] ));
				});
			}
		});
	}
	
	/**
	 * @param array|null $filter_data
	 */
	public function initializeData( array $filter_data = array() ) {
		// prevent double initialization for ajax request
		if( ocme()->ajaxRendering() && self::$filter_data ) {
			return $this;
		}
		
		self::$filter_data = $filter_data;
		self::$pagination = null;
		
		/* @var $type string */
		foreach( array( '', '_current' ) as $type ) {
			if( self::${'url_parameters'.$type} ) {
				/* @var $url_parameter array */
				foreach( self::${'url_parameters'.$type} as $url_parameter ) {
					/* @var $name string */
					$name = ocme()->arr()->get( $url_parameter, 'name' );

					/* @var $key string|null */
					$key = null;

					/* @var $values array|null */
					$values = ocme()->arr()->get( $url_parameter, 'values' );

					switch( $name ) {
						case 'attribute':
						case 'option':
						case 'filter': {
							if( $values ) {
								$key = $name . '_ids';
								$values = array_unique( $this->convertToInteger( $values ) );
							}

							/* @var $range array */
							if( null != ( $range = ocme()->arr()->get( $url_parameter, 'integer_range' ) ) ) {
								$range = $this->convertToInteger( $range );
								$key = 'integer';
							} else if( null != ( $range = ocme()->arr()->get( $url_parameter, 'float_range' ) ) ) {
								$range = $this->convertToFloat( $range );
								$key = 'float';
							} else 
							/* @var $texts array */
							if( null != ( $texts = ocme()->arr()->get( $url_parameter, 'text' ) ) ) {
								$values = $texts;
								$key = $name . '_text';
							} else
							/* @var $all_values array */
							if( null != ( $all_values = ocme()->arr()->get( $url_parameter, 'all_values' ) ) ) {
								$values = $all_values;
								$key = $name . '_all_ids';
							} else if( $name == 'option' ) {
								foreach( array( 'value', 'value_text', 'value_date_range', 'value_time_range', 'value_datetime_range' ) as $key_name ) {
									/* @var $key_values array */
									if( null != ( $key_values = ocme()->arr()->get( $url_parameter, 'option_' . $key_name ) ) ) {
										$values = $key_values;
										$key = $name . '_' . $key_name;
									}
								}
							}

							if( $range ) {
								if( count( $range ) == 2 ) {
									self::$filter_data['ocme_' . $name . '_' . $key . '_range' . $type][$url_parameter['id']] = $range;
								}

								$key = null;
							} else {
								self::$filter_data['ocme_' . $key . $type][$url_parameter['id']] = $values;

								$key = null;
							}

							break;
						}
						case 'sku':
						case 'upc':
						case 'ean':
						case 'jan':
						case 'isbn':
						case 'mpn':
						case 'location':
						case 'model': {	
							$key = $name;
							
							/* @var $texts array */
							if( null != ( $texts = ocme()->arr()->get( $url_parameter, 'text' ) ) ) {
								self::$filter_data['ocme_' . $name . '_text' . $type] = $texts;

								$key = null;
							}

							break;
						}
						case 'manufacturer': {							
							if( $values ) {
								$key = $name . '_ids';
								$values = array_unique( $this->convertToInteger( $values ) );
							} else 						
							/* @var $texts array */
							if( null != ( $texts = ocme()->arr()->get( $url_parameter, 'text' ) ) ) {
								self::$filter_data['ocme_' . $name . '_text' . $type] = $texts;

								$key = null;
							}

							break;
						}
						case 'search': {
							/* @var $texts array */
							if( null != ( $texts = ocme()->arr()->get( $url_parameter, 'text' ) ) ) {
								self::$filter_data['ocme_' . $name . '_text' . $type] = $texts;

								$key = null;
							}

							break;
						}
						case 'category': {
							$key = $name . '_ids';
							$values = array_unique( $this->convertToInteger( $values ) );

							break;
						}
						case 'tags': {
							$key = 'tags_ids';
							$values = array_unique( $this->convertToInteger( $values ) );

							break;
						}
						case 'price': {
							if( count( $values ) == 2 ) {
								$key = $name;
								$values = array( 'min' => (float) $values[0], 'max' => (float) $values[1] );
							}

							break;
						}
						case 'quantity' :
						case 'availability':
						case 'length':
						case 'width':
						case 'height':
						case 'weight': {
							$key = $name;

							/* @var $range array */
							if( null != ( $range = ocme()->arr()->get( $url_parameter, 'integer_range' ) ) ) {
								$values = $this->convertToInteger( $range );
								$key .= '_range';
							} else if ( null != ( $range = ocme()->arr()->get( $url_parameter, 'float_range' ) ) ) {
								$values = $this->convertToFloat( $range );
								$key .= '_range';
							}

							break;
						}
					}

					if( ! is_null( $key ) && ! is_null( $values ) ) {
						$key = 'ocme_' . $key . $type;

						if( ! isset( self::$filter_data[$key] ) ) {
							self::$filter_data[$key] = array();
						}

						self::$filter_data[$key] = array_merge( self::$filter_data[$key], $values );
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @return array|null
	 */
	public function filterData() {
		return self::$filter_data;
	}
	
	/**
	 * @return string
	 */
	public function getFilterData() {
		return http_build_query( ocme()->collection()->make( self::$filter_data )->filter(function( $v, $k ){
			if( $v === '' ) {
				return false;
			}
			
			if( ocme()->str()->startsWith( $k, array( 'filter_' ) ) ) {
				return true;
			}
			
			return false;
		})->all());
	}
	
}