<?php namespace Ocme\OpenCart\Admin\Traits;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

trait Search {
	
	public function search() {
		$this->initialize()->module( 'search' );
	}
	
	public function search_config() {		
		$this->initialize()->module_config( Module::CODE_SEARCH );
	}
	
	public function search_settings() {
		$this->initialize()->render('search_settings');
	}
	
}