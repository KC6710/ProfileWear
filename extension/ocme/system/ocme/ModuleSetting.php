<?php namespace Ocme;

use Ocme\Model\OcmeVariable;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class ModuleSetting {
	
	/**
	 * @var array
	 */
	protected $setting;
	
	/**
	 * @var string
	 */
	protected $global_type;
	
	/**
	 * @param array $setting
	 */	
	public function __construct( array $setting, $global_type ) {
		$this->setting = $setting;
		$this->global_type = $global_type;
	}
	
	public function get( $key, $default = null ) {
		/* @var $value mixed */
		$value = ocme()->arr()->get( $this->setting, $key );
		
		/* @var $default null|mixed */
		$default = null;
		
		if( null === $value || is_array( $value ) ) {
			if( null === ( $default = ocme()->variable()->get( $this->global_type . '.' . $key ) ) ) {
				if( null === ( $default = ocme()->variable()->get( 'config_global.' . $key ) ) ) {
					/* @var $parts array */
					$parts = explode( '.', $key );

					/* @var $name string */
					$name = array_pop( $parts );

					/* @var $namespace string */
					$namespace = implode( '.', $parts );

					/* @var $namespaces array */
					$namespaces = array();
					
					if( ocme()->str()->startsWith( $namespace, 'conditions.' ) ) {
						$namespaces[] = 'conditions.global';
						$namespaces[] = 'global.conditions';
						$namespaces[] = 'global.conditions.setting';
					}

					while( $namespaces ) {
						$namespace = array_shift( $namespaces );

						if( ocme()->str()->startsWith( $namespace, 'global.' ) ) {
							$default = ocme()->variable()->get( $this->global_type . '.' . substr( $namespace, 7 ) . '.' . $name );
						} else {
							$default = ocme()->arr()->get( $this->setting, $namespace . '.' . $name );
						}

						if( ! is_null( $default ) ) {
							break;
						}
					}
				}
			}
		}
		
		if( $value === null ) {
			$value = $default;
		} else if( is_array( $value ) && is_array( $default ) ) {
			foreach( ocme()->arr()->dot( $default ) as $key => $val ) {
				if( ocme()->arr()->get( $value, $key ) === null ) {
					ocme()->arr()->set( $value, $key, $val );
				}
			}
		}
		
		if( is_null( $value ) ) {
			return $default;
		}
		
		if( is_array( $value ) ) {
			foreach( $value as $k => $v ) {
				if( is_null( $v ) ) {
					$value[$k] = $this->get( $key . '.' . $k );
				}
			}
		}
		
		return $value;
	}
	
	public function getSettings() {
		return $this->setting;
	}
	
	public function getGlobal() {
		return ocme()->variable()->get( $this->global_type );
	}
	
}