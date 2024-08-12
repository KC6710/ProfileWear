<?php namespace Ocme\OpenCart\Admin\Controller;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use Ocme\Model\Module as ModuleModel,
	Ocme\Model\OcmeFilterCondition,
	Ocme\Model\OcmeFilterProperty,
	Ocme\Model\OcmeFilterPropertyValue,
	Ocme\Model\OcmeFilterPropertyValueToProduct,
	Ocme\Model\Attribute as AttributeModel,
	Ocme\Model\AttributeValue,
	Ocme\Model\OcmeFilterGrid,
	Ocme\Model\OcmeFilterGridCondition,
	Ocme\Model\Option,
	Ocme\Model\OptionValue,
	Ocme\Model\ProductOption,
	Ocme\Model\FilterGroup,
	Ocme\Model\Product,
	Ocme\Model\ProductAttributeValue,
	Ocme\Model\ProductAttribute,
	Ocme\Model\ProductTag,
	Ocme\Model\ProductToTag,
	Ocme\Model\ProductDescription,
	Ocme\Model\OcmeVariable;

trait OcmeMfpFilter {
	
	use \Ocme\OpenCart\Admin\Controller;
	use \Ocme\OpenCart\Admin\Traits\OcmeMfp;
	use \Ocme\OpenCart\Admin\Traits\Module;
	use \Ocme\Support\Traits\Minify;
	
	protected function initTrait() {
		$this->name = 'extension/module/ocme_mfp_filter';
		$this->path = 'extension/module/ocme_mfp';
		
		$this->cache_path_js = 'view/ocme/javascript/cache';
		$this->cache_path_css = 'view/ocme/stylesheet/cache';
	}
	
	public function index() {
		if( ! defined('OCME_MFP_IS_READY') ) {
			die('The main module Mega Filter Pack has not been installed. Please go back to the list of modules and click the button INSTALL for it.');
		}
		
		$this->checkInstallation();
		
		$this->initialize()->module( 'filter', function(){
			$this->data['https_catalog'] = defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG;
			$this->data['ocme_mfp_editable'] = $this->validateAccess('modify',false)?'true':'false';
			$this->data['ocme_mfp_module_id'] = ocme()->request()->query('module_id');

			$this->data['ocme_mfp_license'] = base64_encode(json_encode(array_replace( ocme()->license()->shopData(), array(
				'token' => ocme()->license()->token(),
				'version' => ocme()->version(),
			))));
		
			$this->data['action_autocomplete'] = ocme()->url()->adminLink($this->name.'/autocomplete');
			$this->data['base_attributes_names'] = str_replace( '"', "'", json_encode( ocme()->model('filter')->filterBaseConditions('base_attributes.names') ) );
			$this->data['properties_names'] = str_replace( '"', "'", json_encode( ocme()->model('filter')->filterBaseConditions('properties.names') ) );
			$this->data['ocme_oc_version'] = defined( 'VERSION' ) ? VERSION : '';
			$this->data['ocme_version'] = ocme()->version();
		});
	}
	
	protected function createGrid( $module_id ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = OcmeFilterCondition::select(array(
				'`ofc`.id AS ocme_filter_condition_id',
				'`ofc`.condition_type',
				'`ofc`.name',
				'`ofgc`.id',
				'`ofgc`.ocme_filter_grid_id',
				ocme()->db()->raw( 'IF(`a`.`attribute_id` IS NULL, `ofc`.`record_id`, `a`.`attribute_id` ) AS `vid`' ),
				ocme()->db()->raw( 'IF(`ofc`.`condition_type` = "attribute_group", "attribute", `ofc`.`condition_type`) AS `vtype`'),
				ocme()->db()->raw( 'IF( `ad`.`name` IS NULL, IF( `od`.`name` IS NULL, IF( `fgd`.`name` IS NULL, `ad2`.`name`, `fgd`.`name` ), `od`.`name` ), `ad`.`name` ) AS `label`' ),
			))
			->addFromAlias('`ofc`')
			->leftJoin('attribute AS `a`', function($q){
				$q->on('`ofc`.record_id', '=', '`a`.attribute_group_id')
					->where('`ofc`.condition_type', '=', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP);
			})
			->leftJoin('ocme_filter_grid AS `ofg`', function($q){
				$q
					->on('`ofg`.module_id', '=', '`ofc`.module_id')
					->where('`ofg`.type', '=', 'column');
			})
			->leftJoin('ocme_filter_grid_condition AS `ofgc`', function($q){
				$q
					->on('`ofgc`.ocme_filter_grid_id', '=', '`ofg`.id')
					->on('`ofgc`.vtype', '=', ocme()->db()->raw('IF(`ofc`.`condition_type` = "attribute_group", "attribute", `ofc`.`condition_type`)'))
					->on(ocme()->db()->raw('( ( `ofgc`.`vid`'), 'IS', ocme()->db()->raw('NOT NULL AND `ofgc`.`vid` = IF(`a`.`attribute_id` IS NULL, `ofc`.`record_id`, `a`.`attribute_id` ) ) OR ( `ofgc`.`vname` IS NOT NULL AND `ofgc`.`vname` = `ofc`.`name` ) )'));
			})
			->leftJoin('attribute_description AS `ad`', function($q){
				$q
					->on('`ad`.attribute_id', '=', '`ofc`.record_id')
					->where('`ad`.language_id', '=', ocme()->ocRegistry()->get('config')->get('config_language_id'))
					->where('`ofc`.condition_type', '=', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE);
			})
			->leftJoin('attribute_description AS `ad2`', function($q){
				$q
					->on('`ad2`.attribute_id', '=', '`a`.attribute_id')
					->where('`ad2`.language_id', '=', ocme()->ocRegistry()->get('config')->get('config_language_id'))
					->where('`ofc`.condition_type', '=', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP);
			})
			->leftJoin('option_description AS `od`', function($q){
				$q
					->on('`ofc`.record_id', '=', '`od`.option_id')
					->where('`od`.language_id', '=', ocme()->ocRegistry()->get('config')->get('config_language_id'))
					->where('`ofc`.condition_type', '=', OcmeFilterCondition::CONDITION_TYPE_OPTION);
			})
			->leftJoin('filter_group_description AS `fgd`', function($q){
				$q
					->on('`ofc`.record_id', '=', '`fgd`.filter_group_id')
					->where('`fgd`.language_id', '=', ocme()->ocRegistry()->get('config')->get('config_language_id'))
					->where('`ofc`.condition_type', '=', OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP);
			})
			->where(function($q){
				$q->whereNull('`ofgc`.vtype')->orWhere('`ofgc`.vtype', '=', ocme()->db()->raw('IF(`ofc`.`condition_type` = "attribute_group", "attribute", `ofc`.`condition_type`)'));
			})
			->where('`ofc`.module_id', $module_id)
			->where('`ofc`.status', '1')
			->groupBy(ocme()->db()->raw(
				'CONCAT(IF(`ofc`.`condition_type` = "attribute_group", "attribute", `ofc`.`condition_type`), ":", IF(
					`ofc`.`condition_type` IN("attribute","option","filter_group"), `ofc`.`record_id`, IF(
						`ofc`.`condition_type` = "attribute_group", `a`.`attribute_id`, `ofc`.`name`
					)
				))'
			))
			->orderBy(ocme()->db()->raw('IF(`ofgc`.`id` IS NULL, 1, 0)'))
			->orderBy(ocme()->db()->raw('IF(`ofgc`.`sort_order` IS NULL, IF(`ofc`.`sort_order` IS NULL, 0, `ofc`.`sort_order`), `ofgc`.`sort_order`)'))
			->with(array(
				'attribute' => function( $q ){
					$q->withDescription();
				},
			));

		/* @var $conditions array */
		$lsb_conditions = array();
		
		/* @var $grid array */
		$lsb_grid = array();

		/* @var $total int */
		$total = ocme()->arr()->get( ocme()->db()->connection()->selectOne('SELECT COUNT(*) AS `total` FROM(' . ocme()->db()->queryToRawSql( $query ) . ') AS `tmp`'), 'total' );
		
		if( $total <= 100 ) {
			/* @var $ocme_filter_condition OcmeFilterCondition */
			foreach( $query->get() as $ocme_filter_condition ) {
				/* @var $key string */
				$key = $ocme_filter_condition->vtype;
				
				if( ! is_null( $ocme_filter_condition->vid ) ) {
					$key .= ':' . $ocme_filter_condition->vid;
				} else if( ! is_null( $ocme_filter_condition->name ) ) {
					$key .= ':' . $ocme_filter_condition->name;
				}

				if( $ocme_filter_condition->condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP ) {
					$key = OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE . ':' . $ocme_filter_condition->vid;
				}

				$lsb_conditions[] = array(
					'id' => $ocme_filter_condition->id,
					'ocme_filter_grid_id' => $ocme_filter_condition->ocme_filter_grid_id,
					'ocme_filter_condition_id' => $ocme_filter_condition->ocme_filter_condition_id,
					'vid' => $ocme_filter_condition->vid,
					'vtype' => $ocme_filter_condition->vtype,
					'vname' => $ocme_filter_condition->name,
					'condition_type' => $ocme_filter_condition->condition_type,
					'type' => $ocme_filter_condition->type,
					'label' => is_null( $ocme_filter_condition->label ) ? ocme()->trans('module::global.text_' . $ocme_filter_condition->name) : $ocme_filter_condition->label,
					'key' => $key,
				);
			}

			foreach( OcmeFilterGrid::where('module_id', $module_id )->orderBy('sort_order')->get() as $ocme_filter_grid ) {
				$lsb_grid[] = array(
					'id' => $ocme_filter_grid->id,
					'parent_id' => $ocme_filter_grid->parent_id,
					'type' => $ocme_filter_grid->type,
					'settings' => $ocme_filter_grid->settings,
				);
			}
		} else {
			return false;
		}
		
		return compact( 'lsb_grid', 'lsb_conditions' );
	}
	
	protected function saveGrid( $module_id, array $rows, $parent_id = null ) {
		/* @var $ocme_filter_grid_ids array */
		$ocme_filter_grid_ids = array();
		
		/* @var $ocme_filter_grid_condition_ids array */
		$ocme_filter_grid_condition_ids = array();
		
		/* @var $lsb_conditions_keys array */
		$lsb_conditions_keys = array();
		
		/* @var $grid array */
		if( null != ( $grid = $this->createGrid( $module_id ) ) ) {
			$lsb_conditions_keys = array_map(function( $v ){
				return $v['key'];
			}, $grid['lsb_conditions']);
		}
		
		/* @var $row array */
		foreach( $rows as $sort_order => $row ) {
			/* @var $ocme_filter_row OcmeFilterGrid */
			$ocme_filter_row = null;
			
			/* @var $rid int */
			if( null != ( $rid = ocme()->arr()->get( $row, 'id' ) ) ) {
				$ocme_filter_row = OcmeFilterGrid::find( $rid );
			} else {
				$ocme_filter_row = OcmeFilterGrid::create(array(
					'module_id' => $module_id,
					'parent_id' => $parent_id,
					'type' => OcmeFilterGrid::TYPE_ROW,
				));
			}
			
			if( $ocme_filter_row ) {
				$ocme_filter_grid_ids[] = $ocme_filter_row->id;
				
				$ocme_filter_row->fill(array(
					'sort_order' => $sort_order,
				))->save();

				foreach( ocme()->arr()->get( $row, 'columns' ) as $sort_order => $column ) {
					/* @var $ocme_filter_column OcmeFilterGrid */
					$ocme_filter_column = null;

					/* @var $cid int */
					if( null != ( $cid = ocme()->arr()->get( $column, 'id' ) ) ) {
						$ocme_filter_column = OcmeFilterGrid::find( $cid );
					} else {
						$ocme_filter_column = OcmeFilterGrid::create(array(
							'module_id' => $module_id,
							'parent_id' => $ocme_filter_row->id,
							'type' => OcmeFilterGrid::TYPE_COLUMN,
						));
					}
					
					if( $ocme_filter_column ) {
						$ocme_filter_grid_ids[] = $ocme_filter_column->id;
						
						$ocme_filter_column->fill(array(
							'sort_order' => $sort_order,
							'settings' => array_filter(ocme()->arr()->only( $column, array(
								'cols', 'offsets',
							)), function( $v ){
								return (bool) $v;
							})
						))->save();

						/* @var $condition array */
						foreach( ocme()->arr()->get( $column, 'conditions' ) as $sort_order => $condition ) {
							/* @var $ocme_filter_grid_condition OcmeFilterGridCondition */
							$ocme_filter_grid_condition = null;

							/* @var $gcid int */
							if( null != ( $gcid = ocme()->arr()->get( $condition, 'id' ) ) ) {
								if( in_array( ocme()->arr()->get( $condition, 'key' ), $lsb_conditions_keys ) ) {
									$ocme_filter_grid_condition = OcmeFilterGridCondition::find( $gcid );
								}
							}
							
							if( ! $ocme_filter_grid_condition && in_array( ocme()->arr()->get( $condition, 'key' ), $lsb_conditions_keys ) ) {
								$ocme_filter_grid_condition = OcmeFilterGridCondition::create(array(
									'ocme_filter_condition_id' => ocme()->arr()->get( $condition, 'ocme_filter_condition_id' ),
									'vid' => ocme()->arr()->get( $condition, 'vid' ),
									'vtype' => ocme()->arr()->get( $condition, 'vtype' ),
									'vname' => ocme()->arr()->get( $condition, 'vname' ),
									'ocme_filter_grid_id' => $ocme_filter_column->id,
									'sort_order' => $sort_order,
								));
							}

							if( $ocme_filter_grid_condition ) {
								$ocme_filter_grid_condition_ids[] = $ocme_filter_grid_condition->id;
								
								$ocme_filter_grid_condition->fill(array(
									'ocme_filter_grid_id' => $ocme_filter_column->id,
									'sort_order' => $sort_order,
								))->save();
							}
						}
					}

					/* @var $crows array */
					if( null != ( $crows = ocme()->arr()->get( $column, 'rows' ) ) ) {
						$this->saveGrid( $module_id, $crows, $ocme_filter_column->id );
					}
				}
			}
		}
		
		/* @var $del_query_grid \Illuminate\Database\Eloquent\Builder */
		$del_query_grid = OcmeFilterGrid::where('module_id', $module_id);
		
		if( $ocme_filter_grid_ids ) {
			$del_query_grid->whereNotIn( 'id', $ocme_filter_grid_ids );
		}
		
		$del_query_grid->delete();
		
		if( $ocme_filter_grid_ids ) {
			/* @var $del_query_grid_condition \Illuminate\Database\Eloquent\Builder */
			$del_query_grid_condition = OcmeFilterGridCondition::whereIn('ocme_filter_grid_id', $ocme_filter_grid_ids);
		
			if( $ocme_filter_grid_condition_ids ) {
					$del_query_grid_condition->whereNotIn('id', $ocme_filter_grid_condition_ids);
			}
			
			$del_query_grid_condition->delete();
		}
		
		foreach( OcmeFilterGridCondition::addFromAlias('`ofgc`')
			->whereNotExists(function($q) {
				$q
					->select(ocme()->db()->raw(1))
					->from('ocme_filter_grid AS `ofg`')
					->whereColumn('`ofg`.id', '=', '`ofgc`.ocme_filter_grid_id');
			})
			->get() as $ocme_filter_grid_condition 
		) {
			$ocme_filter_grid_condition->delete();
		}
	}
	
	public function config() {
		$this->initialize()->module_config( ModuleModel::CODE_FILTER, array(
			'data' => array(
				'has_any_to_re_index' => $this->hasAnyToReIndex(),
			)
		));
	}
	
	public function indexation_action() {		
		/* @var $response array */
		$response = array(
			'status' => 'success',
			'data' => array(),
		);
		
		/* @var $config array */
		$config = array(
			'limit' => 100,
		);
		
		/* @var $actions array */
		$actions = (array) ocme()->request()->input('actions');
		
		if( in_array( 'remove_data', $actions ) && $this->validateAccess() ) {
			/* @var $type string */
			$type = ocme()->request()->input('type');
			
			switch( $type ) {
				case 'product' : {
					Product::whereNotNull('ocme_filter_indexed_at')->update(array(
						'ocme_filter_indexed_at' => null,
					));
					
					break;
				}
				case 'attribute' : {					
					OcmeFilterProperty::whereNotNull('attribute_id')->delete();
					OcmeFilterPropertyValue::whereNotNull('attribute_id')->delete();
					OcmeFilterPropertyValueToProduct::whereNotNull('attribute_id')->delete();
					
					ProductAttributeValue::query()->delete();
					
					break;
				}
				case 'tags' : {					
					ProductTag::query()->delete();
					ProductToTag::query()->delete();
					
					break;
				}
			}
		}
		
		if( in_array( 'reindex', $actions ) && $this->validateAccess() ) {
			/* @var $type string */
			$type = ocme()->request()->input('type');
			
			/* @var $params array */
			$params = (array) ocme()->request()->input('params');
			
			/* @var $reindex string|null */
			if( null != ( $reindex = ocme()->arr()->get( $params, 'reindex' ) ) ) {
				if( ! in_array( $reindex, array(
						'AttributeModel::missingOcmeFilterProperty',
						'AttributeValue::missingOcmeFilterPropertyValue',
					
						'ProductOption::missing',
					
						'ProductAttribute::missing',
						'ProductAttributeValue::missing',
						'ProductAttributeValue::missingOcmeFilterPropertyValueToProduct',
					))
				) {
					$reindex = null;
				}
			}
			
			/* @var $page int */
			if( 1 > ( $page = (int) ocme()->request()->input('page') ) ) {
				$page = 1; 
			}
			
			/* @var $query \Illuminate\Database\Eloquent\Builder */
			$query = null;
				
			/* @var $limit int */
			$limit = ocme()->arr()->get( $config, 'limit' );
			
			switch( $type ) {
				case 'product' : {
					$query = Product::query();
					
					break;
				}
				case 'tags' : {
					if( $page == 1 ) {
						ProductToTag::redundant()->delete();
					}

					/* @var $product_description ProductDescription */
					foreach( ProductDescription::missingTags()->limit( $limit )->get() as $product_description ) {
						$product_description->reIndexTags();
					}
					
					break;
				}
			}
			
			if( $reindex && in_array( $type, array( OcmeFilterProperty::TYPE_ATTRIBUTE, OcmeFilterProperty::TYPE_FILTER_GROUP, OcmeFilterProperty::TYPE_OPTION ) ) ) {
				/* @var $class string */
				$class = '\\Ocme\\Model\\' . substr( $reindex, 0, strpos( $reindex, ':' ) );
				
				/* @var $method string */
				$method = substr( $reindex, strrpos( $reindex, ':' )+1 );
				
				switch( $method ) {
					case 'missing' : {
						if( $page == 1 ) {
							$class::redundant()->delete();
						}
						
						/* @var $class string */
						$class = '\\Ocme\\Model\\' . $reindex;
				
						foreach( call_user_func( $class )->missing()->limit( $limit )->get() as $item ) {
							$item->reCreate();
						}
						
						break;
					}
					case 'missingOcmeFilterPropertyValueToProduct' : {
						if( $page == 1 ) {
							$class::redundant()->delete();
							
							OcmeFilterPropertyValueToProduct::redundant( $type )->delete();
						}
						
						OcmeFilterPropertyValueToProduct::reIndex( $limit, $type );
						
						break;
					}
					case 'missingOcmeFilterPropertyValue' : {
						/* @var $property_type string */
						$property_type = ocme()->str()->snake( basename( str_replace('\\', '/', substr( $reindex, 0, strpos( $reindex, ':' ) ) ) ) );
						
						if( $page == 1 ) {
							$class::redundant()->delete();
							
							OcmeFilterPropertyValue::redundant( $property_type )->delete();
						}
						
						OcmeFilterPropertyValue::reIndex( $limit, $property_type );
						
						break;
					}
					case 'missingOcmeFilterProperty' : {
						if( $page == 1 ) {
							OcmeFilterProperty::redundant( $type )->delete();
						}
						
						OcmeFilterProperty::reIndex( $limit, $type );
						
						break;
					}
				}
			} else if( $query ) {
				if( $type == 'product' ) {
					if( ocme()->arr()->get( $params, 'only_new' ) ) {
						$query->where(function($q){
							$q->whereNull('ocme_filter_indexed_at')->orWhereColumn('ocme_filter_indexed_at', '<', 'date_modified');
						});
					} else {
						if( ocme()->arr()->get( 'params', 'first_new' ) ) {
							$query->orderBy(ocme()->db()->raw('IF(ocme_filter_indexed_at < date_modified OR ocme_filter_indexed_at IS NULL, 0, 1)'));
						}
						
						$query->offset( ocme()->arr()->get( $config, 'limit' ) * ( $page - 1 ) );
					}
				}
				
				try {
					/* @var $item Attribute|Option|FilterGroup|AttributeValue|OptionValue|Filter */
					foreach( $query->limit( ocme()->arr()->get( $config, 'limit' ) )->get() as $item ) {
						switch( $type ) {
							case 'product' : {
								ocme()->model('filter')->reIndexProduct( $item->product_id );

								break;
							}
						}
					}
				} catch( \Exception $e ) {
					$response['status'] = 'error';
					$response['error'] = array(
						'msg' => 'Cannot continue indexing because your database does not contain consistent data. Please check the associations between the records and remove relationships that do not exist.',
						'details' => $e->getMessage()
					);
				}
			}
		}
		
		if( in_array( 'get_config', $actions ) ) {
			$response['data']['config'] = $config;
		}
		
		if( in_array( 'get_panels', $actions ) ) {
			$response['data']['panels'] = $this->panels();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode( $response ));
	}
	
	private function hasAnyToReIndex() {
		foreach( $this->_panels() as $panel ) {
			foreach( $panel['items'] as $item ) {
				foreach( array( 'redundant', 'missing', 'damaged' ) as $key ) {
					if( ocme()->arr()->has( $item, $key ) ) {
						if( $item[$key]->count() ) {
							return true;
						}
					}
				}
			}
		}
		
		return false;
	}
	
	private function panels() {
		/* @var $panels array */
		$panels = $this->_panels();
		
		foreach( $panels as & $panel ) {
			foreach( $panel['items'] as & $item ) {
				foreach( array( 'total', 'redundant', 'missing', 'damaged' ) as $key ) {
					if( ocme()->arr()->has( $item, $key ) ) {
						$item[$key] = $item[$key]->count();
					}
				}
			}
		}
		
		return $panels;
	}
	
	private function _panels() {
		return array(
			array(
				'type' => 'product',
				'name' => ocme()->trans('module::global.text_products'),
				'items' => array(
					array(
						'name' => ocme()->trans('module::global.text_items'),
						'total' => Product::query(),
						'missing' => Product::missing(),
					),
				)
			),
			array(
				'type' => OcmeFilterProperty::TYPE_OPTION,
				'name' => Ocme()->trans('module::global.text_options'),
				'items' => array(
					array(
						'name' => ocme()->trans('module::global.text_items'),
						'total' => Option::query(),
					),
					array(
						'name' => '<i class="fa fa-link"></i>',
						'total' => ProductOption::query(),
						'missing' => ProductOption::missing(),
						'params' => array(
							'reindex' => 'ProductOption::missing',
						)
					),
				)
			),
			array(
				'type' => OcmeFilterProperty::TYPE_ATTRIBUTE,
				'name' => ocme()->trans('module::global.text_attributes'),
				'items' => array(
					array(
						'name' => ocme()->trans('module::global.text_items'),
						'total' => AttributeModel::query(),
						'redundant' => OcmeFilterProperty::redundant( OcmeFilterProperty::TYPE_ATTRIBUTE ),
						'missing' => AttributeModel::missingOcmeFilterProperty(),
						'params' => array(
							'reindex' => 'AttributeModel::missingOcmeFilterProperty',
						),
					),
					array(
						'name' => ocme()->trans('module::global.text_values'),
						'total' => AttributeValue::query(),
						'redundant' => OcmeFilterPropertyValue::redundant( OcmeFilterPropertyValue::TYPE_ATTRIBUTE_VALUE ),
						'missing' => AttributeValue::missingOcmeFilterPropertyValue(),
						'params' => array(
							'reindex' => 'AttributeValue::missingOcmeFilterPropertyValue',
						),
					),
					array(
						'name' => '<i class="fa fa-link"></i>',
						'total' => ProductAttribute::query(),
						'redundant' => ProductAttribute::redundant(),
						'missing' => ProductAttribute::missing(),
						'params' => array(
							'reindex' => 'ProductAttribute::missing',
						)
					),
					array(
						'name' => '<i class="fa fa-link"></i>',
						'total' => ProductAttributeValue::query(),
						'redundant' => ProductAttributeValue::redundant(),
						'missing' => ProductAttributeValue::missingOcmeFilterPropertyValueToProduct(),
						'params' => array(
							'reindex' => 'ProductAttributeValue::missingOcmeFilterPropertyValueToProduct',
						),
					),
				),
			),
			array(
				'type' => 'tags',
				'name' => ocme()->trans('module::global.text_tags'),
				'items' => array(
					array(
						'name' => ocme()->trans('module::global.text_items'),
						'total' => ProductTag::query(),
					),
					array(
						'name' => '<i class="fa fa-link"></i>',
						'total' => ProductToTag::query(),
						'redundant' => ProductToTag::redundant(),
						'missing' => ProductDescription::missingTags(),
						'params' => array(
							'reindex' => 'ProductDescription::missingTags',
						),
					),
				),
			),
		);
	}
	
	public function settings() {
		if( ocme()->request()->methodIsPost() && $this->validateAccess() ) {
			/* @var $ocme_variables array */
			if( null != ( $ocme_variables = ocme()->request()->post('ocme_variables') ) ) {
				foreach( $ocme_variables as $type => $values ) {
					foreach( $values as $name => $value ) {
						OcmeVariable::firstOrNew(array(
							'store_id' => null, // @todo
							'type' => $type,
							'name' => $name,
						))->fill(array(
							'value' => $value,
						))->save();
					}
				}
				
				ocme()->msg()->success( 'module::global.success_updated' );
			}
		}
		
		$this->initialize()->module( 'filter_settings', function(){
			$this->data['action'] = ocme()->url()->adminLink($this->path.'_filter/settings');
			$this->data['oc_version4'] = version_compare( VERSION, '4', '>=' );
		});
	}
	
	public function indexation() {
		if( ! ocme()->ocRegistry()->get('config')->get('ocme_mfp_installed_at') ) {
			ocme()->model('setting/setting')->editSetting('ocme_mfp_installed_at', array(
				'ocme_mfp_installed_at' => date('Y-m-d H:i:s'),
			));
		}
		
		$this
			->initialize()
			->module('filter_indexation', function(){
				$this->data['https_catalog'] = defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG;
				$this->data['ocme_mfp_editable'] = $this->validateAccess('modify',false)?'true':'false';
		
				$this->data['ocme_mfp_license'] = base64_encode(json_encode(array_replace( ocme()->license()->shopData(), array(
					'token' => ocme()->license()->token(),
					'version' => ocme()->version(),
				))));
				
				$this->data['action_filter'] = ocme()->url()->adminLink($this->name);
				$this->data['action_indexation_action'] = ocme()->url()->adminLink($this->name.'/indexation_action');
				$this->data['autostart'] = ocme()->request()->query('autostart') ? 'true' : 'false';
			});
	}
	
}