<?php

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

if( ! function_exists( 'ocme' ) ) {
    /**
     * Get the available container instance.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed|\Ocme\App
     */
	function ocme( $abstract = null, array $parameters = array() ) {
		/* @var $app \Ocme\App */
		if( null == ( $app = \Ocme\App::getInstance() ) ) {
			$app = new \Ocme\App;
			
			\Ocme\App::setInstance( $app );
		}
		
		if( is_null( $abstract ) ) {
			return $app;
		}
		
		return $app->make( $abstract, $parameters );
	}
}