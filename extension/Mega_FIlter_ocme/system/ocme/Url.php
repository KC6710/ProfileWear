<?php namespace Ocme;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class Url {
	
	/**
	 * @return string
	 */
	public function link( $route, $parameters = array() ) {
		/* @var $query string */
		$query = $parameters;
		
		if( is_array( $query ) ) {
			$query = http_build_query( $query );
		}
		
		return ocme()->ocRegistry()->get('url')->link(ocme_extension_path( $route ), $query, true);
	}
	
	/**
	 * @return string
	 */
	public function userTokenParamName() {
		return 'user_token';
	}
	
	/**
	 * @return string
	 */
	public function adminLink( $route, $parameters = array() ) {
		/* @var $query array */
		$query = $parameters;
		
		if( ! is_array( $query ) ) {
			parse_str( $parameters, $query );
		}
		
		if( ! isset( $query[$this->userTokenParamName()] ) ) {
			$query[$this->userTokenParamName()] = ocme()->arr()->get( ocme()->ocRegistry()->get('session')->data, $this->userTokenParamName() );
		}
		
		return $this->link(ocme_extension_path( $route ), $query );
	}
	
}