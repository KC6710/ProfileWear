<?php namespace Opencart\Catalog\Controller\Extension\Ocme\Browse;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

if( ! class_exists( 'Ocme\OpenCart\Catalog\Controller\BrowseCatalog' ) ) {
	require_once DIR_SYSTEM . 'ocme/open-cart/catalog/controller/BrowseCatalog.php';
}

class Catalog extends \Opencart\System\Engine\Controller {
	
	use \Ocme\OpenCart\Catalog\Controller\BrowseCatalog;
	
}
