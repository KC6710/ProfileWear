<?php namespace Ocme;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class Loader {
	
	private static function finalPath( $path ){
		if( strpos( $path, 'PhpDepends' ) !== false ) {
			$filename = pathinfo($path, PATHINFO_FILENAME);
			$extension = pathinfo($path, PATHINFO_EXTENSION);
			$dirname = pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
			$version = implode( '.', array_slice( explode( '.', PHP_VERSION ), 0, 1 ) );
			
			if( file_exists( $dirname . $filename . '.' . $version . '.' . $extension ) ) {
				$path = $dirname . $filename . '.' . $version . '.' . $extension;
			}
		}
		
		return $path;
	}
	
	/**
	 * Loading class of OCME
	 * 
	 * @param string $class
	 * @return void|bool
	 */
	public static function load( $class ) {
		/* @var $parts array */
		if( null == ( $parts = explode( '\\', $class ) ) || $parts[0] != 'Ocme' ) {
			return;
		}
		
		/* @var $name string */
		$name = array_pop( $parts );
		
		/* @var $path string */
		$path = strtolower( implode( DIRECTORY_SEPARATOR, array_map(function($v){
			if( ! ctype_lower( $v ) ) {
				$v = preg_replace('/\s+/u', '', ucwords($v));
				$v = preg_replace('/(.)(?=[A-Z])/u', '$1-', $v);
			}
			
			return $v;
		}, $parts)));
		
		/* @var $file string */
		$file = self::finalPath( DIR_OCME . '..' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $name . '.php' );
		
		if( is_file( $file ) ) {
			require_once ocme_modification( $file );
			
			return true;
		}
	}
}