<?php namespace Ocme\OpenCart\Catalog\Controller;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use Ocme\Module,
	Ocme\ModuleSetting,
	Ocme\Model\OcmeFilterCondition,
	Ocme\Model\OcmeVariable,
	Ocme\Module\Filter as FilterModule,
	Ocme\Module\Filter\Condition,
	Ocme\Model\Module as OcModule;

trait Filter {
	
	use \Ocme\Support\Traits\Minify;
	
	/**
	 * @var string
	 */
	protected $name = 'extension/module/ocme_mfp_filter';
	
	/**
	 * @var array
	 */
	protected $inheritance_map = array();
	
	/**
	 * @var ModuleSetting
	 */
	protected $setting;
	
	/**
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * @var bool
	 */
	protected static $initialized = false;

	/**
	 * @var array
	 */
	protected static $cache = array();

	public function initTrait() {
		$this->cache_path_js = 'view/ocme/javascript/cache';
		$this->cache_path_css = 'view/ocme/stylesheet/cache';
	}
	
	/**
	 * @param type $setting
	 * @return $this
	 */
	protected function initializeSetting( $setting ) {
		$this->setting = new ModuleSetting( $setting, OcmeVariable::TYPE_FILTER_GLOBAL );
		
		return $this;
	}
	
	/**
	 * @param array $setting
	 * @return string|null
	 */
	public function index( $setting ) {
		if( ! function_exists( 'ocme' ) ) {
			return;
		}
		
		if( ocme()->ajaxRendering() ) {
			return;
		}
		
		/* @var $route string */
		$route = ocme()->request()->ocQueryRoute();
		
		/**
		 * Check list of supported routes
		 */
		if( ! in_array( $route, FilterModule::supportedRoutes() ) && ! in_array( $route, array( 'common/home' ) ) ) {
			return;
		}
		
		$this->initializeSetting( $setting );
		
		/**
		 * Check status
		 */
		if( ! Module::validStatus( $this->setting->get( 'status' ) ) ) {
			return;
		}
		
		/**
		 * Check store
		 */
		if( ! Module::validStore( $this->setting->get( 'stores' ) ) ) {
			return;
		}
		
		/**
		 * Check customer group
		 */
		if( ! Module::validCustomerGroup( $this->setting->get( 'customer_groups' ) ) ) {
			return;
		}
		
		/**
		 * Check schedule
		 */
		if( ! Module::validSchedule( $this->setting->get('start_date'), $this->setting->get('end_date') ) ) {
			return;
		}
		
		/**
		 * Check device
		 */
		if( ! Module::validDevice( $this->setting->get('devices') ) ) {
			return;
		}
		
		/**
		 * Check category
		 */
		if( ! Module::validCategory( 
			$this->setting->get('show_in_categories'), 
			$this->setting->get('show_in_categories_with_children'), 
			$this->setting->get('hide_in_categories'), 
			$this->setting->get('hide_in_categories_with_children') 
		) ) {
			return;
		}
		
		/**
		 * Check manufacturer
		 */
		if( ! Module::validManufacturer( $this->setting->get('show_in_manufacturers'), $this->setting->get('hide_in_manufacturers') ) ) {
			return;
		}
		
		return $this->renderIndex();
	}
	
	protected function jsDynamicContent() {
		/* @var $js array */
		$js = array();
		
		/* @var $trans array */
		$trans = ocme()->translator()->get('module::filter');

		/** @var string $url */
		if( null == ( $url = ocme()->oc()->registry()->get('config')->get('site_ssl') ) ) {
			$url = ocme()->oc()->registry()->get('config')->get('site_url');
		}

		$url .= 'index.php?route=' . ocme_extension_path('extension/module/ocme_mfp_filter');
		
		if (version_compare( VERSION, '4', '>=' )) {
			$url = str_replace( 'index.php?', 'index.php?language='.ocme()->oc()->registry()->get('config')->get('config_language').'&', $url );
		}

		if (version_compare( VERSION, '4.0.2.0', '>=' )) {
			$url .= '.';
		} else if (version_compare( VERSION, '4', '>=' )) {
			$url .= '|';
		} else {
			$url .= '/';
		}
		
		$js[] = "window.addEventListener('load', function() {";
		
			$js[] = "ocmeFramework.extension('config').set(" . json_encode(array(
				'filter' => array(
					'url' => $url,
					'seo' => ocme()->variable()->get( OcmeVariable::TYPE_FILTER_SEO_CONFIG ),
				)
			)) . ');';

			$js[] = "ocmeFramework.extension('trans').set(" . ocme()->arr()->getAsJson( $trans ) . ");";
		
		$js[] = "});";
		
		$js = implode("\n", $js);
		
		/* @var $file string */
		$file = md5($js) . '.js';
		
		/* @var $dir string */
		$dir = ( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'ocme/catalog/' : DIR_APPLICATION ) . $this->cache_path_js;
		
		if( ! file_exists( $dir . '/' . $file ) ) {
			$this->refreshCacheFolder($dir, 'js');
			
			file_put_contents( $dir . '/' . $file, $js );
		}
		
		return 'catalog/' . $this->cache_path_js . '/' . $file;
	}
	
	protected function renderIndex() {
		/** @var string $cache_key */
		$cache_key = 'module:'.$this->setting->get( 'module_id' );

		if( isset( self::$cache[$cache_key] ) ) {
			return self::$cache[$cache_key];
		}

		$this->load->language( $this->name );
		
		/* @var $filter FilterModule */
		$filter = new FilterModule( $this->setting );
		
		if( ! $filter->hasGroups() ) {
			return;
		}
		
		/* @var $css_path string */
		$css_path = 'catalog/view/ocme/stylesheet/';
		
		/* @var $js_path string */
		$js_path = 'catalog/view/ocme/javascript/';
		
		/**
		 * JavaScript
		 */
		$this->addScript($js_path . 'plugins/lodash/lodash.min.js');
		$this->addScript($js_path . 'plugins/moment/moment.min.js');
		$this->addScript($js_path . 'plugins/vue/vue.min.js');
		$this->addScript($js_path . 'plugins/vue/vue-resource.min.js');
		$this->addScript($js_path . 'plugins/uri/uri.min.js');
		$this->addScript($js_path . 'framework.js');
		$this->addScript($js_path . 'ocme.js');
		$this->addScript($js_path . 'utils.js');
		$this->addScript($js_path . 'config.js');
		$this->addScript($js_path . 'trans.js');
		$this->addScript($js_path . 'helpers/b64.js');
		$this->addScript($this->jsDynamicContent());
		$this->addScript($js_path . 'filter.js');
		$this->addScript($js_path . 'filter/condition.js');
		
		/**
		 * CSS
		 */
		$this->addStyle( $css_path . 'reset.css' );
		$this->addStyle( $css_path . 'breakpoints.css' );
		$this->addStyle( $css_path . 'plugins/fontawesome/css/all.css' );
		$this->addStyle( $css_path . 'filter.css' );

		/**
		 * @todo check if the module has any text
		 */
		if( true ) {
			$this->addScript($js_path . 'filter/condition-text.js');
			$this->addScript($js_path . 'components/autocomplete.js');
			$this->addStyle($css_path . 'autocomplete.css');
		}

		/**
		 * @todo check if the module has any list or select
		 */
		if( true ) {
			$this->addScript($js_path . 'filter/helper-labelable.js');
			$this->addScript($js_path . 'filter/helper-selectable.js');
		}

		/**
		 * @todo check if the module has any list
		 */
		if( true ) {
			$this->addScript($js_path . 'plugins/vue-virtual-list/vue-virtual-list.min.js');
			$this->addScript($js_path . 'filter/condition-list.js');
		}

		/**
		 * @todo check if the module has any range
		 */
		if( true ) {
			/**
			 * @todo check if the module has active any slider
			 */
			if( true ) {
				$this->addStyle($js_path . 'plugins/vue-slider/default.css');
				$this->addScript($js_path . 'plugins/vue-slider/vue-slider-component.umd.js');
			}
			
			$this->addScript($js_path . 'filter/condition-range.js');
		}

		/**
		 * @todo check if the module has any select
		 */
		if( true ) {
			$this->addScript($js_path . 'plugins/popper/popper.min.js');
			$this->addScript($js_path . 'plugins/vue-select/vue-select.js');
			$this->addStyle($js_path . 'plugins/vue-select/vue-select.css');
			$this->addScript($js_path . 'filter/condition-select.js');
		}
		
		/* @var $scripts array */
		if( null != ( $scripts = $this->minifyJS() ) ) {
			/* @var $script string */
			foreach( $scripts as $script ) {
				$this->document->addScript( $script );
			}
		}
		
		/* @var $styles array */
		if( null != ( $styles = $this->minifyCSS() ) ) {
			/* @var $style string */
			foreach( $styles as $style ) {
				$this->document->addStyle( $style );
			}
		}
		
		/* @var $import_css_path string */
		$import_css_path = defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER;
		
		if( version_compare( VERSION, '4', '>=' ) ) {
			$import_css_path .= 'extension/ocme/' . $css_path;
		} else {
			$import_css_path .= $css_path;
		}

		/* @var $import_css array */
		$import_css = array(
			array( 'file' => $import_css_path . 'filter.mobile.css' ),
			//array( 'file' => $import_css_path . 'filter.desktop.css', 'resolution' => 'min-width: 768px' )
		);
		
		/* @var $theme string */
		if( null != ( $theme = $this->setting->get( 'configuration.theme' ) ) ) {
			$import_css[] = array( 'file' => $import_css_path . 'themes/filter/' . $theme . '.css' );
		}
		
		$import_css = array_filter($import_css, function( $v ){
			return ! in_array( $v['file'], self::$loaded_files );
		});
		
		self::$loaded_files = array_merge( self::$loaded_files, array_map(function($v){
			return $v['file'];
		}, $import_css));
		
		/* @var $data array */
		$data = array_replace($this->data, array(
			'ocme' => ocme(),
			'id' => $this->setting->get( 'module_id' ),
			'import_css' => $import_css,
			'title' => $this->setting->get( 'title.' . $this->config->get('config_language_id') ),
			'setting' => $this->setting,
			'filter' => $filter,
			'device' => ocme()->mdetect()->device(),
			'filters' => $this->render_output( $filter ),
			'with_layout' => (bool) $filter->getGrid(),
			'events' => $filter->getEvents(),
		));
		
		if( ! self::$initialized ) {
			self::$initialized = true;
			
			$data = array_replace( $data, array(
				'fdata' => ocme()->arr()->getAsJson(array(
					'device' => ocme()->mdetect()->device(),
					'url_query' => http_build_query( ocme()->collection()->make( ocme()->request()->query() )->filter(function( $v, $k ) {
						if( in_array( $k, array( '_route_', ocme()->arr()->get( ocme()->variable()->get( OcmeVariable::TYPE_FILTER_SEO_CONFIG ), 'url_parameter_name' ) ) ) ) {
							return false;
						}

						return true;
					})->all()),
					'filter_data' => ocme()->model('filter')->getFilterData(),
					'pagination' => ocme()->model('filter')->pagination(),
					'current_route' => ocme()->request()->ocQueryRoute(),
					'redirect_url' => in_array( ocme()->request()->ocQueryRoute(), FilterModule::supportedRoutes() ) ? null : ocme()->oc()->registry()->get('url')->link('browse/catalog'),
				)),
				'global_custom_styles' => html_entity_decode( 
					(string) ocme()->variable()->get('filter_global_styles.custom_styles'), 
					ENT_QUOTES, 'UTF-8'
				),
			));
		}
		
		/* @var $out string */
		if( null != ( $out = $this->load->view(ocme_extension_path('extension/module/ocme_mfp_filter'), $data) ) ) {
			ocme()->config()->set('modules_rendered.filter', array_merge( ocme()->config()->get('modules_rendered.filter', array()), array( $this->setting->get('module_id') )));
		}
		
		return self::$cache[$cache_key] = $out;
	}
	
	protected function render_filters_items( FilterModule $filter, $parent = null ) {		
		/* @var $output string */
		$output = '';
		
		/* @var $current array */
		$current = array_filter( $filter->getGrid(), function( $item ) use( $parent ){			
			if( $parent ) {
				return $item['parent_id'] == $parent['id'];
			}
			
			return ! $item['parent_id'];
		});
		
		/* @var $map array */
		$map = $parent ? array_map(function( $item ){
			return $item['vid'] . ':' . $item['vname'] . ':' . $item['vtype'];
		}, array_filter( $filter->getGridMap(), function( $item ) use( $parent ){
			return $parent && $item['ocme_filter_grid_id'] == $parent['id'];
		})) : array();
		
		/* @var $not_assigned array */
		$not_assigned = array();
		
		if( $parent && ! empty( $parent['is_last'] ) ) {
			/* @var $full_map array */
			$full_map = array_map(function( $item ){
				return $item['vid'] . ':' . $item['vname'] . ':' . $item['vtype'];
			}, $filter->getGridMap());
			
			$not_assigned = ocme()->arr()->flatten( array_map( function( $item ) use( $full_map ){
				/* @var $conditions array */
				$conditions = array_filter( array_map(function( $item ){
					return $item->getConfig('vid') . ':' . $item->getConfig('name') . ':' . $item->getConfig('vtype');
				}, $item['conditions']), function( $key ) use( $full_map ){
					return ! in_array( $key, $full_map );
				});

				return $conditions;
			}, ocme()->arr()->get( $filter->groups(), 'items' )) );
		}
		
		/* @var $items array */
		$items = $map || $not_assigned ? array_map( function( $item ) use( $map, $not_assigned ){
			$item['conditions'] = array_filter( $item['conditions'], function( $item ) use( $map, $not_assigned ){
				/* @var $key string */
				$key = $item->getConfig('vid') . ':' . $item->getConfig('name') . ':' . $item->getConfig('vtype');
				
				return in_array( $key, $map ) || in_array( $key, $not_assigned );
			});
			
			usort( $item['conditions'], function( $a, $b ) use( $map ){
				/* @var $ka string */
				$ka = $a->getConfig('vid') . ':' . $a->getConfig('name') . ':' . $a->getConfig('vtype');
				
				/* @var $kb string */
				$kb = $b->getConfig('vid') . ':' . $b->getConfig('name' ) . ':' . $b->getConfig('vtype');
				
				/* @var $sa int|bool */
				if( false === ( $sa = array_search( $ka, $map ) ) ) {
					$sa = (int) $a->getConfig('sort_order');
				}
				
				/* @var $sb int|bool */
				if( false === ( $sb = array_search( $kb, $map ) ) ) {
					$sb = (int) $b->getConfig('sort_order');
				}
				
				return $sa - $sb;
			});
			
			return $item;
		}, ocme()->arr()->get( $filter->groups(), 'items' )) : array();
				
		if( $items ) {
			$output .= $this->load->view(ocme_extension_path('extension/module/ocme_mfp_filters'), array_replace($this->data, array(
				'filter' => $filter,
				'items' => $items,
				'with_layout' => true,
			)));
		}
		
		/* @var $item array */
		foreach( $current as $item ) {
			/* @var $content string */
			if( '' !== ( $content = $this->render_filters_items( $filter, $item ) ) ) {			
				if( $item['type'] == 'row' ) {
					$output .= '<div class="row">';
				} else if( $item['type'] == 'column' ) {
					/* @var $cols array */
					$cols = ocme()->arr()->get( $item, 'settings.cols', array( 'lg' => 12 ) );

					/* @var $offsets array */
					$offsets = ocme()->arr()->get( $item, 'settings.offsets', array() );

					$output .= '<div class="';

					$output .= implode( ' ', array_map(function($k, $v){
						return 'col-' . $k . '-' . $v;
					}, array_keys( $cols ), $cols ));

					if( $offsets ) {
						$output .= ' ' . implode( ' ', array_map(function($k, $v){
							return 'col-' . $k . '-offset-' . $v;
						}, array_keys( $offsets ), $offsets ));
					}

					$output .= '">';
				}
				
				$output .= $content;
				$output .= '</div>';
			}
		}
		
		return $output;
	}
	
	public function refresh() {
		/* @var $response array */
		$response = array(
			'status' => 'error',
		);
		
		$this->load->language( $this->name );
		
		/* @var $module_ids array */
		if( null != ( $module_ids = (array) ocme()->request()->input('ocmef_module_ids') ) ) {
			/* @var $module_data array */
			$module_data = (array) ocme()->request()->input('ocmef_module_data');
			
			/* @var $ocmef_conditions_params array */
			$ocmef_conditions_params = (array) ocme()->request()->input('ocmef_conditions_params');
			
			/* @var $load_results_via_ajax bool */
			$load_results_via_ajax = false;
					
			/* @var $module_id int */
			foreach( $module_ids as $module_id ) {
				/* @var OcModule $oc_module */
				if( null != ( $oc_module = OcModule::where('code', OcModule::codeByOcVersion( OcModule::CODE_FILTER ))->find( $module_id ) ) ) {
					$this
						->initializeSetting( $oc_module->setting )
						->initializeUrlQuery()
						->initializeFilterData(array(
							'ocmef_refreshing_values' => ! ocme()->arr()->get( $oc_module->setting, 'configuration.load_results_via_ajax' ),
							'ocmef_only_availability' => ocme()->arr()->get($module_data, $module_id . '.only_availability', ocme()->arr()->get($oc_module->setting,'configuration.other.only_available')),
							'ocmef_conditions_params' => $ocmef_conditions_params,
						));
					
					/* @var $conditions_list string */
					$conditions_list = 'first';
					
					if( ocme()->arr()->get($module_data, $module_id . '.hidden_filters') == '1' || $ocmef_conditions_params ) {
						$conditions_list = 'all';
					}

					/* @var $filter FilterModule */
					$filter = new FilterModule( $this->setting, $conditions_list );
					
					if( $filter->hasGroups() ) {
						if( $filter->getConfig('configuration.load_results_via_ajax') ) {
							$load_results_via_ajax = true;
						}
						
						$response['status'] = 'success';
						
						/* @var $module array */
						$module = array(
							'id' => $module_id,
							'left_filters' => ocme()->arr()->get( $filter->groups(), 'left' ),
						);

						/* @var $items array */
						foreach( ocme()->arr()->get( $filter->groups(), 'items' ) as $item ) {
							/* @var $condition \Ocme\Module\Filter\Condition */
							foreach( ocme()->arr()->get( $item, 'conditions', array()) as $condition ) {
								$module['conditions'][] = array(
									'params' => array(
										'vid' => $condition->getConfig('vid'),
										'vtype' => $condition->getConfig('vtype'),
										'name' => $condition->getConfig('name'),
									),
									'data' => $condition->getBasicData(),
								);
							}
						}
						
						$response['data']['modules'][] = $module;
					}
					
					/* @var $ocmef_source_route string */
					if( $load_results_via_ajax && null != ( $ocmef_source_route = ocme()->arr()->get( ocme()->oc()->registry()->get('request')->get, 'ocmef_source_route' ) ) ) {
						// Sanitize the call
						$ocmef_source_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$ocmef_source_route);
						
						if( ! in_array( $ocmef_source_route, FilterModule::supportedRoutes() ) ) {
							$ocmef_source_route = 'browse/catalog';
						}
						
						ocme()->ajaxRendering( true );
						
						$action = version_compare( VERSION, '4', '>=' ) ? new \Opencart\System\Engine\Action( $ocmef_source_route ) : new \Action( $ocmef_source_route );

						$action->execute( $this->registry );

						$response['data']['ajax'] = array(
							'content' => base64_encode( $this->response->getOutput() ),
							'meta' => array (
								'title' => $this->document->getTitle(),
							),
						);
					}
				}
			}
			
			if( ocme()->arr()->get($response, 'status') == 'success' ) {
				/* @var $model ModelExtensionModuleOcmeMfpFilter */
				$model = ocme()->model('filter')->setConditionsList('all');
				
				$response['data']['pagination'] = $model->pagination();
			}
		}
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode( $response ));
	}
	
	protected function render_output( FilterModule $filter ) {
		if( ! $filter->hasGroups() ) {
			return;
		}	
		
		if( $filter->getGrid() ) {
			return $this->render_filters_items($filter);
		}
		
		return $this->load->view(ocme_extension_path('extension/module/ocme_mfp_filters'), array_replace($this->data, array(
			'filter' => $filter,
			'items' => ocme()->arr()->get( $filter->groups(), 'items' ),
		)));
	}
	
	public function conditions() {
		$this->load->language( $this->name );
		
		/* @var $module_id int */
		if( null != ( $module_id = (int) ocme()->request()->input('ocmef_module_id') ) ) {
			/* @var $conditions string */
			if( null != ( $conditions = $this->parseOcmefConditions( ocme()->request()->input('ocmef_conditions') ) ) ) {
				/* @var OcModule $module */
				if( null != ( $module = OcModule::where('code', OcModule::codeByOcVersion( OcModule::CODE_FILTER ))->find( $module_id ) ) ) {
					$this
						->initializeSetting( $module->setting )
						->initializeUrlQuery()
						->initializeFilterData(array(
							'ocmef_remaining_filters' => true,
							'ocmef_conditions' => $conditions
						));

					return $this->response->setOutput($this->render_output( new FilterModule( $this->setting, 'remaining' ) ));
				}
			}
		}
		
		return '';
	}
	
	protected function parseOcmefConditions( $ocmef_conditions ) {
		/* @var $conditions array */
		$conditions = array();
		
		/* @var $groups array */
		$groups = explode(';', $ocmef_conditions);
		
		/* @var $group string */
		foreach( $groups as $group ) {
			/* @var $items array */
			$items = explode(',', $group);
			
			/* @var $vtype string */
			if( 
				null != ( $vtype = array_shift( $items ) )
					&&
				in_array( $vtype, array(
					OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
					OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP,
					OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE,
					OcmeFilterCondition::CONDITION_TYPE_FEATURE,
					OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
					OcmeFilterCondition::CONDITION_TYPE_OPTION,
					OcmeFilterCondition::CONDITION_TYPE_PROPERTY,
				))
			) {
				/* @var $item string */
				foreach( $items as $item ) {
					/* @var $parts array */
					$parts = explode(':', $item);
					
					/* @var $name string */
					$name = array_shift( $parts );
					
					/* @var $vid int */
					$vid = array_shift( $parts );

					$conditions[$name . ':' . $vtype . ':' . $vid] = array(
						'name' => $name,
						'vtype' => $vtype,
						'vid' => $vid,
					);
				}
			}
		}
		
		return $conditions;
	}
	
	protected function initializeFilterData( array $data = array() ) {
		/* @var $filter_data string */
		if( '' !== ( $filter_data = ocme()->request()->query( 'ocmef_filter_data', '' ) ) ) {
			if( null != ( $filter_data = base64_decode( $filter_data ) ) ) {
				/* @var $filter_data_array */
				parse_str(str_replace('&amp;', '&', $filter_data), $filter_data_array);
				
				if( $filter_data_array ) {
					$data = array_replace( $filter_data_array, $data );
				}
			}
		}
			
		/* @var $pagination_data array */
		$pagination_data = ocme()->model('filter')->paginationData();

		$data = array_replace(array(
			'start' => ( ocme()->arr()->get( $pagination_data, 'page' ) - 1 ) * ocme()->arr()->get( $pagination_data, 'limit' ),
			'limit' => ocme()->arr()->get( $pagination_data, 'limit' ),
		), $this->applyExtraParamsToFilterData( $data ));
		
		ocme()->model('filter')->initializeData( $data );
		
		return $this;
	}
	
	protected function initializeUrlQuery( array $data = array() ) {
		/* @var $url_query string */
		if( '' !== ( $url_query = ocme()->request()->query( 'ocmef_url_query' ) ) ) {
			if( null != ( $url_query = base64_decode( $url_query ) ) ) {
				/* @var $url_query_array */
				parse_str(str_replace('&amp;', '&', $url_query), $url_query_array);

				if( $url_query_array ) {
					if( ocme()->arr()->has( $url_query_array, 'route' ) ) {
						ocme()->arr()->set( $url_query_array, 'ocmef_source_route', ocme()->arr()->get( $url_query_array, 'route' ) );
						ocme()->arr()->forget( $url_query_array, 'route' );
					}
					
					ocme()->oc()->registry()->get('request')->get = array_replace( ocme()->oc()->registry()->get('request')->get, $url_query_array, $data );
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @return json
	 */
	public function values() {
		/* @var $response array */
		$response = array(
			'status' => 'error',
		);
		
		/* @var $module_id int */
		if( null != ( $module_id = (int) ocme()->request()->input('ocmef_module_id') ) ) {
			/* @var OcModule $module */
			if( null != ( $module = OcModule::where('code', OcModule::codeByOcVersion( OcModule::CODE_FILTER ))->find( $module_id ) ) ) {
				$this->initializeSetting( $module->setting );

				/* @var $condition array|null */
				$condition = null;

				/* @var $ocme_filter_condition OcmeFilterCondition */
				$ocme_filter_condition = null;
				
				/* @var $model ModelExtensionModuleOcmeMfpFilter */
				$model = ocme()->model('filter')->setConditionsList('remaining');

				/* @var $condition_id int */
				if( null != ( $condition_id = (int) ocme()->request()->input('ocmef_condition_id') ) ) {
					/* @var $ocme_filter_condition OcmeFilterCondition */
					if( null != ( $ocme_filter_condition = OcmeFilterCondition::where('module_id', $module_id)->checkStatus()->find( $condition_id ) ) ) {
						$condition = $model->createCondition( $this->setting, $ocme_filter_condition );
					}
				} else if( 
					/* @var $condition_type string */
					null != ( $condition_type = (string) ocme()->request()->input('ocmef_condition_type') ) 
						&&
					/* @var $vid int */
					null != ( $vid = (int) ocme()->request()->input('ocmef_vid') )
						&&
					/* @var $vtype string */
					null != ( $vtype = ocme()->request()->input('ocmef_vtype') )
				) {
					$condition = $model->makeCondition( $this->setting, $vtype, $vid );
				}

				if( $condition ) {
					/* @var $filter_data array */
					$filter_data = array();
					
					/* @var $where_conditions array */
					$where_conditions = array();

					/* @var $phrase string */
					if( '' !== ( $phrase = ocme()->request()->query( 'ocmef_phrase', '' ) ) ) {
						$where_conditions['phrase'] = $phrase;
						$filter_data['ocmef_live_filter'] = true;
					} else {
						$filter_data['ocmef_remaining_values'] = true;
					}
					
					$this->initializeFilterData( $filter_data );
					
					/* @var $values array */
					$values = null;
		
					/* @var $filter FilterModule */
					$filter = new FilterModule( $this->setting, null );
					
					switch( ocme()->arr()->get( $condition, 'condition_type' ) ) {
						case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE :
						case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP :
						case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP :
						case OcmeFilterCondition::CONDITION_TYPE_OPTION : {
							/* @var $query \Illuminate\Database\Eloquent\Builder */
							$query = $model->buildConditionsQuery( $this->setting, array(
								'conditions' => array(
									'vid' => (int) ocme()->request()->input('ocmef_vid'),
									'vtype' => ocme()->request()->input('ocmef_vtype'),
								)
							))->limit( 1 );
							
							// reset sorting because it isn't needed
							$query->orders = null;
							
							/* @var $condition Condition */
							if( 
								null != ( $condition = current( $model->setUpConditions( $filter, $this->setting, $query ) ) ) 
									&& 
								( $condition->isValuable() || $condition->withAutocomplete() )
							) {
								$values = $model->getConditionValues( 
									$condition, 
									array(
										'vid' => $condition->getConfig('vid'),
									), 
									$where_conditions
								);
							}
							
							break;
						}
						case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE :
						case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : {
							/* @var $condition Condition */
							$condition = new Condition( $filter, $this->setting, $condition );
							
							if( $condition->isValuable() || $condition->withAutocomplete() ) {
								$values = $model->getConditionValues( 
									$condition, 
									array(), 
									$where_conditions
								);
							}
							
							break;
						}
					}
					
					if( ! is_null( $values ) ) {
						$response = array(
							'status' => 'success',
							'data' => array(
								'values' => $values
							)
						);
					}
				}
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode( $response ));
	}
	
	protected function isAllowedOrigin(&$route, &$args, $x = false) {
		/* @var $backtrace array */
		$backtrace = array_slice( array_map(function( $item ){
			return $item['class'] . '::' . $item['function'];
		}, array_filter( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 25 ), function( $item ){
			if( ! isset( $item['function'] ) ) {
				return false;
			}
			
			if( ! isset( $item['class'] ) ) {
				return false;
			}
			
			if( strpos( $item['class'], 'Controller' ) === 0 ) {
				return true;
			}
			
			if( strpos( $item['class'], 'Opencart\\Catalog\\Controller' ) === 0 ) {
				return true;
			}
			
			return false;
		})), 2);
		
		/* @var $prev_controller string */
		if( null != ( $prev_controller = array_shift( $backtrace ) ) ) {
			if( strpos( $prev_controller, get_class( $this ) ) === 0 ) {
				$prev_controller = array_shift( $backtrace );
			}
			
			if( $prev_controller != null ) {
				$prev_controller = str_replace( array( 'Opencart\Catalog\Controller', '\\' ), array( 'Controller', '' ), $prev_controller );
			}
		}
		
		if( ! in_array( $prev_controller, array(
			'ControllerProductCategory::index',
			'ControllerProductManufacturer::info',
			'ControllerProductSearch::index',
			'ControllerProductSpecial::index',
			'ControllerBrowseCatalog::index',
		)) ) {
			return false;
		}
		
		return true;
	}
	
	protected function isAllowedCall(&$route, &$args) {
		if( ! $this->isAllowedOrigin( $route, $args ) ) {
			return false;
		}
		
		/* @var $filter_data array|null */
		if( null === ( $filter_data = $this->getFilterData($route, $args) ) ) {
			return false;
		}
		
		if( 
			! ocme()->arr()->has( $filter_data, array( 'sort', 'order', 'start', 'limit' ) ) 
				&&
			! in_array( $route, array(
				'catalog/product/getTotalProductSpecials',
			))
		) {
			return false;
		}
		
		return true;
	}
	
	protected function getFilterData(&$route, &$args) {
		/* @var $filter_data null|array */
		$filter_data = null;
		
		/**
		 * Support for J3
		 */
		if( $route == 'journal3/filter/getTotalProducts' ) {
			$filter_data = ocme()->model('journal3/filter')->getFilterData();
		} else if( $args ) {
			list( $filter_data ) = $args;
		} else if( in_array( $route, array(
			'catalog/product/getProductSpecials',
			'catalog/product/getTotalProductSpecials',
		))) {
			$filter_data = array();
		}
		
		if( is_array( $filter_data ) ) {
			$filter_data['ocmef_origin_action'] = $route;
		}
		
		return $this->applyExtraParamsToFilterData( $filter_data );
	}
	
	protected function applyExtraParamsToFilterData( $filter_data ) {
		if( is_array( $filter_data ) ) {
			$filter_data['ocmef_source_route'] = ocme()->request()->query('ocmef_source_route', ocme()->request()->ocQueryRoute());
		}
		
		return $filter_data;
	}
	
	public function eventGetProduct( &$route, &$args, &$data ) {
		if( ! ocme()->variable()->get('filter.include_option_prices') ) {
			return null;
		}
		
		if( ! $this->isAllowedOrigin( $route, $args, true ) ) {
			return null;
		}
		
		/* @var $filter_data array */
		if( null == ( $filter_data = ocme()->model('filter')->filterData() ) ) {
			return null;
		}
		
		/* @var $applied_conditions bool */
		$applied_conditions = false;
		
		/* @var $option_price_query \Illuminate\Database\Query\Builder */
		$option_price_query = \Ocme\Model\ProductOptionValue::query()
			->selectRaw("IF(`pov`.`price_prefix` = '+', `pov`.`price`, -`pov`.`price`) AS `price`")
			->addFromAlias('`pov`')
			->where('`pov`.product_id', ocme()->arr()->get($data, 'product_id'));
		
		$option_price_query
			->where(function($q) use( $option_price_query, & $applied_conditions ){
				ocme()->model('filter')
					->applyConditionSubQueryOptionIds(function( $option_id, $option_value_ids ) use( $q, & $applied_conditions ){
						$applied_conditions = true;

						$q->orWhere(function($q) use( $option_id, $option_value_ids ){
							$q->where('`pov`.option_id', $option_id)->whereIn('`pov`.option_value_id', $option_value_ids);
						});
					})
						
					->applyConditionSubQueryOptionTexts(function( $option_id, $option_value_texts ) use( $option_price_query, $q, & $applied_conditions ){
						$applied_conditions = true;
						
						$option_price_query
							->leftJoin('option_value_description AS `ovd`', '`ovd`.option_value_id', '=', '`pov`.option_value_id')
							->where('`ovd`.language_id', $this->config_language_id);
						
						$q
							->where(function($q) use( $option_id, $option_value_texts ){
								$q
									->orWhere(function($q) use( $option_id, $option_value_texts ){
										$q->where('`pov`.option_id', $option_id)->where(function($q) use( $option_value_texts ){
											foreach( $option_value_texts as $v ) {
												$q->orWhere('`ovd`.name', 'LIKE', '%' . $v . '%');
											}
										});
									});
							});
					})

					->applyConditionSubQueryOptionValues(function( $option_id, $option_values ) use( $q, & $applied_conditions ){
						$applied_conditions = true;
						
						$q->orWhere(function($q) use( $option_id, $option_values ) {
							$q->where('`po`.option_id', $option_id)->whereIn('`po`.value', $option_values);
						});
					})

					->applyConditionSubQueryOptionValueTexts(function( $option_id, $option_value_texts ) use( $q, & $applied_conditions ){
						$applied_conditions = true;
						
						$q->orWhere(function($q) use( $option_id, $option_value_texts ){
							$q->where('`po`.option_id', $option_id)->where(function($q) use( $option_value_texts ){
								foreach( $option_value_texts as $v ) {
									$q->orWhere('`po`.value', 'LIKE', '%' . $v . '%');
								}
							});
						});
					})

					->applyConditionSubQueryOptionValueRanges(function( $option_id, $option_value_range, $range_type ) use( $q, & $applied_conditions ){
						$applied_conditions = true;
						
						$q->orWhere(function($q) use( $option_id, $option_value_range, $range_type ){
							$q->where('`po`.option_id', $option_id)->whereBetween('`po`.v' . $range_type, $option_value_range );
						});
					});
			})
			//->limit(1)
					;
		
		/* @var $price_range array */
		if( null == ( $price_range = ocme()->arr()->get( $filter_data, 'ocme_price' ) ) && ! $applied_conditions ) {
			return null;
		}
		
		/* @var $price float */
		$price = ocme()->arr()->get( $data, 'special', ocme()->arr()->get( $data, 'price' ) );
		
		/* @var $price_with_tax float */
		$price_with_tax = $price;
		
		/* @var $tax_class_id int */
		$tax_class_id = (int) ocme()->arr()->get( $data, 'tax_class_id' );
		
		/* @var $config_tax bool */
		$config_tax = ocme()->oc()->registry()->get('config')->get('config_tax');
		
		if( $config_tax ) {
			$price_with_tax = ocme()->oc()->registry()->get('tax')->calculate($price_with_tax, $tax_class_id, $config_tax);
		}
		
		/* @var $sql string */
		$sql = "IF(`pov`.`price_prefix` = '+', `pov`.`price`, -`pov`.`price`)";

		if( ocme()->oc()->registry()->get('config')->get('config_tax') ) {
			/* @var $percent_tax_query */
			$percent_tax_query = ocme()->model('filter')->buildTaxColumnQuery( ocme()->db()->newQuery(), 'P', $tax_class_id );

			/* @var $fixed_tax_query */
			$fixed_tax_query = ocme()->model('filter')->buildTaxColumnQuery( ocme()->db()->newQuery(), 'F', $tax_class_id );

			$sql = sprintf(
				'(%s * ( 1 + IFNULL((%s), 0) / 100) + IFNULL((%s), 0) ) * %s', 
				$sql, 
				ocme()->db()->queryToRawSql( $percent_tax_query ), 
				ocme()->db()->queryToRawSql( $fixed_tax_query ), 
				ocme()->model('filter')->getCurrencyValue()
			);
		}
		
		if( $price_range ) {			
			/* @var $min float */
			$min = (float) ocme()->arr()->get( $price_range, 'min' );

			/* @var $max float */
			$max = (float) ocme()->arr()->get( $price_range, 'max' );

			if( ! ( $price_with_tax >= $min && $price_with_tax <= $max ) ) {
				if( $price_with_tax < $min ) {
					$option_price_query->orderBy('price', 'ASC')->having('price_with_tax', '>=', $min - $price_with_tax);
				} else {
					$option_price_query->orderBy('price', 'DESC')->having('price_with_tax', '<=', $max - $price_with_tax);
				}
			}
		} else {
			$option_price_query->orderBy('price', 'ASC');
		}

		$option_price_query->selectRaw("( " . $sql . " ) as `price_with_tax`");
		
		if( $applied_conditions ) {
			$option_price_query->groupBy('`pov`.option_id');			
			
			$option_price_query = ocme()->db()->newQuery()
				->selectRaw('SUM(price) AS price')
				->selectRaw('SUM(price_with_tax) AS price_with_tax')
				->from( ocme()->db()->raw( '(' . ocme()->db()->queryToRawSql($option_price_query) . ') AS `v`' ) );
		} else {
			$option_price_query->limit(1);
		}
		
		/* @var $option_price \Ocme\Model\ProductOptionValue */
		if( null != ( $option_price = (object) $option_price_query->first() ) ) {
			foreach( array( 'price', 'special' ) as $key ) {
				if( ocme()->arr()->has( $data, $key ) ) {
					$data[$key] += $option_price->price;
				}
			}
		}
	}
	
	public function getProducts( &$route, &$args ) {
		if( $this->isAllowedCall( $route, $args ) ) {
			return ocme()->model('filter')->setOriginRoute( $route )->getProducts();
		}
	}
	
	public function getTotalProducts( &$route, &$args ) {
		if( $this->isAllowedCall( $route, $args ) ) {
			/* @var $filter_data array|null */
			if( null === ( $filter_data = $this->getFilterData($route, $args) ) ) {
				return 0;
			}
			
			return ocme()->model('filter')->initializeData( $filter_data )->setOriginRoute( $route )->getTotalProducts();
		}
	}
}