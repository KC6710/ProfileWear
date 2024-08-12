<?php namespace Ocme\OpenCart\Admin\Model;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use Ocme\Model\Event,
	Ocme\Model\OcmeVariable;

class OcmeMfp {
	
	public function createUrlParams( $keys, $defaults = array() ) {
		if( ! is_array( $keys ) ) {
			$keys = func_get_args();
			
			$defaults = array_pop( $keys );
			
			if( ! is_array( $defaults ) ) {
				$keys[] = $defaults;
				$defaults = array();
			}
		}
		
		/* @var $url string */
		$url = 'user_token=' . ocme()->ocRegistry()->get('session')->data['user_token'];

		/* @var $key string */
		foreach( $keys as $key ) {
			if( ocme()->request()->hasQuery( $key ) ) {
				$url .= '&' . $key . '=' . urlencode( ocme()->request()->query( $key ) );
			} else if( null !== ( $default = ocme()->arr()->get( $defaults, $key ) ) ) {
				$url .= '&' . $key . '=' . urlencode( $default );
			}
		}
		
		return $url;
	}
	
	protected function migrations( $version = null ) {
		/* @var $files array */
		if( null != ( $files = glob( DIR_OCME . 'database/migrations/*.php' ) ) ) {			
			usort( $files, function( $a, $b ){
				$ba = str_replace( '.php', '', basename( $a ) );
				$bb = str_replace( '.php', '', basename( $b ) );
				
				if( version_compare( $ba, $bb, '>' ) ) {
					return 1;
				}
				
				if( version_compare( $ba, $bb, '<' ) ) {
					return -1;
				}
				
				return 0;
			});
		}
		
		/* @var $migrations array */
		$migrations = array_map(function($file){
			/* @var $name string */
			$name = basename( $file, '.php' );
			
			/* @var $class string */
			$class = str_replace( '.', '', $name );
			
			return array(
				'file' => $file,
				'version' => $name,
				'class' => '\\Ocme\\Database\\Migrations\\Ocme' . $class,
			);
		}, $files);
		
		if( ! is_null( $version ) ) {
			$migrations = array_filter( $migrations, function( $migration ) use( $version ){
				return version_compare( $migration['version'], $version, '>=' );
			});
		}
		
		return $migrations;
	}
	
	public function installMigrations( $version = null ) {
		/* @var $migrations array */
		$migrations = $this->migrations( $version );
		
		/* @var $migration array */
		foreach( $migrations as $migration ) {
			require_once $migration['file'];
			
			/* @var $class string */
			$class = $migration['class'];
			
			/* @var $obj \Illuminate\Database\Migrations\Migration */
			$obj = new $class();
			
			$obj->up();
		}
		
		OcmeVariable::firstOrNew(array(
			'type' => 'app',
			'name' => 'version',
		))->fill(array(
			'value' => ocme()->version()
		))->save();
	}
	
	public function install() {
		$this->installMigrations();
	}
	
	public function update() {
		$this->installMigrations( ocme()->version() );
	}
	
	public function uninstall() {
		$this->rollbackMigrations();
		$this->deleteEvents();
	}
	
	public function rollbackMigrations() {
		/* @var $migrations array */
		$migrations = array_reverse( $this->migrations() );
		
		/* @var $migration array */
		foreach( $migrations as $migration ) {
			require_once $migration['file'];
			
			/* @var $class string */
			$class = $migration['class'];
			
			/* @var $obj \Illuminate\Database\Migrations\Migration */
			$obj = new $class();
			
			$obj->down();
		}
	}
	
	public function deleteEvents() {
		Event::whereIn('code', array('ocme_mfp', 'ocme_mfp_attribute', 'ocme_mfp_filter'))->delete();
	}
	
}