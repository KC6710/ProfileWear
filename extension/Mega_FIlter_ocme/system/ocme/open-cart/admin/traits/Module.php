<?php namespace Ocme\OpenCart\Admin\Traits;

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
	Ocme\Model\Store,
	Ocme\Model\Layout,
	Ocme\Model\LayoutRoute,
	Ocme\Model\Category,
	Ocme\Model\CustomerGroup,
	Ocme\Model\Language,
	Ocme\Model\Manufacturer,
	Ocme\Model\OcmeVariable;

trait Module {
	
	protected function module_config( $type, $response = array() ) {
		if( ! in_array( $type, array( ModuleModel::CODE_FILTER, ModuleModel::CODE_SEARCH ) ) ) {
			exit;
		}
		
		/* @var $paginate_params array */
		$paginate_params = array( 'per_page', 'total', 'last_page', 'current_page', 'data' );
		
		$this->response->addHeader('Content-Type: application/json');
		
		\Illuminate\Pagination\Paginator::currentPageResolver(function(){
			return ocme()->request()->input( 'page', 1 );
		});
		
		if( ocme()->request()->methodIsPost() ) {
			/* @var $action string */
			$action = ocme()->arr()->get( $this->request->post, 'action' );
			
			$response = array(
				'status' => 'error',
			);
			
			if( $this->validateAccess() ) {
				switch( $action ) {
					case 'add_module' : {
						/* @var $module ModuleModel */
						$module = ModuleModel::create(array(
							'code' => ModuleModel::codeByOcVersion($type)
						));
						
						$response = array(
							'status' => 'success',
							'data' => array(
								'name' => $module->name,
								'module_id' => $module->module_id,
							)
						);
						
						break;
					}
					case 'remove_module' : {
						/* @var $module_id int */
						if( null != ( $module_id = (int) ocme()->arr()->get( $this->request->post, 'module_id' ) ) ) {
							/* @var $module ModuleModel */
							if( null != ( $module = ModuleModel::find( $module_id ) ) ) {
								$module->delete();
								
								$response = array(
									'status' => 'success',
								);
							}
						}
						
						break;
					}
					case 'duplicate_module' : {
						/* @var $module_id int */
						if( null != ( $module_id = (int) ocme()->arr()->get( $this->request->post, 'module_id' ) ) ) {
							/* @var $module ModuleModel */
							if( null != ( $module = ModuleModel::find( $module_id ) ) ) {
								/* @var $new_module ModuleModel */
								$new_module = $module->duplicate();
								
								$response = array(
									'status' => 'success',
									'data' => array(
										'name' => $new_module->name,
										'module_id' => $new_module->module_id,
									)
								);
							}
						}
						
						break;
					}
					case 'save_conditions' : {
						/* @var $module_id int */
						if( null != ( $module_id = (int) ocme()->arr()->get( $this->request->post, 'module_id' ) ) ) {
							/* @var $module ModuleModel */
							if( null != ( $module = ModuleModel::find( $module_id ) ) ) {
								/* @var $conditions_to_add array */
								if( null != ( $conditions_to_add = ocme()->arr()->get( $this->request->post, 'conditions_to_add' ) ) ) {
									if( null != ( $conditions_to_add = (array) json_decode( html_entity_decode($conditions_to_add, ENT_QUOTES, 'UTF-8'), true ) ) ) {
										/* @var $condition array */
										foreach( $conditions_to_add as $condition ) {
											OcmeFilterCondition::addOrUpdate(array_replace($condition, array(
												'module_id' => $module_id,
											)));
										}
									}
								}
								
								/* @var $conditions_to_remove array */
								if( null != ( $conditions_to_remove = ocme()->arr()->get( $this->request->post, 'conditions_to_remove' ) ) ) {
									if( null != ( $conditions_to_remove = (array) json_decode( html_entity_decode($conditions_to_remove, ENT_QUOTES, 'UTF-8'), true ) ) ) {
										/* @var $condition array */
										foreach( $conditions_to_remove as $condition ) {
											/* @var $id int */
											if( null != ( $id = (int) ocme()->arr()->get( $condition, 'id' ) ) ) {
												/* @var $ocme_filter_condition OcmeFilterCondition */
												if( null != ( $ocme_filter_condition = OcmeFilterCondition::where('module_id', $module_id)->find( $id ) ) ) {
													$ocme_filter_condition->delete();
												}
											}
										}
									}
								}
								
								/* @var $conditions_to_save array */
								if( null != ( $conditions_to_save = ocme()->arr()->get( $this->request->post, 'conditions_to_save' ) ) ) {
									if( null != ( $conditions_to_save = (array) json_decode( html_entity_decode($conditions_to_save, ENT_QUOTES, 'UTF-8'), true ) ) ) {
										/* @var $condition array */
										foreach( $conditions_to_save as $condition ) {
											/* @var $id int */
											if( null != ( $id = (int) ocme()->arr()->get( $condition, 'id' ) ) ) {
												/* @var $ocme_filter_condition OcmeFilterCondition */
												if( null != ( $ocme_filter_condition = OcmeFilterCondition::where('module_id', $module_id)->find( $id ) ) ) {
													$ocme_filter_condition->fill( ocme()->arr()->only( $condition, array(
														'status', 'type', 'sort_order',
													)))->fill(array(
														'setting' => ocme()->arr()->get( $condition, 'setting' )
													))->save();
												}
											}
										}
									}
								}
							}
						}
						
						$response = array(
							'status' => 'success',
						);
						
						break;
					}
					case 'save_module' : {
						/* @var $module_id int */
						if( null != ( $module_id = (int) ocme()->arr()->get( $this->request->post, 'module_id' ) ) ) {
							/* @var $module ModuleModel */
							if( null != ( $module = ModuleModel::find( $module_id ) ) ) {
								/* @var $setting string|array */
								if( null != ( $setting = ocme()->arr()->get( $this->request->post, 'setting' ) ) ) {
									$setting = json_decode( html_entity_decode($setting, ENT_QUOTES, 'UTF-8'), true );
									
									foreach( ocme()->model('filter')->filterBaseConditions() as $base_condition_name => $base_condition ) {
										/* @var $items array */
										if( null != ( $items = ocme()->arr()->get( $setting, 'conditions.' . $base_condition_name . '.items' ) ) ) {
											/* @var $item_name string */
											/* @var $item array */
											foreach( $items as $item_name => $item ) {
												OcmeFilterCondition::firstOrNew(array(
													'module_id' => $module_id,
													'condition_type' => $base_condition['type'],
													'name' => $item_name,
												))->fill(array(
													'status' => ocme()->arr()->get( $item, 'status' ),
													'type' => ocme()->arr()->get( $item, 'type' ),
													'sort_order' => ocme()->arr()->get( $item, 'sort_order' ),
													'setting' => ocme()->arr()->get( $item, 'setting' ),
												))->save();
											}

											unset( $setting['conditions'][$base_condition_name]['items'], $item, $item_name, $items );
										}
									}
									
									/* @var $name string */
									if( '' === ( $name = ocme()->request()->post('name', $module->name) ) ) {
										$name = $module->name;
									}
									
									$module->fill(array(
										'setting' => array_replace( $setting, array(
											'name' => $name,
											'module_id' => $module->module_id,
										)),
										'name' => $name,
									))->save();									

									$response = array(
										'status' => 'success',
										'data' => array(
											'name' => $module->name,
											'module_id' => $module->module_id,
										)
									);
									
									/* @var $lsb_grid array */
									if( null != ( $lsb_grid = ocme()->request()->post('lsb_grid') ) ) {
										$lsb_grid = json_decode( html_entity_decode( $lsb_grid, ENT_QUOTES, 'UTF-8'), true );
										
										$this->saveGrid( $module_id, $lsb_grid );
										
										/* @var $lsb_data_grid array|bool */
										if( null != ( $lsb_data_grid = $this->createGrid( $module_id ) ) ) {
											$response['data'] = array_replace( $response['data'], $lsb_data_grid );
										}
									}
								}
							}
						}
						
						break;
					}
				}
			} else {
				$response = array( 'status' => 'error', 'msg' => ocme()->trans('error_permission'), 'error' => 'no_permission' );
			}
		} else if( ocme()->str()->startsWith( ocme()->request()->input( 'namespace', '' ), 'module' ) && ocme()->arr()->has( $this->request->get, 'name' ) ) {
			$response['status'] = 'success';
			$response['data'] = array();
			
			/* @var $conditions array */
			$conditions = array();
			
			/* @var $phrase string|null */
			if( null !== ( $phrase = ocme()->request()->input( 'phrase' ) ) && $phrase !== '' ) {
				$conditions['phrase'] = $phrase;
			}
			
			/* @var $name string */
			$name = ocme()->request()->input( 'name' );
			
			if( in_array( $name, array( 'show_in_categories', 'hide_in_categories' ) ) ) {
				if( ocme()->request()->input( 'with_path' ) ) {
					$conditions['with_path'] = 1;
				}
				
				/* @var $only_ids array|string */
				if( null != ( $only_ids = ocme()->request()->input( 'only_ids' ) ) ) {
					$conditions['only_ids'] = $only_ids;
				}
			}
			
			switch( $name ) {
				case 'layouts' : {
					$response['data'] = Layout::createTree( $conditions );
					
					break;
				}
				case 'customer_groups' : {
					$response['data'] = CustomerGroup::createTree( $conditions );
					
					break;
				}
				case 'stores': {
					$response['data'] = Store::createTree( $conditions );
					
					break;
				}
				case 'show_in_categories' :
				case 'hide_in_categories' : {					
					$response['data'] = Category::createTree( $conditions, ocme()->request()->input( 'parent_id' ) );
					
					break;
				}
				case 'show_in_manufacturers' :
				case 'hide_in_manufacturers' : {					
					$response['data'] = Manufacturer::createTree( $conditions );
					
					break;
				}
			}
		} else {
			$response['status'] = 'success';
			
			/* @var $data_types array */
			$data_types = ocme()->arr()->has( $this->request->get, 'data_types' ) ? explode( ',', ocme()->request()->input( 'data_types' ) ) : array();
			
			/* @var $module_id int */
			if( null == ( $module_id = ocme()->arr()->has( $this->request->get, 'module_id' ) ? (int) ocme()->request()->input( 'module_id' ) : null ) ) {
				if( ! empty( $response['data']['modules'] ) ) {
					$module_id = ocme()->arr()->get( $response['data']['modules'], '0.module_id' );
				}
			}
			
			/* @var $module ModuleModel|null */
			$module = $module_id ? ModuleModel::find( $module_id ) : null;
			
			if( in_array( 'base', $data_types ) ) {
				/* @var $modules \Illuminate\Database\Eloquent\Collection */
				$modules = ModuleModel::select(array('module_id', 'name'))->where('code', 'LIKE', ModuleModel::codeByOcVersion($type))->orderBy('module_id', 'ASC')->get();
				
				if( ! $modules->isEmpty() ) {
					$response['data']['modules'] = ocme()->arr()->build( $modules, function($k, $v){
						return array( $k, ocme()->collection()->make( $v )->only(array('module_id', 'name'))->all() );
					});
				}
				
				if( is_null( $module ) && ! empty( $response['data']['modules'] ) ) {
					$module_id = ocme()->arr()->get( $response['data']['modules'], '0.module_id' );
					$module = ModuleModel::find( $module_id );
				}
				
				/**
				 * Global variables
				 */
				$response['data']['global'] = array();
				
				/* @var $ocme_variable OcmeVariable */
				foreach( OcmeVariable::where('type', ocme()->arr()->first(array(
						ModuleModel::CODE_FILTER => OcmeVariable::TYPE_FILTER_GLOBAL
					), function($v, $k) use( $type ){
						return $k == $type;
					}))->get() as $ocme_variable 
				) {
					ocme()->arr()->set( $response['data']['global'], $ocme_variable->name, $ocme_variable->value );	
				}
				
				/**
				 * Themes
				 */
				$response['data']['config']['themes'] = array();
				
				/* @var $themes array */
				if( null != ( $themes = glob( DIR_CATALOG . 'view/ocme/stylesheet/themes/' . str_replace( 'ocme_mfp_', '', $type ) . '/*.css' ) ) ) {
					$response['data']['config']['themes'] = ocme()->collection()->make( $themes )->mapWithKeys(function( $path ){
						/* @var $name string */
						$name = str_replace( array( '.min.css', '.css' ), '', basename( $path ) );
						
						return array( $name => $name );
					})->values();
				}
				
				/**
				 * Breakpoints
				 */
				$response['data']['config']['breakpoints'] = ocme()->collection( ocme()->variable()->breakpoints() )->map(function($v){
						return array(
							'id' => (string) ocme()->arr()->get( $v, 'id' )
						) + ocme()->arr()->only( $v, array( 'name', 'value' ) );
					});
				
				/**
				 * Layouts per route
				 */
				$response['data']['config']['layouts'] = ocme()->collection( LayoutRoute::whereIn('route', [
					LayoutRoute::ROUTE_PRODUCT_CATEGORY,
					LayoutRoute::ROUTE_MANUFACTURER_INFO
				])->get() )->mapToDictionary(function($v){
					/* @var $keys array */
					$keys = [
						LayoutRoute::ROUTE_PRODUCT_CATEGORY => 'category',
						LayoutRoute::ROUTE_MANUFACTURER_INFO => 'manufacturer',
					];
					
					/* @var $key string */
					$key = array_get( $v, 'route' );
					
					return array( $keys[$key] => array_get( $v, 'layout_id' ) );
				});
				
				/**
				 * Layouts
				 */
				$response['data']['layouts'] = Layout::createTree();
				
				/**
				 * Stores
				 */
				$response['data']['stores'] = Store::createTree();
				
				/**
				 * Categories
				 */
				$response['data']['show_in_categories'] = Category::createTree(array(
					'values' => $module ? ocme()->arr()->get( $module->setting, 'show_in_categories', array() ) : array()
				), 0);
				$response['data']['hide_in_categories'] = Category::createTree(array(
					'values' => $module ? ocme()->arr()->get( $module->setting, 'show_in_categories', array() ) : array()
				), 0);
				$response['data']['show_in_manufacturers'] = Manufacturer::createTree(array(
					'values' => $module ? ocme()->arr()->get( $module->setting, 'show_in_categories', array() ) : array()
				));
				$response['data']['hide_in_manufacturers'] = Manufacturer::createTree(array(
					'values' => $module ? ocme()->arr()->get( $module->setting, 'hide_in_categories', array() ) : array()
				));
				
				/**
				 * Customer groups
				 */
				$response['data']['customer_groups'] = CustomerGroup::createTree();
				
				/**
				 * Languages
				 */
				$response['data']['languages'] = Language::query()->statusEnabled()->get()->map(function( $language ){
					return [
						'language_id' => $language->language_id,
						'name' => $language->name,
						'image_path' => $language->image_path,
					];
				});
			}
			
			/* @var $condition_type string */
			$condition_type = ocme()->arr()->first(array(
				'attributes' => OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
				'attribute_groups' => OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP,
				'options' => OcmeFilterCondition::CONDITION_TYPE_OPTION,
				'filter_groups' => OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
				//'features' => OcmeFilterCondition::CONDITION_TYPE_FEATURE
			), function( $v, $k ) use( $data_types ) {
				return in_array( $k, $data_types );
			});
			
			if( $condition_type ) {
				/* @var $class string */
				$class = '\\Ocme\\Model\\' . ocme()->str()->studly( $condition_type );
				
				/* @var $query \Illuminate\Database\Eloquent\Builder */
				$query = $class::query();
				
				$query->select([
						$condition_type . '.*',
						$condition_type . '_description.*',
						'ocme_filter_condition.id AS exists_in_module',
					])
					->leftJoin('ocme_filter_condition', function($q) use( $module_id, $condition_type ){
						$q
							->on('ocme_filter_condition.record_id', '=', $condition_type . '.' . $condition_type . '_id')
							->where('ocme_filter_condition.condition_type', '=', $condition_type)
							->where('ocme_filter_condition.module_id', '=', $module_id);
					})
					->withDescription()
					->orderBy(ocme()->db()->raw('IF(exists_in_module IS NULL, 0, 1)'))
					->orderBy($condition_type . '_description.name');
				
				switch( $condition_type ) {
					case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : {
						$query
							->withAttributeGroup()
							->withAttributeGroupDescription()
							->with([
								'attribute_group' => function( $q ) {
									$q->withDescription();
								}
							]);

						/* @var $attribute_name string */
						if( '' !== ( $attribute_name = ocme()->request()->input( 'attribute_name', ocme()->request()->input( 'value_name', '' ) ) ) ) {
							$query->where('attribute_description.name', 'LIKE', '%' . $attribute_name . '%');
						}
						
						break;
					}
					case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
						$query->whereNotIn($condition_type . '.type', array( 'file' ));
						
						break;
					}
				}
				
				/* @var $attribute_group_name string */
				if( '' !== ( $attribute_group_name = ocme()->request()->input( 'attribute_group_name', ocme()->request()->input( 'value_group_name', '' ) ) ) ) {
					$query->where('attribute_group_description.name', 'LIKE', '%' . $attribute_group_name . '%');
				}
					
				$response['data'][$condition_type . 's'] = ocme()->arr()->only( $query->paginate()->toArray(), $paginate_params ) + [ 'loaded' => true ];
			}
			
			if( $module ) {
				/* @var $condition_type string */
				$condition_type = ocme()->arr()->first([
					'module_attributes' => OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
					'module_attribute_groups' => OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP,
					'module_options' => OcmeFilterCondition::CONDITION_TYPE_OPTION,
					'module_filter_groups' => OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
					//'module_features' => OcmeFilterCondition::CONDITION_TYPE_FEATURE
				], function( $v, $k ) use( $data_types ) {
					return in_array( $k, $data_types );
				});
				
				if( $condition_type ) {
					/* @var $query \Illuminate\Database\Eloquent\Builder */
					$query = OcmeFilterCondition::select(array(
							'ocme_filter_condition.*'
						))
						->leftJoin($condition_type . '_description', function($q) use( $condition_type ) {
							$q
								->on('ocme_filter_condition.record_id', '=', $condition_type . '_description.' . $condition_type . '_id')
								->where($condition_type . '_description.language_id', '=', ocme()->ocRegistry()->get('config')->get('config_language_id'));
						})
						->where('module_id', $module_id)
						->where('condition_type', $condition_type)
						->with(array(
							$condition_type => function( $q ){
								$q->withDescription();
							},
						));

					if( $condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE ) {
						$query
							->with(array(
								$condition_type => function( $q ){
									$q->withDescription();
								},
								'attribute.attribute_group' => function( $q ){
									$q->withDescription();
								}
							));
					}

					/* @var $q string */
					if( '' !== ( $q = ocme()->request()->input( 'q', '' ) ) ) {
						$query->where($condition_type . '_description.name', 'LIKE', '%' . $q . '%');
					}

					// show latest added first
					if( ocme()->request()->input( 'latest' ) ) {
						$query->orderBy('id', 'DESC');
					} else {
						$query->orderBy($condition_type . '_description.name');
					}

					/* @var $results \Illuminate\Contracts\Pagination\LengthAwarePaginator */
					$results = $query->paginate( max(array( 20, (int) ocme()->request()->input( 'min_per_page', 20 ) )) );

					if( $condition_type == OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE ) {
						/* @var $attribute_group_ids array */
						$attribute_group_ids = ocme()->collection()->make( $results->items() )->map(function( $result ){
							return $result->attribute->attribute_group_id;
						})->unique()->all();

						if( $attribute_group_ids ) {
							$response['data']['module_attribute_groups'] = array(
								'data' => OcmeFilterCondition::select(array(
										'ocme_filter_condition.*'
									))
									->where('module_id', $module_id)
									->where('condition_type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP)
									->whereIn('record_id', $attribute_group_ids)
									->with('attribute_group')
									->get(),
								'current_page' => 1,
							);

							$response['data']['module_attribute_groups']['total'] = $response['data']['module_attribute_groups']['per_page'] = count( $response['data']['module_attribute_groups']['data'] );
						}
					}
					
					$response['data']['module_' . $condition_type . 's'] = ocme()->arr()->only( 
						$results->toArray(),
						$paginate_params
					) + [ 'loaded' => true ];
				}
				
				if( in_array( 'lsb_grid', $data_types ) ) {
					/* @var $lsb_data_grid array|bool */
					if( null != ( $lsb_data_grid = $this->createGrid( $module_id ) ) ) {
						$response['data'] = array_replace( $response['data'], $lsb_data_grid );
					}
				}
			}
			
			if( in_array( 'module', $data_types ) ) {
				$this->prepareModuleInResponse( $response, $module );
			}
		}
		
		$this->response->setOutput(json_encode( $response ));
	}
	
	protected function prepareModuleInResponse( array & $response, $module ) {		
		if( $module ) {
			if( ! $module->getOriginal('setting') ) {
				$response['data']['module_is_not_saved'] = true;
			}

			$response['data']['module'] = $module->toArray();

			/**
			 * Create base conditions
			 */
			foreach( ocme()->model('filter')->filterBaseConditions() as $base_condition_name => $base_condition ) {
				$response['data']['module']['setting']['conditions'][$base_condition_name]['items'] = ocme()->collection()->make( OcmeFilterCondition::where('module_id', $module->module_id)
					->where('condition_type', $base_condition['type'])
					->get()
				)->mapWithKeys(function($v){
					return array( ocme()->arr()->get( $v, 'name' ) => ocme()->arr()->except( $v, array( 'id', 'module_id', 'condition_type', 'name', 'record_id' ) ) );
				})->toArray();

				/**
				 * Init items
				 */
				if( ! $response['data']['module']['setting']['conditions'][$base_condition_name]['items'] ) {
					$response['data']['module']['setting']['conditions'][$base_condition_name]['items'] = array_fill_keys( $base_condition['names'], array(
						'status' => '0',
					));

					if( $base_condition_name == 'base_attributes' ) {
						$response['data']['module']['setting']['conditions'][$base_condition_name]['items']['price'] = array(
							'status' => '1',
							'sort_order' => '-1'
						);
					}
				}
			}
		} else {
			$response['data']['module'] = null;
		}
	}
	
	protected function module( $type, $callback = null ) {
		$this->data['action_'.$type.'_config'] = ocme()->url()->adminLink($this->name.'/config');
		
		if( in_array( $type, array( 'filter', 'filter_settings', 'filter_indexation' ) ) ) {
			$this->data['action_filter_settings'] = ocme()->url()->adminLink($this->name.'/settings');
			$this->data['action_filter_indexation'] = ocme()->url()->adminLink($this->name.'/indexation');
		}
		
		$this->render($type, 'module::global.heading_name', function() use( $type, $callback ){			
			if( is_callable( $callback ) ) {
				$callback();
			}
			
			$this->data['ocme_heading_title'] = ocme()->trans('module::global.heading_title', array(
				'ocme_url' => defined('DIR_EXTENSION') ? HTTP_CATALOG . 'extension/ocme/admin/' : ( defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER ),
			));
		});
	}
	
}