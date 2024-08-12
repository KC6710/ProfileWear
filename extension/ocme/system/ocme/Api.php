<?php namespace Ocme;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 * 
 * @method \Ocme\Support\Collection|null get(string $uri, array $data)
 * @method \Ocme\Support\Collection|null post(string $uri, array $data)
 * @method \Ocme\Support\Collection|null put(string $uri, array $data)
 * @method \Ocme\Support\Collection|null patch(string $uri, array $data)
 * @method \Ocme\Support\Collection|null delete(string $uri, array $data)
 */

class Api {
	
	/**
	 * API Url
	 *
	 * @var string
	 */
	private $url = 'https://license.ocdemo.eu/v1/';
	
	/**
	 * License data
	 *
	 * @var array
	 */
	private $license;
	
	/**
	 * @var string
	 */
	private $access_token;
	
	/**
	 * @var bool
	 */
	private static $curl_available;
	
	/**
	 * @var int
	 */
	private $status;
	
	/**
	 * @param string $access_token
	 */	
	public function __construct() {		
		if( is_null( self::$curl_available ) ) {
			self::$curl_available = extension_loaded( 'curl' );
		}
		
		$this->license = self::$curl_available ? (array) ocme()->oc()->registry()->get('config')->get('ocme_mfp_license') : array();
		
		if( null == ( $this->access_token = ocme()->arr()->get( $this->license, 'access_token' ) ) ) {
			$this->access_token = null;
		}
	}
	
	/**
	 * @return string|null
	 */
	public function getAccessToken() {
		return $this->access_token;
	}
	
	/**
	 * @return string|null
	 */
	public function hasAccessToken() {
		return (bool) $this->access_token;
	}
	
	/**
	 * @param string $access_token
	 * @return \static
	 */
	public function setAccessToken( $access_token ) {
		$this->access_token = $access_token;
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		if( in_array( $name, array( 'get', 'post', 'put', 'patch', 'delete' ) ) ) {
			/* @var $uri string */
			$uri = array_shift( $arguments );
			
			/* @var $data array */
			$data = $arguments && is_array( $arguments[0] ) ? array_shift( $arguments ) : array();
			
			return $this->request( $name, $uri, $data );
		}
	}
	
	/**
	 * @param string $method
	 * @param string $uri
	 * @param array $data
	 * @return \Ocme\Support\Collection|null
	 */
	private function request( $method, $uri, array $data ) {
		if( $this->access_token ) {
			$data['access_token'] = $this->access_token;
		}
		
		/* @var $curl Curl */
		$curl = new Curl();
		
		/* @var $response mixed */
		$response = $curl
			->to( $this->url . $uri )
			->withData( $data )
			->withResponseHeaders()
			->returnResponseObject()
			->asJsonResponse( true )
			->{$method}();
			
		$this->status = $response->status;
		
		if( $response->status != 200 ) {
			return null;
		}
		
		return ocme()->collection()->make( $response->content );
	}
	
	/**
	 * @return string
	 */
	public function url() {
		return $this->url;
	}
	
	/**
	 * @return string
	 */
	public function connectUrl() {
		/* @var $https bool */
		$https = ocme()->request()->server('HTTPS') == 'on' || ocme()->request()->server('HTTPS') == '1';
		
		/* @var $params array */
		$params = array(
			'redirect_url' => 'http' . ( $https ? 's' : '' ) . '://' . ocme()->request()->server('HTTP_HOST') . ocme()->request()->server('REQUEST_URI'),
		);
		
		/* @var $url string */
		$url = ocme()->api()->url();
		
		if( ! $https ) {
			$url = str_replace( 'https://', 'http://', $url );
		}
		
		return $url . 'connect?' . http_build_query( $params );
	}
	
	/**
	 * @return array
	 */
	public function connectData( array $params = array() ) {
		return array_replace(ocme()->license()->shopData(), array(
			'redirect' => 1,
		), $params);
	}
	
	/**
	 * @return int
	 */
	public function status() {
		return $this->status;
	}
}