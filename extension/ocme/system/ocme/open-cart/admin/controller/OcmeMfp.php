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

use	Ocme\Model\OcmeVariable,
	Ocme\Model\OcmeFilterCondition,
	Ocme\Model\Attribute,
	Illuminate\Validation\Validator;

trait OcmeMfp {
	
	use \Ocme\OpenCart\Admin\Controller;
	use \Ocme\OpenCart\Admin\Traits\OcmeMfp;
	use \Ocme\OpenCart\Admin\Traits\Search;
	use \Ocme\OpenCart\Admin\Traits\Attribute;
	use \Ocme\Support\Traits\Minify;
	
	protected function initTrait() {
		$this->name = 'extension/module/ocme_mfp';
		$this->path = 'extension/module/ocme_mfp';
		
		$this->cache_path_js = 'view/ocme/javascript/cache';
		$this->cache_path_css = 'view/ocme/stylesheet/cache';
	}
	
	public function eventStartup() {
		if( self::$startup_initialized ) return;
		
		self::$startup_initialized = true;
		
		ocme_startup( $this->registry );
	}
	
	////////////////////////////////////////////////////////////////////////////
		
	public function index() {
		$this->checkInstallation();
		
		$this->data['hide_tabs'] = true;
		
		$this
			->checkCacheDirs()
			->initialize()
			->render('index');
	}
	
	public function flush_cache() {
		/* @var $response array */
		$response = array(
			'status' => 'error',
		);
		
		if( $this->validateAccess() ) {
			$this->flushCacheDirs();
			
			$response['status'] = 'success';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode( $response ));
	}
	
	public function config() {
		$this->data['action'] = ocme()->url()->adminLink($this->path.'/config');
		$this->data['action_flush_cache'] = ocme()->url()->adminLink($this->path.'/flush_cache');
		
		if( ocme()->request()->methodIsPost() && $this->validateAccess() ) {
			/* @var $variables array */
			foreach( (array) ocme()->request()->post('variables', array()) as $variable_type => $variables ) {
				foreach( $variables as $variable_name => $variable_value ) {
					OcmeVariable::firstOrNew(array(
						'store_id' => null,
						'type' => $variable_type,
						'name' => $variable_name,
					))->fill(array(
						'value' => $variable_value
					))->save();
				}
			}

			ocme()->msg()->success( 'module::global.success_updated' );

			$this->response->redirect(ocme()->url()->adminLink($this->path.'/config'));
		}
		
		return $this
			->initialize()
			->withBreadcrumbs()
			->render('config');
	}
	
	public function seo() {
		/* @var $restricted_names array */
		$restricted_names = array(
			'route', '_route_', 'filter', 'page', 'sort', 'order', 'search', 'sub_category', 'limit', 'path', 'tag', 'description', 'product_id', 'category_id', 'manufacturer_id', 'information_id',
		);
		
		$this->data['action'] = ocme()->url()->adminLink($this->path.'/seo');
		$this->data['text_url_parameter_name_help'] = ocme()->trans('module::global.text_url_parameter_name_help', array( 'restricted_names' => '<code>' . implode( '</code>, <code>', $restricted_names ) . '</code>' ));
		
		if( ocme()->request()->methodIsPost() && $this->validateAccess() ) {
			/* @var $variables array */
			$variables = (array) ocme()->request()->post('variables', array());
			
			/* @var $validator Validator */
			$validator = new Validator( $variables, array(
				'filter_seo_config.url_parameter_name' => 'required|regex:/^[a-z]+$/|not_in:' . implode( ',', $restricted_names ),
			), array(), array(
				'filter_seo_config.url_parameter_name' => ocme()->trans('module::global.text_url_parameter_name'),
			));
			
			if( $validator->fails() ) {
				ocme()->msg()->error( $validator->getMessageBag() );
			} else {
				/* @var $parameters array */
				foreach( $variables as $parameters ) {
					foreach( $parameters as $variable_name => $variable_value ) {
						OcmeVariable::firstOrNew(array(
							'store_id' => null,
							'type' => 'filter_seo_config',
							'name' => $variable_name,
						))->fill(array(
							'value' => $variable_value
						))->save();
					}
				}

				ocme()->msg()->success( 'module::global.success_updated' );

				$this->response->redirect(ocme()->url()->adminLink($this->path.'/seo'));
			}
		}
		
		$this
			->initialize()
			->withBreadcrumbs()
				->render('seo');
	}

	public function connect() {		
		if( ocme()->request()->post('access_token') && $this->validateAccess() ) {
			ocme()->model('setting/setting')->editSetting('ocme_mfp_license', array(
				'ocme_mfp_license' => ocme()->request()->post()
			));
			
			ocme()->msg()->success('module::global.success_activated');

			$this->response->redirect(ocme()->url()->adminLink($this->name));
		}
		
		if( version_compare( VERSION, '4', '>=' ) ) {
			if( ocme()->oc()->registry()->get('config')->get('config_session_samesite') != 'None' ) {
				$this->data['connect_not_available'] = true;
				$this->data['text_connect_not_available'] = ocme()->trans('module::global.text_connect_not_available', array(
					'samesite' => ocme()->oc()->registry()->get('config')->get('config_session_samesite'),
				));
			}
		}
		
		$this->data['action_connect'] = ocme()->api()->connectUrl();
		$this->data['reconnect'] = ocme()->request()->query('reconnect');
		$this->data['data'] = ocme()->api()->connectData(array(
			'version' => ocme()->version(),
			'extension_code' => 'mega_filter_pack',
		));
		
		$this
			->initialize()
			->render('connect');
	}

	public function disconnect() {		
		if( ocme()->request()->methodIsPost() && $this->validateAccess() ) {
			ocme()->license()->disconnect();
			
			ocme()->msg()->success('module::global.success_disconnected');

			$this->response->redirect(ocme()->url()->adminLink($this->path));
		}
		
		$this->data['action'] = ocme()->url()->adminLink($this->path.'/disconnect');
		
		$this
			->initialize()
			->render('disconnect');
	}
	
	public function update() {
		/* @var $version string */
		$version = ocme()->variable()->get('app.version');
		
		if( version_compare( $version, ocme()->version(), '=' ) ) {
			$this->response->redirect(ocme()->url()->adminLink($this->path));
		}
		
		$this->data['update_action'] = ocme()->url()->adminLink($this->path.'/update_process');
		
		$this
			->initialize()
			->render('update');
	}
	
	public function update_process() {
		/* @var $response array */
		$response = array(
			'status' => 'error',
		);
		
		if( ocme()->request()->methodIsPost() && $this->validateAccess() && version_compare( ocme()->variable()->get('app.version'), ocme()->version(), '<' ) ) {
			ocme()->model( 'ocme_mfp' )->update();
			
			$this->flushCacheDirs();
			
			ocme()->msg()->success('module::global.success_updated');
			
			$response['status'] = 'success';
			$response['redirect'] = ocme()->url()->adminLink($this->path.'_filter/indexation', '&autostart=1');
		} else {
			$response['redirect'] = ocme()->url()->adminLink($this->path);
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode( $response ));
	}

	public function about() {
		$this->checkInstallation();
		
		/* @var $license \Ocme\Support\Collection */
		if( null == ( $license = ocme()->license()->shop() ) ) {
			$this->data['action_connect'] = ocme()->url()->adminLink($this->name.'/connect');
		} else {
			$this->data['action_disconnect'] = ocme()->url()->adminLink($this->name.'/disconnect');
			
			if( 'warning' == ( $this->data['license_status'] = $license->get('status') ) ) {
				$this->data['action_reconnect'] = ocme()->url()->adminLink($this->name.'/connect', 'reconnect=1');
			}
			
			foreach( $license->get('messages', array()) as $message ) {
				ocme()->msg()->info( ocme()->data()->get( $message, 'msg' ) );
			}
		}
		
		$this->data['extension_version'] = ocme()->version();
		
		$this
			->initialize()
			->render('about');
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	public function autocomplete() {
		/* @var $types array */
		$types = array(
			'attributes' => OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
			'attribute_groups' => OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP,
			'options' => OcmeFilterCondition::CONDITION_TYPE_OPTION,
			'filter_groups' => OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
			//'features' => OcmeFilterCondition::CONDITION_TYPE_FEATURE,
		);
		
		/* @var $type string */
		$type = ocme()->request()->input( 'type' );
		
		/* @var $key string */
		$key = str_replace( 'module_', '', $type );
		
		/* @var $condition_type string */
		$condition_type = ocme()->arr()->first( $types, function($v, $k) use( $key ) {
			return $k == $key;
		});
		
		/* @var $q string */
		$q = '%' . ocme()->request()->input( 'q' ) . '%';
		
		/* @var $response array */
		$response = array(
			'status' => 'success',
		);
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = null;
		
		if( $condition_type ) {			
			/* @var $class string */
			$class = '\\Ocme\\Model\\' . ocme()->str()->studly( $condition_type );
			
			/* @var $query \Illuminate\Database\Eloquent\Builder */
			$query = $class::addFromAlias('`ct`')->withDescription()->where('name', 'LIKE', $q)->limit(10);
			
			if( $key == 'attributes' ) {
				$query->with(array(
					'attribute_group' => function($q){
						$q->withDescription();
					}
				));
			}
			
			if( ocme()->str()->startsWith( $type, 'module_' ) ) {
				$query->whereExists(function($q) use( $condition_type ){					
					$q
						->select(ocme()->db()->raw(1))
						->from('ocme_filter_condition AS `ofc`')
						->where('`ofc`.condition_type', '=', $condition_type)
						->whereColumn('`ofc`.record_id', '`ct`.' . $condition_type . '_id');
				});
			}
			
			$response['data'] = $query->get()->map(function($v) use( $key ){
				$values = array(
					'id' => $v->option_id,
					'name' => htmlspecialchars_decode( $v->name ),
				);
				
				if( $key == 'attributes' ) {
					$values['attribute_group'] = array(
						'id' => $v->attribute_group_id,
						'name' => $v->attribute_group->name
					);
				}
				
				return $values;
			});
		} else {
			$response['status'] = 'error';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode( $response ));
	}
	
	protected function addBaseAssets() {		
		/* @var $path_catalog string */
		$path_catalog = defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG;
		
		$this->addStyle($path_catalog . 'catalog/view/ocme/javascript/plugins/bootstrap/css/bootstrap.css', 'header');
		$this->addStyle('view/ocme/stylesheet/common.css', 'header');
		$this->addStyle('view/ocme/stylesheet/style.css', 'header');
		
		$this->addStyle('view/ocme/javascript/plugins/sweetalert2/sweetalert2.min.css');
		$this->addScript('view/ocme/javascript/plugins/sweetalert2/sweetalert2.min.js');

		return $this;		
	}
	
	protected function addBaseLibraries() {		
		/* @var $path_catalog string */
		$path_catalog = defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG;
		
		$this->addStyle($path_catalog . 'catalog/view/ocme/stylesheet/plugins/fontawesome/css/all.css');
		
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/vue-select/vue-select.js');
		$this->addStyle($path_catalog . 'catalog/view/ocme/javascript/plugins/vue-select/vue-select.css');
		
		$this->addStyle($path_catalog . 'catalog/view/ocme/javascript/plugins/vue-slider/default.css');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/vue-slider/vue-slider-component.umd.js');
		
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/vue-datepicker/datepicker.min.js');
		$this->addStyle($path_catalog . 'catalog/view/ocme/javascript/plugins/vue-datepicker/datepicker.min.css');
		
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/scrollTo/jquery.scrollTo.min.js');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/typeahead/bloodhound.js');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/typeahead/typeahead.jquery.js');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/helpers/b64.js');
		$this->addScript('view/ocme/javascript/helpers/jse.js');
		
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/components/autocomplete.js');
		$this->addStyle($path_catalog . 'catalog/view/ocme/stylesheet/autocomplete.css');
		
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/helpers/html.js');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/helpers/form.js');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/helpers/str.js');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/utils.js');
		$this->addScript($path_catalog . 'catalog/view/ocme/javascript/components/select.js');
	}
	
	// Events //////////////////////////////////////////////////////////////////
	
	protected function scriptsByRoute( $route, $type ) {
		/* @var $path_catalog string */
		$path_catalog = defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG;
		
		/* @var $scripts array */
		$scripts = array();
		
		switch( $route ) {
			case 'catalog/product/add' :
			case 'catalog/product/edit' :
			case 'catalog/product|form' :
			case 'catalog/product.form' : {
				$scripts[] = 'view/ocme/javascript/field.js';
				$scripts[] = 'view/ocme/javascript/plugins/verte/verte.js';
				$scripts[] = 'view/ocme/javascript/components/colorpicker.js';
				$scripts[] = 'view/ocme/javascript/plugins/sortable/sortable.min.js';
				$scripts[] = 'view/ocme/javascript/plugins/vue-draggable/vue-draggable.umd.min.js';
				$scripts[] = 'view/ocme/javascript/product/attribute.js';
				$scripts[] = 'view/ocme/javascript/product/attributes.js';
				
				break;
			}
			case 'catalog/attribute' : {
				$scripts[] = 'view/ocme/javascript/attribute/filter.js';
				$scripts[] = 'view/ocme/javascript/attribute/list.js';
				
				break;
			}
			case 'catalog/attribute/add' :
			case 'catalog/attribute/edit' :
			case 'catalog/attribute|form' :
			case 'catalog/attribute.form' : {
				$scripts[] = 'view/ocme/javascript/attribute/form.js';
				
				break;
			}
			case 'catalog/attribute_group' : {
				$scripts[] = 'view/ocme/javascript/attribute-group/filter.js';
				
				break;
			}
			case ocme_extension_path($this->path.'/attribute_value') :
			case ocme_extension_path($this->path.'/attribute_values') : {
				$scripts[] = 'view/ocme/javascript/attribute-value/filter.js';
				
				break;
			}
			case ocme_extension_path($this->path.'/attribute_value_add') :
			case ocme_extension_path($this->path.'/attribute_value_edit') : {
				$scripts[] = 'view/ocme/javascript/field.js';
				$scripts[] = 'view/ocme/javascript/plugins/verte/verte.js';
				$scripts[] = 'view/ocme/javascript/components/colorpicker.js';
				
				break;
			}
			case ocme_extension_path($this->path.'_filter') : {
				$scripts[] = 'view/ocme/javascript/field.js';
				$scripts[] = ('view/ocme/javascript/helpers/pre-def-opts.js');
				$scripts[] = ('view/ocme/javascript/plugins/bootstrap-select/js/bootstrap-select.js');
				$scripts[] = ('view/ocme/javascript/plugins/bootstrap-select/js/ajax-bootstrap-select.js');

				$scripts[] = ('view/ocme/javascript/plugins/vue-layout/vue-grid-layout.umd.min.js');

				$scripts[] = ('view/ocme/javascript/plugins/sortable/sortable.min.js');
				$scripts[] = ('view/ocme/javascript/plugins/vue-draggable/vue-draggable.umd.min.js');

				$scripts[] = 'view/ocme/javascript/layout-structure-builder-column.js';
				$scripts[] = 'view/ocme/javascript/layout-structure-builder-row.js';
				$scripts[] = 'view/ocme/javascript/layout-structure-builder.js';

				$scripts[] = 'view/ocme/javascript/slider.js';
				$scripts[] = 'view/ocme/javascript/components/sizes.js';
				$scripts[] = 'view/ocme/javascript/components/iconpicker.js';
				$scripts[] = 'view/ocme/javascript/components/icon.js';
				$scripts[] = 'view/ocme/javascript/condition.js';
				$scripts[] = 'view/ocme/javascript/input.js';
				$scripts[] = 'view/ocme/javascript/textarea.js';
				$scripts[] = 'view/ocme/javascript/datepicker.js';
				$scripts[] = 'view/ocme/javascript/select.js';
				$scripts[] = 'view/ocme/javascript/multilanguage-text.js';
				$scripts[] = 'view/ocme/javascript/properties.js';
				$scripts[] = 'view/ocme/javascript/conditions.js';
				$scripts[] = 'view/ocme/javascript/filter-widget.js';
				$scripts[] = 'view/ocme/javascript/filter-configuration.js';
			
				$scripts[] = 'view/ocme/javascript/plugins/jquery-ui/jquery-ui.min.js';
				$scripts[] = 'view/ocme/javascript/plugins/jquery/sticky.js';
				$scripts[] = 'view/ocme/javascript/plugins/iconpicker/fontawesome-iconpicker.min.js';
				$scripts[] = 'view/ocme/javascript/plugins/verte/verte.js';
				$scripts[] = $path_catalog . 'catalog/view/ocme/javascript/components/msg.js';
				$scripts[] = $path_catalog . 'catalog/view/ocme/javascript/components/pagination.js';
				$scripts[] = 'view/ocme/javascript/components/tree.js';
				$scripts[] = 'view/ocme/javascript/components/status.js';
				$scripts[] = 'view/ocme/javascript/components/btn-group.js';
				$scripts[] = 'view/ocme/javascript/components/iconpicker.js';
				$scripts[] = 'view/ocme/javascript/components/colorpicker.js';
				$scripts[] = 'view/ocme/javascript/module.js';
				$scripts[] = 'view/ocme/javascript/sticky.js';
				$scripts[] = 'view/ocme/javascript/layout-structure-builder.js';
				$scripts[] = 'view/ocme/javascript/filter.js';
				
				break;
			}
			case ocme_extension_path($this->path.'_filter/indexation') : {
				$scripts[] = 'view/ocme/javascript/helpers/pre-def-opts.js';
				$scripts[] = 'view/ocme/javascript/filter_indexation.js';
				
				break;
			}
		}
		
		if( $scripts && $type == 'footer' ) {
			$this->addBaseLibraries();
			
			$scripts[] = 'view/ocme/javascript/app.js';
		}
		
		foreach( $scripts as $script ) {
			$this->addScript( $script );
		}
	}
	
	protected function stylesByRoute( $route ) {
		/* @var $styles array */
		$styles = array();
		
		switch( $route ) {
			case 'catalog/product/add' :
			case 'catalog/product/edit' :
			case 'catalog/product|form' :
			case 'catalog/product.form' : {
				$styles[] = 'view/ocme/stylesheet/catalog/product.css';
				$styles[] = 'view/ocme/javascript/plugins/verte/verte.min.css';
				$styles[] = 'view/ocme/stylesheet/style.css';
				
				break;
			}
			case ocme_extension_path($this->path.'/attribute_value_add') :
			case ocme_extension_path($this->path.'/attribute_value_edit') : {
				$styles[] = 'view/ocme/javascript/plugins/verte/verte.min.css';
				$styles[] = 'view/ocme/stylesheet/style.css';
				
				break;
			}
			case 'catalog/attribute' :
			case ocme_extension_path($this->path) : 
			case ocme_extension_path($this->path.'_filter/settings') :
			case ocme_extension_path($this->path.'_filter/indexation') :
			case ocme_extension_path($this->path . '/seo') :
			case ocme_extension_path($this->path . '/connect') :
			case ocme_extension_path($this->path . '/disconnect') :
			case ocme_extension_path($this->path . '/config') :
			case ocme_extension_path($this->path . '/update') :
			case ocme_extension_path($this->path . '/about') : {
				$this->addBaseAssets();
				
				break;
			}
			case ocme_extension_path($this->path.'_filter') : {				
				$styles[] = 'view/ocme/javascript/plugins/bootstrap-select/css/bootstrap-select.min.css';
				$styles[] = 'view/ocme/javascript/plugins/bootstrap-select/css/ajax-bootstrap-select.min.css';
				$styles[] = 'view/ocme/stylesheet/layout-structure-builder.css';
			
				$styles[] = 'view/ocme/javascript/plugins/iconpicker/fontawesome-iconpicker.min.css';
				$styles[] = 'view/ocme/javascript/plugins/verte/verte.min.css';
				
				break;
			}
		}
		
		if( $styles ) {
			$this->addBaseAssets();
		}
		
		foreach( $styles as $style ) {
			$this->addStyle( $style );
		}
	}
	
	// Event's proxy ///////////////////////////////////////////////////////////
	
	protected function eventAction($type, &$route, &$args, &$output = null) {
		/* @var $path_catalog string */
		$path_catalog = defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG;
		
		/* @var $url_route string */
		$url_route = ocme()->request()->query( 'route' );
		
		switch( $route ) {
			case 'common/header' : {
				$this->scriptsByRoute( $url_route, 'header' );
				$this->stylesByRoute( $url_route );
				
				if( ! empty( $this->minifier_js ) ) {
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/lodash/lodash.min.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/popper/popper.min.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/polyfill/ResizeObserver.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/vue/vue.min.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/plugins/vue/vue-resource.min.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/framework.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/ocme.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/config.js', 'header');
					$this->addScript($path_catalog . 'catalog/view/ocme/javascript/trans.js', 'header');
					
					/* @var $with_trans array */
					$with_trans = array( 'module::global' );
					
					switch( true ) {
						case ocme()->str()->startsWith( $url_route, ocme_extension_path( 'extension/module/ocme_mfp/filter' ) ) : {
							$with_trans[] = 'module::filter'; 
							
							break;
						}
						case ocme()->str()->startsWith( $url_route, ocme_extension_path( 'extension/module/ocme_mfp/attribute' ) ) : {
							$with_trans[] = 'module::attribute'; 
							
							break;
						}
						case ! ocme()->str()->startsWith( $url_route, ocme_extension_path( 'extension/module/ocme_mfp' ) ) : {
							$with_trans[] = implode( '/', array_slice( explode( '/', $url_route ), 0, 2 ) ); 
							
							break;
						}
					}
					
					$this->addScript($this->js(array('with_trans' => implode(';', $with_trans))), 'header');
					
					/* @var $script string */
					foreach( $this->minifyJS('header') as $script ) {
						$this->document->addScript( $script );
					}
				}
				
				if( ! empty( $this->minifier_css['header'] ) ) {
					/* @var $script string */
					foreach( $this->minifyCSS('header') as $style ) {
						$this->document->addStyle( $style );
					}
				}
				
				break;
			}
			case 'common/footer' : {
				$this->scriptsByRoute( $url_route, 'footer' );
				$this->stylesByRoute( $url_route );
		
				/* @var $body string */
				$body = '';
				
				if( ! empty( $this->minifier_css['footer'] ) ) {
					$body .= implode("\n", array_map(function($style){
						return '<link href="' . $style . '" type="text/css" rel="stylesheet" />';
					}, $this->minifyCSS('footer')));
				}
				
				if( ! empty( $this->minifier_js['footer'] ) ) {					
					$body .= implode("\n", array_map(function($script){
						return '<script type="text/javascript" src="' . $script . '"></script>';
					}, $this->minifyJS('footer')));
				}
				
				if( $body != '' ) {
					$output = str_replace('</body>', $body . '</body>', $output);
				}
				
				break;
			}
		}
	}
	
	protected function eventModel($type, &$route, &$args, &$output = null) {
		/* @var $parts array */
		$parts = explode( '/', $route );
		
		/* @var $method string */
		$method = array_pop( $parts );
		
		/* @var $event string */
		$event = 'event' . ucfirst( $type ) . ucfirst( $method );
		
		switch( $route ) {
			case 'catalog/product/addProduct' :
			case 'catalog/product/editProduct' :
			case 'catalog/product/copyProduct' :
			case 'catalog/product/deleteProduct' : {
				ocme()->model('filter')->{$event}( $args, $output );
				ocme()->model('option')->{$event}( $args, $output );
				ocme()->model('attribute')->{$event}( $args, $output );
				
				break;
			}
			case 'catalog/option/addOption' :
			case 'catalog/option/editOption' :
			case 'catalog/option/deleteOption' : {
				ocme()->model('option')->{$event}( $args, $output );
				
				break;
			}
			case 'catalog/attribute/addAttribute' :
			case 'catalog/attribute/editAttribute' :
			case 'catalog/attribute/deleteAttribute' : {
				ocme()->model('attribute')->{$event}( $args, $output );
				
				break;
			}
			case 'catalog/attribute_group/deleteAttributeGroup' : {
				ocme()->model('attribute')->{$event}( $args, $output );
				
				break;
			}
			case 'catalog/filter/deleteFilter' : {
				ocme()->model('filter')->{$event}( $args, $output );
				
				break;
			}
			case 'catalog/attribute/getAttributes' :
			case 'catalog/attribute/getTotalAttributes' :
			case 'catalog/attribute_group/getAttributeGroups' :
			case 'catalog/attribute_group/getTotalAttributeGroups' : {
				return ocme()->model('attribute')->{$event}( $args, $output );
			}
		}
	}
	
	public function eventView($type, &$route, &$data, &$output) {
		/* @var $operations array */
		$operations = array();
		
		if( $type == 'after' && in_array( $route, array( 'catalog/attribute_form', 'catalog/attribute_list', 'catalog/attribute_group_list' ) ) ) {
			if( ocme()->oc()->isV4() ) {
				if( in_array( $route, array( 'catalog/attribute_list', 'catalog/attribute_group_list' ) ) ) {
					$operations['<form'] = '<div id="ocme-app"><form';
					$operations['</form>'] = '</form></div>';
				} else {
					$operations['<div class="card">'] = '<div id="ocme-app" class="card">';
				}
			} else {
				$operations['<div class="panel panel-default">'] = '<div id="ocme-app" class="panel panel-default">';
			}
		}
		
		switch( true ) {
			case $type == 'before' && $route == 'catalog/attribute_form' : {
				/* @var $attribute Attribute */
				$attribute = Attribute::firstOrNew(array(
					'attribute_id' => ocme()->request()->query('attribute_id')
				));
				
				return ocme()->oc()->view('extension/module/ocme_mfp/catalog_attribute_form', array_replace($data, array(
					'ocme_form_action' => ocme()->url()->adminLink( 
						ocme()->oc()->isVersion('4.0.2.0', '>=') ? 'catalog/attribute.save' : (
							ocme()->oc()->isV4() ? 'catalog/attribute|save' : ocme()->request()->ocQueryRoute()
						), ocme()->arr()->except( ocme()->request()->query(), 'route' ) 
					),
					'ocme_oc' => ocme()->oc()->toJS(),
					'ocme_attribute' => ocme()->arr()->getAsVue( $attribute->toArray() ),
					'ocme_descriptions' => ocme()->arr()->getAsVue( $attribute->descriptions->toArray() ),
					'ocme_attribute_group_id' => ocme()->request()->post('attribute_group_id', $attribute->attribute_group_id),
					'ocme_config' => ocme()->variable()->getAsVue('attribute'),
				)));
			}
			case $type == 'before' && $route == 'catalog/attribute_group_list' : {
				$this->addExtraUrlParametersToData($data, array( 'filter_name', 'filter_attribute_group_id' ), array( 'add', 'sort_name', 'sort_sort_order', 'attribute_groups.edit', 'pagination' ));
				
				break;
			}
			case $type == 'before' && $route == 'catalog/attribute_list' : {
				$this->addExtraUrlParametersToData($data, array( 'filter_name' ), array( 'add', 'sort_name', 'sort_attribute_group', 'sort_sort_order', 'attributes.edit', 'pagination' ));
				
				break;
			}
			case $type == 'after' && $route == 'catalog/attribute_list' : {
				$operations['<thead>'] = sprintf(
					'<thead is="ocme-attribute-filter" sort="%s" order="%s" sort_name="%s" sort_attribute_group="%s" sort_sort_order="%s">',
					ocme()->arr()->get( $data, 'sort' ),
					ocme()->arr()->get( $data, 'order' ),
					ocme()->arr()->get( $data, 'sort_name' ),
					ocme()->arr()->get( $data, 'sort_attribute_group' ),
					ocme()->arr()->get( $data, 'sort_sort_order' )
				);
				
				break;
			}
			case $type == 'after' && $route == 'catalog/attribute_group_list' : {
				$operations['<thead>'] = sprintf(
					'<thead is="ocme-attribute-group-filter" sort="%s" order="%s" sort_name="%s" sort_sort_order="%s">',
					ocme()->arr()->get( $data, 'sort' ),
					ocme()->arr()->get( $data, 'order' ),
					ocme()->arr()->get( $data, 'sort_name' ),
					ocme()->arr()->get( $data, 'sort_sort_order' )
				);
				
				break;
			}
			case $type == 'after' && $route == 'catalog/product_form' : {
				$tab_ocme = '<div class="tab-pane ocme ocme-bootstrap" id="tab-attribute-ocme"><div id="ocme-app">' .
					sprintf('<ocme-product-attributes 
						license="%s" 
						displayed_values_separator="%s"
						b64oc="%s" />',
						base64_encode(json_encode(array_replace( ocme()->license()->shopData(), array(
							'token' => ocme()->license()->token(),
							'version' => ocme()->version(),
						)))),
						ocme()->variable()->store(null)->get('attribute.displayed_values_separator'),
						ocme()->oc()->toJS()
					) . '</div></div><div class="tab-pane" id="tab-attribute">';
				
				if( version_compare( VERSION, '4', '>=' ) ) {
					$operations['tab-attribute" data-bs-toggle="tab" class="nav-link">'] = 'tab-attribute-ocme" data-bs-toggle="tab" class="nav-link ocme ocme-bootstrap">OCME ';
					$operations['<div id="tab-attribute" class="tab-pane">'] = $tab_ocme;
				} else {
					$operations['tab-attribute" data-toggle="tab">'] = 'tab-attribute-ocme" data-toggle="tab">OCME ';
					$operations['<div class="tab-pane" id="tab-attribute">'] = $tab_ocme;
				}
				
				break;
			}
			case $type == 'before' && $route == 'common/column_left' : {				
				if( ocme()->oc()->registry()->get('user')->hasPermission('access', ocme_extension_path('extension/module/ocme_mfp') ) ) {
					if( ! isset( $data['menus'] ) ) {
						$data['menus'] = array();
					}
					
					/* @var $menu_catalog array */
					if( null === ( $menu_catalog = & ocme()->arr()->find( $data['menus'], array( 'id' => 'menu-catalog' ) ) ) ) {
						$menu_catalog = array(
							'id' => 'menu-catalog',
							'icon' => 'fa-tags',
							'name' => ocme()->trans('module::global.text_catalog'),
							'children' => array(),
						);
						
						/* @var $offset int */
						if( null === ( $offset = ocme()->arr()->findKey( $data['menus'], array( 'id' => 'menu-dashboard' ) ) ) ) {
							$offset = 0;
						}
						
						array_splice( $data['menus'], $offset, 0, array( $menu_catalog ) );
						
						$menu_catalog = & ocme()->arr()->find( $data['menus'], array( 'id' => 'menu-catalog' ) );
					}
					
					/* @var $menu_attributes array */
					$menu_attributes = & ocme()->arr()->find( $menu_catalog['children'], function( $item ){
						foreach( ocme()->arr()->get( $item, 'children', array() ) as $children ) {
							if( strpos( ocme()->arr()->get( $children, 'href'), 'catalog/attribute' ) !== false ) {
								return true;
							}
						}
					});
					
					if( is_null( $menu_attributes ) ) {
						$menu_catalog['children'][] = array(
							'name' => ocme()->trans('module::global.text_attributes'),
							'href' => null,
							'children' => array(),
						);
						
						$menu_attributes = & $menu_catalog['children'][count($menu_catalog['children'])-1];
					}
					
					$menu_attributes['children'][] = array(
						'name' => ocme()->trans('module::global.text_attribute_values'),
						'href' => ocme()->url()->adminLink('extension/module/ocme_mfp/attribute_value'),
						'children' => array(),
					);
				}
				
				break;
			}
		}
		
		if( $operations ) {
			$output = str_replace( array_keys( $operations ), array_values( $operations ), $output );
		}
	}
	
	private function addExtraUrlParametersToData( & $data, array $url_parameters, array $keys ) {
		/* @var $url string */
		$url = '';
		
		/* @var $url_parameter string */
		foreach( $url_parameters as $url_parameter ) {
			if( ocme()->request()->hasQuery( $url_parameter ) ) {
				$url .= '&' . $url_parameter . '=' . ocme()->request()->query( $url_parameter );
			}
		}
		
		if( $url !== '' ) {
			/* @var $key string */
			foreach( $keys as $key ) {
				/* @var $parts array */
				$parts = explode('.', $key);

				/* @var $primary string */
				$primary = array_shift( $parts );

				if( ocme()->arr()->get( $data, $primary ) ) {
					if( $primary == 'pagination' ) {
						$data[$primary] = str_replace( array( 
							'&amp;' . ocme()->url()->userTokenParamName() . '=', 
							'&' . ocme()->url()->userTokenParamName() . '=',
						), array( 
							str_replace( '&', '&amp;', $url ) . '&amp;' . ocme()->url()->userTokenParamName() . '=', 
							$url . '&' . ocme()->url()->userTokenParamName() . '=',
						), $data[$primary] );
					} else if( is_array( $data[$primary] ) ) {
						foreach( $data[$primary] as & $item ) {
							foreach( $parts as $subkey ) {
								if( ocme()->arr()->has( $item, $subkey ) ) {
									$item[$subkey] .= $url;
								}
							}
						}
					} else {
						$data[$primary] .= $url;
					}
				}
			}
		}
	}
	
	////////////////////////////////////////////////////////////////////////////

    public function install() {
		$this->eventStartup();
		
		ocme()->model( 'ocme_mfp' )->install();
    }

    public function uninstall() {
		$this->eventStartup();
		
		ocme()->license()->disconnect();
		
		ocme()->model( 'ocme_mfp' )->uninstall();
    }
	
}