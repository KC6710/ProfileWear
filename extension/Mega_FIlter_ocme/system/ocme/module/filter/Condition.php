<?php namespace Ocme\Module\Filter;

use Ocme\Module\Filter,
	Ocme\Model\OcmeFilterCondition;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class Condition {
	
	/**
	 * @var Filter
	 */
	protected $filter;
	
	/**
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * @var string
	 */
	protected $uid;
	
	/**
	 * @var ModuleSetting
	 */
	protected $setting;
	
	/**
	 * @param array $config
	 * @param array $data
	 */
	public function __construct( Filter $filter, \Ocme\ModuleSetting $setting, array $config, array $data = array() ) {
		foreach( ocme()->arr()->dot( $config ) as $key => $val ) {
			if( $val === null || ( strpos( $key, 'icon.' ) !== false && $val === '' ) ) {
				ocme()->arr()->set( $config, $key, $setting->get('conditions.'.$key) );
			}
		}
		
		$this->filter = $filter;
		$this->setting = $setting;
		$this->config = $config;
		$this->data = $data;
		$this->uid = uniqid('uid');
		
		if( ! $this->hasConfig( 'vid' ) ) {
			$this->setConfig( 'vid', $this->getConfig( 'id' ) );
		}
		
		if( ! $this->hasConfig( 'vtype' ) ) {
			$this->setConfig( 'vtype', $this->getConfig( 'condition_type' ) );
		}
	}
	
	public function hasAnyItemToDisplay() {
		foreach( (array) $this->getData('values.items') as $item ) {
			if( $this->canDisplayItem( $item ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	public function formatValue( $num ) {
		if( is_null( $num ) ) {
			return '-';
		}
		
		/* @var $units array */
		$units = array( 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y' );
		
		$num = (float) $num;

		for( $i = count( $units ) - 1; $i >= 0; $i-- ) {
			$decimal = pow(1000, $i + 1);

			if( $num <= -$decimal || $num >= $decimal ) {
				return round($num / $decimal, 1) . $units[$i];
			}
		}

		return $num;
	}
	
	/**
	 * @return bool
	 */
	public function canDisplayItem( array $item ) {
		if( ! $this->getConfig('setting.hide_inactive_values') ) {
			return true;
		}
		
		return ocme()->arr()->get( $item, 'total_with_conditions' ) !== 0;
	}
	
	/**
	 * @return string
	 */
	public function componentName() {
		/* @var $component string */
		$component = $this->getConfig( 'condition_type' );
		
		switch( true ) {
			case $this->isRange() : $component = 'range'; break;
			case $this->isSelect() : $component = 'select'; break;
			case $this->isTextInput() : $component = 'text'; break;
			case $this->isVirtualList() : $component = 'list'; break;
		}
		
		return 'ocme-mfp-f-condition-' . $component;
	}
	
	/**
	 * @return string
	 */
	public function paramKeyName() {
		switch( $this->getConfig('condition_type') ) {
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE :
			case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : return $this->getConfig('name');
			default : return $this->getConfig('vtype') . '_' . $this->getConfig('vid');
		}
	}
	
	/**
	 * @return string
	 */
	public function globalKeyName() {
		switch( $this->getConfig('condition_type') ) {
			case OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE : return 'base_attributes';
			case OcmeFilterCondition::CONDITION_TYPE_PROPERTY : return 'properties';
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP :
			case OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE : return 'attributes';
			case OcmeFilterCondition::CONDITION_TYPE_OPTION : return 'options';
			case OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP : return 'filter_groups';
			case OcmeFilterCondition::CONDITION_TYPE_FEATURE : return 'features';
		}
	}
	
	/**
	 * @return bool
	 */
	public function isValuable() {
		return in_array( $this->getConfig('vtype'), array(
			OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE,
			OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE,
			OcmeFilterCondition::CONDITION_TYPE_OPTION,
			OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP,
			OcmeFilterCondition::CONDITION_TYPE_PROPERTY
		)) && ( $this->isSelect() || $this->isVirtualList() );
	}
	
	/**
	 * @return bool
	 */
	public function isRange() {
		/*if( $this->getConfig('condition_type') == OcmeFilterCondition::CONDITION_TYPE_BASE_ATTRIBUTE && $this->getConfig('name') == 'price' && $this->getConfig('type') == 'without_slider' ) {
			return true;
		}
		
		return false;*/
		return in_array( $this->getConfig('type'), array( 'range' ) );
	}
	
	/**
	 * @return bool
	 */
	public function isDateRange() {
		return $this->isRange() && in_array( $this->getConfig('values_type'), array( 'date' ) );
	}
	
	/**
	 * @return bool
	 */
	public function isTimeRange() {
		return $this->isRange() && in_array( $this->getConfig('values_type'), array( 'time' ) );
	}
	
	/**
	 * @return bool
	 */
	public function isDateTimeRange() {
		return $this->isRange() && in_array( $this->getConfig('values_type'), array( 'datetime' ) );
	}
	
	/**
	 * @return bool
	 */
	public function isTextInput() {
		return in_array( $this->getConfig('type'), array( 'text', 'search' ) );
	}
	
	/**
	 * @return bool
	 */
	public function isSelect() {
		return $this->getConfig('type') == 'select';
	}
	
	/**
	 * @return bool
	 */
	public function isRadio() {
		return $this->getConfig('type') == 'radio';
	}
	
	/**
	 * @return bool
	 */
	public function isCheckbox() {
		return $this->getConfig('type') == 'checkbox';
	}
	
	/**
	 * @return bool
	 */
	public function isVirtualList() {
		return $this->isCheckbox() || $this->isRadio();
	}
	
	/**
	 * @return bool
	 */
	public function isActiveValue( $value ) {
		return $this->filter->isActiveValue( $this, $value );
	}
	
	/**
	 * @return bool
	 */
	public function withLiveFilter() {
		return $this->getConfig('setting.live_filter') == '1' && $this->isVirtualList();
	}
	
	/**
	 * @return bool
	 */
	public function withRangeInputs() {
		return $this->isRange() && ! in_array( $this->getConfig('setting.slider_mode'), array( 'only_slider' ));
	}
	
	/**
	 * @return string
	 */
	public function rangeInputsType() {
		if( $this->isDateRange() ) {
			return 'text';
		}
		
		if( $this->isTimeRange() ) {
			return 'text';
		}
		
		if( $this->isDateTimeRange() ) {
			return 'text';
		}
		
		return 'number';
	}
	
	/**
	 * @return bool
	 */
	public function withSlider() {
		return $this->isRange() && in_array( $this->getConfig('setting.slider_mode'), array( 'with_slider', 'only_slider' ) );
	}
	
	/**
	 * @return bool
	 */
	public function withSliderMarks() {
		return $this->withSlider() && $this->getConfig('setting.slider.marks') == '1';
	}
	
	/**
	 * @return bool
	 */
	public function withHeader() {
		return (bool) $this->getConfig('setting.show_header') == '1';
	}
	
	/**
	 * @return bool
	 */
	public function withMoreButton() {
		return $this->getConfig('setting.display_list_of_items') == 'with_more_button' && $this->getData('values.pagination.last_page') > 1;
	}
	
	/**
	 * @return bool
	 */
	public function withCalculateCount() {
		return $this->getConfig('setting.calculate_count') == '1' && ( $this->isSelect() || $this->isVirtualList() );
	}
	
	/**
	 * @return bool
	 */
	public function withButtonSelectDeselectAll() {
		return $this->getConfig('setting.button_select_deselect_all') == '1' && $this->isCheckbox();
	}
	
	/**
	 * @return bool
	 */
	public function withAutocomplete() {
		return \Ocme\Module::validStatus( $this->getConfig('setting.autocomplete.status') ) && $this->isTextInput();
	}
	
	/**
	 * @return bool
	 */
	public function withAutocompleteClearButton() {
		return $this->getConfig('setting.autocomplete.show_clear_button') == '1' && $this->withAutocomplete();
	}
	
	/**
	 * @return bool
	 */
	public function withAutocompleteCalculateCount() {
		return $this->getConfig('setting.autocomplete.calculate_count') == '1' && $this->withAutocomplete();
	}
	
	/**
	 * @return bool
	 */
	public function withAutocompleteShowCountBadge() {
		return $this->getConfig('setting.autocomplete.show_count_badge') == '1' && $this->withAutocompleteCalculateCount();
	}
	
	/**
	 * @return bool
	 */
	public function withValueInput() {
		return ! $this->getConfig('setting.hide_input');
	}
	
	/**
	 * @return bool
	 */
	public function withValueImage( $item ) {
		return in_array( 'image', $this->getConfig('setting.display', array() ) ) && ocme()->data()->get( $item, 'image' );
	}
	
	/**
	 * @return bool
	 */
	public function withValueColor( $item ) {
		return in_array( 'color', $this->getConfig('setting.display', array() ) ) && ocme()->data()->get( $item, 'color' );
	}
	
	/**
	 * @return bool
	 */
	public function withValueCountBadge() {
		return $this->getConfig('setting.show_count_badge') && $this->getConfig('setting.calculate_count');
	}
	
	/**
	 * @return bool
	 */
	public function withSearchButton() {
		return $this->getConfig('setting.show_search_button');
	}
	
	/**
	 * @return bool
	 */
	public function withValueLabel( $item ) {
		/* @var $display array */
		$display = $this->getConfig('setting.display', array());
		
		if( in_array( 'text', $display ) ) {
			return true;
		}
		
		if( in_array( 'image', $display ) && ! ocme()->data()->get( $item, 'image' ) ) {
			return true;
		}
		
		if( in_array( 'color', $display ) && ! ocme()->data()->get( $item, 'color' ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @return string
	 */
	public function customCss() {
		/* @var $css array */
		$css = array();
		
		if( strpos( $this->getConfig('setting.wrap_classes', ''), '-slack' ) ) {
			/* @var $combinations array */
			$combinations = array();
			
			/* @var $display array */
			$display = (array) $this->getConfig( 'setting.display' );
			
			if( $this->withValueInput() ) {
				$combinations['.ocme-mfp-f-values-with-inputs'] = 16;
			}
			
			if( in_array( 'image', $display ) ) {
				$combinations['.ocme-mfp-f-values-with-images'] = $this->getConfig('setting.image_width');
				$css[] = '.ocme-mfp-f-values-with-only-images{grid-template-columns:repeat(auto-fill, minmax(' . ($this->getConfig('setting.image_width')+10) . 'px, 1fr));}';
			}
			
			if( in_array( 'color', $display ) ) {
				$combinations['.ocme-mfp-f-values-with-colors'] = $this->getConfig('setting.color_width');
				$css[] = '.ocme-mfp-f-values-with-only-colors{grid-template-columns:repeat(auto-fill, minmax(' . ($this->getConfig('setting.color_width')+10) . 'px, 1fr));}';
			}
			
			if( in_array( 'text', $display ) ) {
				$combinations['.ocme-mfp-f-values-with-labels'] = 40;
			}
			
			if( $this->withValueCountBadge() ) {
				$combinations['.ocme-mfp-f-values-with-count-badges'] = 40;
			}
			
			if( $combinations ) {
				/* @var $keys array */
				$keys = array_keys( $combinations );
				
				/* @var $results array */
				$results = array( array( array_pop( $keys ) ) );

				/* @var $element string */
				foreach( $keys as $element ) {
					/* @var $element2 string */
					foreach( $results as $element2 ) {
						array_push( $results, array_merge( array( $element ), $element2 ) );
					}
				}
				
				/* @var $combination array */
				foreach( $results as $combination ) {
					/* @var $width int */
					$width = 0;
					
					/* @var $name string */
					$name = implode( '', $combination );
					
					/* @var $key string */
					foreach( $combination as $key ) {
						$width += $combinations[$key];
					}
					
					$css[] = $name.'{grid-template-columns:repeat(auto-fill, minmax(' . ($width+10) . 'px, 1fr));}';
				}
			}
		}
		
		if( $css ) {
			return implode('', array_map(function($v){
				return '.ocme-mfp-f-condition-' . $this->uid . ' ' . $v;
			}, $css));
		}
		
		return '';
	}
	
	/**
	 * @return string
	 */
	public function wrapCssClasses() {
		/* @var $classes array */
		$classes = array( 'ocme-mfp-f-condition', 'ocme-mfp-f-condition-' . str_replace( '_', '-', $this->getConfig('type') ), 'ocme-mfp-f-condition-' . $this->uid );
		
		/* @var $mode string */
		if( null != ( $mode = $this->getConfig('setting.mode') ) ) {
			$classes[] = 'ocme-mfp-f-mode-' . $mode;
		}
		
		if( in_array( ocme()->mdetect()->device(), $this->getConfig('setting.collapsed', array()) ) ) {
			$classes[] = 'ocme-mfp-f-collapsed';
		}
		
		if( $this->isRange() && $this->getConfig('setting.slider.marks') == '1' ) {
			$classes[] = 'ocme-mfp-f-slider-with-marks';
		}
		
		return implode(' ', $classes);
	}
	
	/**
	 * @return string
	 */
	public function wrapValuesCssClasses() {
		/* @var $classes array */
		$classes = array();
		
		if( $this->getConfig('setting.layout') == 'grid' && strpos( $this->getConfig('setting.wrap_classes'), '-slack' ) ) {			
			/* @var $display array */
			$display = (array) $this->getConfig( 'setting.display' );
			
			if( $this->withValueInput() ) {
				$classes[] = 'ocme-mfp-f-values-with-inputs';
			}
			
			if( in_array( 'image', $display ) ) {
				$classes[] = 'ocme-mfp-f-values-with-images';
			}
			
			if( in_array( 'color', $display ) ) {
				$classes[] = 'ocme-mfp-f-values-with-colors';
			}
			
			if( in_array( 'text', $display ) ) {
				$classes[] = 'ocme-mfp-f-values-with-labels';
			}
			
			if( $this->withValueCountBadge() ) {
				$classes[] = 'ocme-mfp-f-values-with-count-badges';
			}
			
			if( count( $classes ) == 1 ) {
				$classes = array_map(function($cls){
					return str_replace('ocme-mfp-f-values-with-', 'ocme-mfp-f-values-with-only-', $cls);
				}, $classes);
			}
		}
		
		array_unshift( $classes, $this->getConfig('setting.wrap_classes') );
		array_unshift( $classes, 'ocme-mfp-f-layout-' . $this->getConfig('setting.layout') );
		array_unshift( $classes, 'ocme-mfp-f-values-body' );
		
		return implode(' ', $classes);
	}
	
	/**
	 * @return string
	 */
	public function wrapValueCssClasses( $value ) {
		/* @var $classes array */
		$classes = array( 'ocme-mfp-f-value' );
		
		if( $this->withValueInput() ) {
			$classes[] = 'ocme-mfp-f-value-with-input';
		} else {
			$classes[] = 'ocme-mfp-f-value-without-input';
		}
		
		if( $this->withValueImage( $value ) ) {
			$classes[] = 'ocme-mfp-f-value-with-image';
		}
		
		if( $this->withValueColor( $value ) ) {
			$classes[] = 'ocme-mfp-f-value-with-color';
		}
		
		if( $this->withValueLabel( $value ) ) {
			$classes[] = 'ocme-mfp-f-value-with-label';
		}
		
		if( $this->withValueCountBadge() ) {
			$classes[] = 'ocme-mfp-f-value-with-count-badge';
		} else {
			$classes[] = 'ocme-mfp-f-value-without-count-badge';
		}
		
		if( $this->isActiveValue( $value ) ) {
			$classes[] = 'ocme-mfp-f-value-active';
		}
		
		if( ocme()->arr()->get( $value, 'total_with_conditions' ) === 0 ) {
			$classes[] = 'ocme-mfp-f-value-disabled';
		}
		
		return implode(' ', $classes);
	}
	
	public function iconLeft( $key, $template = null ) {
		return Filter::icon( Filter::POSITION_LEFT, $this->getIconConfig( $key ), $template );
	}
	
	public function iconRight( $key, $template = null ) {
		return Filter::icon( Filter::POSITION_RIGHT, $this->getIconConfig( $key ), $template );
	}
	
	public function getIconConfig( $key ) {
		/* @var $config array */
		$config = (array) $this->setting->get( 'conditions.' . $key, array() );
		
		foreach( (array) ocme()->arr()->get( $this->config, $key, array() ) as $k => $value ) {
			if( $value ) {
				$config[$k] = $value;
			}
		}
		
		if( is_array( $config ) && isset( $config['symbol'] ) ) {
			$config['symbol'] = implode(' ', array_map(function($v){
				if( strpos($v, 'fa') === 0 ) {
					return str_replace('fa', 'ocme-fa', $v);
				}

				return $v;
			}, explode(' ', $config['symbol'])));
		}
		
		return $config;
	}
	
	public function key() {
		return $this->getConfig('name') . ':' . $this->getConfig('vtype') . ':' . $this->getConfig('vid');
	}
	
	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getConfig( $name = null, $default = null ) {
		if( is_null( $name ) ) {
			return $this->config;
		}
		
		return ocme()->arr()->get( $this->config, $name, $this->setting->get( 'conditions.' . $name, $default ) );
	}
	
	/**
	 * @param type $name
	 * @param mixed $value
	 */
	public function setConfig( $name, $value ) {
		ocme()->arr()->set( $this->config, $name, $value );
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasConfig( $name ) {
		return ocme()->arr()->has( $this->config, $name ) || $this->setting->get( 'conditions.' . $name ) !== null;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasOwnConfig( $name ) {
		return ocme()->arr()->has( $this->config, $name );
	}
	
	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getData( $name = null, $default = null ) {
		if( is_null( $name ) ) {
			return $this->data;
		}
		
		return ocme()->arr()->get( $this->data, $name, $default );
	}
	
	/**
	 * @return array
	 */
	public function getBasicData() {
		return $this->getData();
	}
	
	/**
	 * @param type $name
	 * @param mixed $value
	 */
	public function setData( $name, $value ) {
		ocme()->arr()->set( $this->data, $name, $value );
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasData( $name ) {
		return ocme()->arr()->has( $this->data, $name );
	}
	
	/**
	 * @return string
	 */
	public function getConfigAsVue() {
		return ocme()->arr()->getAsVue( $this->config );
	}
	
	/**
	 * @return string
	 */
	public function getDataAsVue() {
		return $this->data ? ocme()->arr()->getAsVue( $this->data ) : '{}';
	}
	
	/**
	 * @return \Ocme\ModuleSetting
	 */
	public function getModuleSetting() {
		return $this->setting;
	}
}