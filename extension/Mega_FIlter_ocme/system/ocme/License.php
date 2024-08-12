<?php namespace Ocme;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission.
 */

class License {
	
	/**
	 * @var Ocme\Support\Collection
	 */
	private static $shop;
	
	/**
	 * @return Ocme\Support\Collection|null
	 */
	public function shop() {
		if( ! ocme()->api()->getAccessToken() ) {
			return null;
		}
		
		if( self::$shop ) {
			return self::$shop;
		}
		
		self::$shop = ocme()->api()->get('shop', array(
			'url' => HTTP_CATALOG,
			'url_ssl' => defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG,
		));
		
		if( ( self::$shop && self::$shop->get('status') == 'error' ) || ocme()->api()->status() == 503 ) {
			$this->disconnect();
		}
		
		return self::$shop;
	}
	
	/**
	 * @return array
	 */
	public function shopData() {
		return array(
			'url' => HTTP_CATALOG,
			'url_ssl' => defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_CATALOG,
			'url_admin' => defined( 'HTTPS_SERVER' ) ? HTTPS_SERVER : HTTP_SERVER,
			
			'name' => htmlspecialchars( ocme()->ocRegistry()->get('config')->get('config_name') ),
			
			'oc_version' => VERSION,
			'ip' => ocme()->request()->server('SERVER_ADDR'),
		);
	}
	
	/**
	 * @return void
	 */
	public function disconnect() {
		ocme()->model('setting/setting')->deleteSetting('ocme_mfp_license');
		ocme()->ocRegistry()->get('config')->set('ocme_mfp_license', null);
		ocme()->api()->setAccessToken(null);

		self::$shop = null;
	}
	
	/**
	 * @return string|null
	 */
	public function token() {
		return ocme()->api()->getAccessToken();
	}
	
	/**
	 * @return bool
	 */
	public function isConnected() {
		return (bool) $this->shop();
	}
	
}