<?php namespace Opencart\Catalog\Controller\Extension\Ocme\Module;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

require_once ( defined('DIR_EXTENSION') ? DIR_EXTENSION . 'Mega_FIlter_ocme/system/' : DIR_SYSTEM ) . 'ocme/Startup.php';

class OcmeMfp extends \Opencart\System\Engine\Controller {
	
	use \Ocme\OpenCart\Catalog\Controller\OcmeMfp;
	
}