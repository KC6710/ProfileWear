<?php namespace Ocme\Module;

use Ocme\Model\OcmeVariable,
	Ocme\Model\OcmeFilterGrid,
	Ocme\Model\OcmeFilterGridCondition;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class Filter {
	
	const POSITION_LEFT = 'left';
	const POSITION_RIGHT = 'right';
	
	/**
	 * @var \Ocme\ModuleSetting
	 */
	protected $setting;
	
	/**
	 * @var array
	 */
	protected $groups = null;
	
	/**
	 * @var string
	 */
	protected $conditions_list;
	
	/**
	 * @var int
	 */
	protected static $global_index = 0;
	
	/**
	 * @var int
	 */
	protected $index;
	
	/**
	 * @var array
	 */
	protected $grid = array();
	
	/**
	 * @var array
	 */
	protected $grid_map = array();
	
	/**
	 * @param \Ocme\ModuleSetting $setting
	 */	
	public function __construct( \Ocme\ModuleSetting $setting, $conditions_list = 'first' ) {
		$this->index = self::$global_index++;		
		$this->setting = $setting;
		$this->conditions_list = $conditions_list;
		
		$this->initGroups();
	}
	
	public static function supportedRoutes() {
		return array(
			'browse/catalog',
			'product/category',
			'product/manufacturer/info',
			'product/search',
			'product/special',
			'product/product',

			// Journal
			'product/catalog',
		);
	}
	
	public function isActiveValue( Filter\Condition $condition, $value ) {
		/* @var $parameter array */
		$parameter = ocme()->arr()->first( ocme()->model('filter')->getUrlParameters(), function($v) use( $condition ){
			return $condition->getConfig('vtype') == ocme()->arr()->get( $v, 'name' ) && $condition->getConfig('vid') == ocme()->arr()->get( $v, 'id' );
		});
		
		if( ! $parameter ) {
			return false;
		}
		
		return in_array( ocme()->arr()->get( $value, 'id' ), (array) ocme()->arr()->get( $parameter, 'values' ) );
	}
	
	/**
	 * @return void
	 */
	protected function initGroups() {
		$this->grid = array();

		/* @var $ocme_filter_grid OcmeFilterGrid */
		foreach( OcmeFilterGrid::where('module_id', $this->getConfig('module_id'))->orderBy('sort_order')->get() as $ocme_filter_grid ) {
			$this->grid[] = ocme()->arr()->only( $ocme_filter_grid->toArray(), array(
				'id', 'parent_id', 'type', 'settings'
			));
		}
		
		/* @var $last_column array */
		$last_column = ocme()->arr()->last( $this->grid, function($v){
			return $v['type'] == 'column';
		});
		
		$this->grid = array_map(function($v) use( $last_column ){
			if( $last_column && $v['type'] == 'column' ) {
				$v['is_last'] = $v['id'] == $last_column['id'];
			}
			
			return $v;
		}, $this->grid);
		
		if( $this->grid ) {
			$this->grid_map = array();
		
			/* @var $ocme_filter_grid_condition OcmeFilterGridCondition */
			foreach( OcmeFilterGridCondition::join('ocme_filter_grid', 'ocme_filter_grid.id', '=', 'ocme_filter_grid_condition.ocme_filter_grid_id')
				->where('ocme_filter_grid.module_id', $this->getConfig('module_id'))
				->orderBy('ocme_filter_grid_condition.sort_order')
				->get() as $ocme_filter_grid_condition
			) {
				$this->grid_map[] = ocme()->arr()->only( $ocme_filter_grid_condition->toArray(), array(
					'ocme_filter_grid_id', 'ocme_filter_condition_id', 'vid', 'vtype', 'vname',
				));
			}
		}
		
		$this->groups = is_null( $this->conditions_list ) ? array() : ocme()->model('filter')->setWithLayout( (bool) $this->grid )->setConditionsList( $this->conditions_list )->getConditions( $this, $this->setting );
	}
	
	/**
	 * @return array
	 */
	public function getGrid() {
		return $this->grid;
	}
	
	/**
	 * @return array
	 */
	public function getGridMap() {
		return $this->grid_map;
	}
	
	/**
	 * @return mixed
	 */
	public function getConfig( $key, $default = null ) {
		return $this->setting->get( $key, $default );
	}
	
	/**
	 * @return bool
	 */
	public function hasAnyFilters() {
		return (bool) ocme()->model('filter')->getUrlParameters();
	}
	
	/**
	 * @return array
	 */
	public function users() {
		/* @var $users array */
		$users = array();
		
		if( ocme()->ocRegistry()->get('customer') && ocme()->ocRegistry()->get('customer')->isLogged() ) {
			$users[] = 'customer';
		}
		
		if( ocme()->ocRegistry()->get('user') && ocme()->ocRegistry()->get('user')->isLogged() ) {
			$users[] = 'user';
		}
		
		return $users;
	}
	
	/**
	 * @return array
	 */
	public function configAsVue() {
		return ocme()->arr()->getAsVue(array(
			'ajax' => $this->ajaxConfig(),
			'widget' => $this->widgetConfig(),
			'refresh' => $this->refreshConfig(),
			'other' => $this->otherConfig(),
		));
	}
	
	/**
	 * @return array
	 */
	public function usersAsVue() {
		return ocme()->arr()->getAsVue( $this->users() );
	}
	
	/**
	 * @return array
	 */
	public function seoConfig() {
		return ocme()->variable()->get( OcmeVariable::TYPE_FILTER_SEO_CONFIG );
	}
	
	/**
	 * @return array
	 */
	public function refreshConfig() {
		return $this->setting->get( 'configuration.refresh' );
	}
	
	/**
	 * @return array
	 */
	public function widgetConfig() {
		return $this->setting->get( 'widget' );
	}
	
	/**
	 * @return array
	 */
	public function otherConfig() {
		return $this->setting->get( 'configuration.other' );
	}
	
	/**
	 * @return array
	 */
	public function ajaxConfig() {
		return array(
			'active' => $this->getConfig('configuration.load_results_via_ajax') == '1',
			'main_selector' => html_entity_decode($this->getConfig('configuration.javascript.main_selector'), ENT_QUOTES, 'UTF-8'),
			'header_selector' => html_entity_decode($this->getConfig('configuration.javascript.header_selector'), ENT_QUOTES, 'UTF-8'),
			'pagination_selector' => html_entity_decode($this->getConfig('configuration.javascript.pagination_selector'), ENT_QUOTES, 'UTF-8'),
			'first_product_selector' => html_entity_decode($this->getConfig('configuration.javascript.first_product_selector'), ENT_QUOTES, 'UTF-8'),
			'breadcrumb_selector' => html_entity_decode($this->getConfig('configuration.javascript.breadcrumb_selector'), ENT_QUOTES, 'UTF-8'),
		);
	}
	
	/**
	 * @return array
	 */
	public function pagination() {
		return ocme()->model('filter')->pagination();
	}
	
	/**
	 * @return string
	 */
	public function paginationAsVue() {
		return ocme()->arr()->getAsVue( $this->pagination() );
	}
	
	public function headerIconLeft() {
		return self::icon( self::POSITION_LEFT, $this->setting->get( 'configuration.icon' ), null, 'ocme-mfp-f-header-icon' );
	}
	
	public function headerIconRight() {
		return self::icon( self::POSITION_RIGHT, $this->setting->get( 'configuration.icon' ), null, 'ocme-mfp-f-header-icon' );
	}
	
	public function iconLeft( $key ) {
		return self::icon( self::POSITION_LEFT, $this->setting->get( $key ), null );
	}
	
	public function iconRight( $key ) {
		return self::icon( self::POSITION_RIGHT, $this->setting->get( $key ), null );
	}
	
	public function getLanguageDirection() {
		return ocme()->ocRegistry()->get('language')->get('direction') == 'ltr' ? 'ltr' : 'rtl';
	}
	
	protected static function determinePosition( $position ) {
		if( 'auto' == $position ) {
			$position = ocme()->ocRegistry()->get('language')->get('direction') == 'ltr' ? 'left' : 'right';
		}
		
		return $position;
	}
	
	public static function icon( $position, $config, $template = null, $classes = array() ) {
		if( ! ocme()->data()->get( $config, 'symbol' ) || ! ocme()->data()->get( $config, 'status' ) ) {
			return '';
		}
		
		/* @var $pos string */
		$pos = self::determinePosition( ocme()->data()->get( $config, 'position', 'auto' ) );
		
		if( $pos != $position ) {
			return '';
		}
		
		/* @var $styles array */
		$styles = array();
		
		foreach( array( 'size' => 'font-size: %spx', 'color' => 'color: %s' ) as $key => $style ) {
			/* @var $val mixed */
			if( null != ( $val = ocme()->data()->get( $config, $key) ) ) {
				$styles[] = sprintf( $style, $val );
			}
		}
		
		if( is_null( $template ) ) {
			$template = '<i class="{icon}"{styles}></i>';
		}
		
		if( ! is_array( $classes ) ) {
			$classes = array( $classes );
		}
		
		$classes[] = 'ocme-mfp-f-icon-' . $pos;
		$classes[] = ocme()->data()->get( $config, 'symbol' );
		
		return str_replace(array(
			'{icon}',
			'{styles}',
		), array(
			implode(' ', $classes),
			$styles ? ' style="' . implode(';', $styles) . '"' : '',
		), $template);
	}
	
	public function wrapConditionsCssClasses( $with_layout = false ) {
		/* @var $classes array */
		$classes = array(
			'ocme-mfp-f-conditions'
		);
		
		if( ! $with_layout ) {
			$classes[] = 'ocme-mfp-f-layout-' . $this->setting->get('configuration.layout');
			
			if( in_array( $this->setting->get('configuration.layout'), array( 'grid' ) ) && $this->setting->get('configuration.layout_cols') ) {
				/* @var $cols array */
				$cols = $this->setting->get('configuration.layout_cols');

				$classes[] = ocme()->model('filter')->generateBreakpointsClassesForCss( $cols, 'filter_global.configuration.layout_cols' );
			}
		} else {
			$classes[] = 'ocme-mfp-f-with-layout';
		}
		
		return implode(' ', $classes);
	}
	
	/**
	 * @return bool
	 */
	public function hasGroups() {
		return (bool) ocme()->arr()->get( $this->groups, 'items' );
	}
	
	/**
	 * @return array
	 */
	public function groups() {
		return $this->groups;
	}
	
	/**
	 * @return int
	 */
	public function getIndex() {
		return $this->index;
	}
	
	/**
	 * @return bool
	 */
	public function withWidgetBackdrop() {
		if( $this->setting->get('widget.status') == '0' ) {
			return false;
		}
		
		if( ! $this->setting->get('widget.backdrop.status') ) {
			return false;
		}
		
		/* @var $devices array */
		$devices = (array) $this->setting->get('widget.backdrop.devices');
		
		if( in_array( '', $devices ) ) {
			return true;
		}
		
		return in_array( ocme()->mdetect()->device(), $devices );
	}
	
	/**
	 * @return string
	 */
	public function customCss() {
		/* @var $css array */
		$css = array();
		
		/* @var $custom_styles string */
		if( null != ( $custom_styles = $this->getConfig('configuration.css.custom_styles') ) ) {
			$css[] = html_entity_decode($custom_styles, ENT_QUOTES, 'UTF-8');
		}
		
		return implode( "\n", $css );
	}
	
	/**
	 * @return array
	 */
	public function getEvents() {
		/* @var $events array */
		$events = array();
		
		/* @var $trigger string */
		foreach( array( 'before_mount', 'mounted', 'before_render', 'after_render' ) as $trigger ) {
			/* @var $global_code string */
			if( null != ( $global_code = ocme()->variable()->get('filter_global_js_hook.' . $trigger) ) ) {
				if( in_array( $trigger, array( 'before_render', 'after_render' ) ) && $this->withLoadResultsViaAjax() ) {
					$events[] = array(
						'trigger' => $trigger,
						'code' => html_entity_decode($global_code, ENT_QUOTES, 'UTF-8'),
					);
				}
			}
			
			/* @var $code string */
			if( null != ( $code = $this->getConfig('configuration.javascript.' . $trigger) ) ) {
				$events[] = array(
					'trigger' => $trigger,
					'code' => html_entity_decode($code, ENT_QUOTES, 'UTF-8'),
				);
			}
		}
		
		return $events;
	}
	
	/**
	 * @return string
	 */
	public function cssClasses() {
		/* @var $classes array */
		$classes = array( 'ocme', 'ocme-mfp-filter' );
		
		$classes[] = sprintf( 'ocme-mfp-filter-index-%s', $this->getIndex() );
		$classes[] = sprintf( 'ocme-mfp-f-position-%s', self::determinePosition( $this->setting->get('position') ) );
		
		if( $this->setting->get('widget.status') == '0' ) {
			$classes[] = 'ocme-mfp-f-without-widget';
		} else {
			$classes[] = sprintf( 'ocme-mfp-f-widget-position-%s', self::determinePosition( $this->setting->get('widget.position') ));
			$classes[] = sprintf( 'ocme-mfp-f-widget-button-position-%s', $this->setting->get('widget.button.position.type') );

			if( $this->setting->get('widget.button.position.type') == 'fixed' ) {
				$classes[] = sprintf( 'ocme-mfp-f-widget-button-position-v-%s', $this->setting->get('widget.button.position.vertical') );
				$classes[] = sprintf( 'ocme-mfp-f-widget-button-position-h-%s', $this->setting->get('widget.button.position.horizontal') );
			}

			if( $this->setting->get('widget.status') == 'resolution' ) {
				$classes[] = sprintf( 'ocme-mfp-f-widget-status-resolution-%s', $this->setting->get('widget.status_resolution') );
			} else if( in_array( $this->setting->get('widget.status'), array( 'auto', 'always' ) ) ) {
				$classes[] = sprintf( 'ocme-mfp-f-widget-status-resolution-%s', $this->setting->get('widget.status') );
			}

			if( $this->withWidgetBackdrop() ) {
				$classes[] = 'ocme-mfp-f-widget-with-backdrop';
			}
		}
		
		if( $this->withLoadResultsViaAjax() ) {
			$classes[] = 'ocme-mfp-f-with-load-results-via-ajax';
		}
		
		if( ocme()->arr()->get( $this->groups(), 'left' ) ) {
			$classes[] = 'ocme-mfp-f-with-more-button';
		}
		
		return implode( ' ', $classes );
	}
	
	/**
	 * @return bool
	 */
	public function withLoadResultsViaAjax() {
		return $this->setting->get('configuration.load_results_via_ajax') == '1';
	}
	
	/**
	 * @return bool
	 */
	public function withMobileCloseButton() {
		return (bool) $this->getConfig('widget.button.close.status');
	}
	
	/**
	 * @return string
	 */
	public function mobileOpenButtonLabel( $default ) {
		/* @var $labels array */
		if( null != ( $labels = (array) $this->getConfig('widget.button.open.text') ) ) {
			if( ! empty( $labels[ocme()->ocRegistry()->get('config')->get('config_language_id')] ) ) {
				return $labels[ocme()->ocRegistry()->get('config')->get('config_language_id')];
			}
		}
		
		return $default;
	}
	
	/**
	 * @return string
	 */
	public function mobileCloseButtonLabel() {
		/* @var $labels array */
		if( null != ( $labels = (array) $this->getConfig('widget.button.close.text') ) ) {
			if( ! empty( $labels[ocme()->ocRegistry()->get('config')->get('config_language_id')] ) ) {
				return $labels[ocme()->ocRegistry()->get('config')->get('config_language_id')];
			}
		}
		
		return '';
	}
	
	/**
	 * @return string
	 */
	public function mobileOpenButtonCssClasses() {
		/* @var $classes array */
		$classes = array( 'ocme-mfp-btn', 'ocme-mfp-f-mobile-btn-' . $this->setting->get( 'module_id' ), 'ocme-mfp-btn-default' );
		
		if( $this->setting->get('widget.status') == 'resolution' ) {
			$classes[] = sprintf( 'ocme-mfp-f-widget-button-resolution-%s', $this->setting->get('widget.status_resolution') );
		} else if( in_array( $this->setting->get('widget.status'), array( 'auto', 'always' ) ) ) {
			$classes[] = sprintf( 'ocme-mfp-f-widget-button-resolution-%s', $this->setting->get('widget.status') );
		}
		
		return implode( ' ', $classes );
	}
	
	/**
	 * @return string
	 */
	public function mobileOpenButtonCssStyles() {
		/* @var $styles array */
		$styles = array();
		
		/* @var $keys array */
		$keys = array();
		
		if( in_array( $this->getConfig('widget.button.position.type'), array( 'fixed', 'inline' ) ) ) {
			$keys['top'] = 'top';
			$keys['right'] = 'right';
			$keys['bottom'] = 'bottom';
			$keys['left'] = 'left';
		} else {
			$keys['right'] = 'top';
		}
		
		/* @var $key string */
		foreach( $keys as $destination => $source ) {
			/* @var $mixed $value */
			$value = $this->getConfig('widget.button.margin.' . $source);
			
			if( $value !== '' && $value !== null ) {
				$styles[] = sprintf( 'margin-%s:%spx', $destination, $value );
			}
		}
		
		foreach( array( 'text_size' => 'font-size:%spx', 'text_color' => 'color:%s', 'background_color' => 'background-color:%s' ) as $key => $template ) {
			$styles[] = sprintf($template, $this->getConfig('widget.button.open.' . $key));
		}
		
		return implode( ';', $styles );
	}
	
	/**
	 * @return string
	 */
	public function mobileCloseButtonCssStyles() {
		/* @var $styles array */
		$styles = array();
		
		foreach( array( 'text_size' => 'font-size:%spx', 'text_color' => 'color:%s', 'background_color' => 'background-color:%s' ) as $key => $template ) {
			$styles[] = sprintf($template, $this->getConfig('widget.button.close.' . $key));
		}
		
		return implode( ';', $styles );
	}
	
}