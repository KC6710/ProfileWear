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

class OcSeoUrl extends \Controller {

	use \Ocme\Support\Traits\Url;
	
	public function __construct( $registry ) {
		parent::__construct( $registry );
		
		$this->initUrlTrait();
	}
	
}