<?php

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

define( 'DIR_OCME', __DIR__ . DIRECTORY_SEPARATOR );

if( ! function_exists( 'ocme_modification' ) ) {
	function ocme_modification( $filename ) {
		if( function_exists( 'modification' ) ) {
			return modification( $filename );
		}
		
		return $filename;
	}
}

require_once ocme_modification(DIR_OCME . 'packages' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
require_once ocme_modification(DIR_OCME . 'Loader.php');

spl_autoload_register(function( $class ) {
	return \Ocme\Loader::load( $class );
});

require_once ocme_modification(DIR_OCME . 'Helpers.php');

function ocme_extension_path( $path ) {
	if( version_compare( VERSION, '4', '>=' ) ) {
		if( strpos( $path, 'extension/module/ocme_' ) === 0 ) {
			$path = str_replace( 'extension/module/ocme_', 'extension/ocme/module/ocme_', $path );
			
			/* @var $parts array */
			$parts = explode( '/', $path );
				
			if( count( $parts ) == 5 ) {
				$sep = version_compare( VERSION, '4.0.2.0', '>=' ) ? '.' : '|';
				$path = array_pop( $parts );
				$path = implode( '/', $parts ) . ( $path ? $sep . $path : '' );
			}
		}
	}
	
	return $path;
}

function ocme_template_path( $path ) {
	$path = ocme_extension_path( $path );	
		
	if( version_compare( VERSION, '4', '>=' ) ) {
		$path = str_replace( '/module/', '/extension/module/', $path );
	}
	
	return str_replace(array('.','|'), '/',$path);
}

function ocme_model( $instance, $model ) {
	$model = ocme_extension_path( $model );
	
	$instance->load->model( $model );
	
	return $instance->{'model_'.str_replace('/','_',$model)};
}

function ocme_startup( $registry ) {
	ocme()->singleton('oc', function() use( $registry ){
		return new Ocme\Support\Oc( $registry );
	});

	ocme()->singleton('user', function() use( $registry ){
		if( version_compare( VERSION, '4', '>=' ) ) {
			return new \Opencart\System\Library\Cart\User( $registry );
		}
		
		return new \Cart\User( $registry );
	});

	/*
	|--------------------------------------------------------------------------
	| Establishing a connection to the database
	|--------------------------------------------------------------------------
	|
	*/

	ocme()->singleton('db', '\Ocme\Database\Connection')->db()->connect();

	/*
	|--------------------------------------------------------------------------
	| Bind Important Interfaces
	|--------------------------------------------------------------------------
	|
	| Next, we need to bind some important interfaces into the container so
	| we will be able to resolve them when needed.
	|
	*/
	
	ocme()->singleton('cache', function(){
		return new Ocme\Support\Cache( new Illuminate\Cache\FileStore( new \Illuminate\Filesystem\Filesystem(), DIR_CACHE . 'ocme/' ) );
	});

	ocme()->singleton('config', '\Ocme\Support\Config');
	ocme()->singleton('translator', '\Ocme\Translator');
	ocme()->singleton('variable', '\Ocme\Support\Variable');
	ocme()->singleton('mdetect', '\Ocme\Support\MobileDetect');
	ocme()->singleton('api', '\Ocme\Api');
	ocme()->singleton('license', '\Ocme\License');

	/*
	|--------------------------------------------------------------------------
	| Base aliases
	|--------------------------------------------------------------------------
	|
	*/

	ocme()->alias('\Ocme\Support\Arr', 'arr');
	ocme()->alias('\Ocme\Support\Data', 'data');
	ocme()->alias('\Ocme\Support\Carbon', 'carbon');
	ocme()->alias('\Ocme\Support\Collection', 'collection');
	ocme()->alias('\Ocme\Support\Str', 'str');
	ocme()->alias('\Ocme\Utils', 'utils');
	ocme()->alias('\Ocme\Msg', 'msg');
	ocme()->alias('\Ocme\Url', 'url');

	ocme()->singleton('request', '\Ocme\Support\Request')->request()->initialize();
		
	ocme()->singleton('link', function() use( $registry ){
		if( version_compare( VERSION, '4', '>=' ) ) {
			return new \Ocme\OpenCart\Catalog\Controller\Oc4SeoUrl( $registry );
		}
		
		return new \Ocme\OpenCart\Catalog\Controller\OcSeoUrl( $registry );
	});
	
	$registry->get('url')->addRewrite( ocme()->link() );
	
	if( ! defined('OCME_MFP_IS_READY') ) {
		define('OCME_MFP_IS_READY', true);
	}
}