<?php

namespace Ocme\Support\Traits;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use MatthiasMullie\Minify as Minifier;

trait Minify {
	
	/**
	 * @var array
	 */
	protected $minifier_css = array();
	
	/**
	 * @var array
	 */
	protected $minifier_js = array();
	
	/**
	 * @var string
	 */
	protected $cache_path_js;
	
	/**
	 * @var bool
	 */
	protected $cache_path_js_is_writable = null;
	
	/**
	 * @var string
	 */
	protected $cache_path_css;
	
	/**
	 * @var bool
	 */
	protected $cache_path_css_is_writable = null;
	
	/**
	 * @var array
	 */
	protected static $loaded_files = array();
	
	protected function refreshCacheFolder( $dir, $ext ) {
		if( null != ( $files = glob($dir.'/*.'.$ext) ) ) {
			foreach( $files as $f ) {
				if( time() - filemtime( $f ) > 60 * 60 * 24 ) {
					unlink( $f );
				}
			}
		}
	}
	
	protected function baseDir() {
		return ( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'ocme/' . ( ocme()->environment() == 'Admin' ? 'admin' : 'catalog' ) . '/' : DIR_APPLICATION );
	}
	
	protected function baseUrl() {
		/* @var $url string */
		$url = defined( 'HTTPS_SERVER' ) ? HTTPS_SERVER : HTTP_SERVER;
		
		if( version_compare( VERSION, '4', '>=' ) ) {
			$url = ( defined('HTTPS_CATALOG') ? HTTPS_CATALOG : ( defined( 'HTTP_CATALOG' ) ? HTTP_CATALOG : HTTP_SERVER ) ) . 'extension/ocme/';
			
			if( ocme()->environment() == 'Admin' ) {
				$url .= 'admin/';
			}
		}
		
		return $url;
	}
	
	/**
	 * @return array
	 */
	protected function minifyJS( $namespace = 'footer' ) {
		if( empty( $this->minifier_js[$namespace] ) ) {
			return array();
		}
		
		if( ! $this->cache_path_js_is_writable || ocme()->variable()->get('debug.debug') ) {
			return $this->normalizeMinifyUrls( $this->minifier_js[$namespace] );
		}
		
		/* @var $dir string */
		$dir = $this->baseDir() . $this->cache_path_js;

		/* @var $name string */
		$name = md5( ocme()->version() . serialize( $this->minifier_js[$namespace] ) ) . '.js';

		if( ! file_exists( $dir . '/' . $name ) ) {
			$this->refreshCacheFolder($dir, 'js');

			if( ocme()->environment() == 'Admin' ) {
				file_put_contents( $dir . '/' . $name, '' );

				foreach( $this->minifier_js[$namespace] as $file ) {
					file_put_contents( $dir . '/' . $name, file_get_contents( $this->normalizeMinifyPath( $file ) ) . PHP_EOL, FILE_APPEND );
				}
			} else {
				$minifier = new Minifier\JS;

				$minifier->add( array_map(function( $file ){
					return $this->normalizeMinifyPath( $file );
				}, $this->minifier_js[$namespace]) );

				$minifier->minify( $dir . '/' . $name );
			}
		}

		return array( $this->baseUrl() . $this->minifyPath( $this->cache_path_js . '/' . $name ) );
	}
	
	protected function minifyPath( $path ) {
		if( ocme()->environment() == 'Admin' ) {
			return $path;
		}
		
		return 'catalog/' . $path;
	}
	
	/**
	 * @return array
	 */
	protected function minifyCSS( $namespace = 'footer' ) {
		if( empty( $this->minifier_css[$namespace] ) ) {
			return array();
		}
		
		if( ! $this->cache_path_css_is_writable || ocme()->variable()->get('debug.debug') ) {
			return $this->normalizeMinifyUrls( $this->minifier_css[$namespace] );
		}
		
		/* @var $dir string */
		$dir = $this->baseDir() . $this->cache_path_css;

		/* @var $name string */
		$name = md5( ocme()->version() . serialize( $this->minifier_css[$namespace] ) ) . '.css';

		if( ! file_exists( $dir . '/' . $name ) ) {
			$this->refreshCacheFolder($dir, 'css');

			$minifier = new Minifier\CSS;

			$minifier->add( array_map(function( $file ){
				return $this->normalizeMinifyPath( $file );
			}, $this->minifier_css[$namespace]) );

			$minifier->minify( $dir . '/' . $name );
		}

		return array( $this->baseUrl() . $this->minifyPath( $this->cache_path_css . '/' . $name ) );
	}
	
	protected function normalizeMinify( $str ) {
		$search = array();
		
		if( defined( 'HTTPS_CATALOG' ) ) {
			$search[] = HTTPS_CATALOG;
		} else {
			$search[] = HTTP_SERVER;
		}
		
		if( defined( 'HTTP_CATALOG' ) ) {
			$search[] = HTTP_CATALOG;
		} else {
			$search[] = HTTP_SERVER;
		}
		
		return str_replace($search, '', $str);
	}
	
	protected function normalizeMinifyUrls( $urls ) {
		foreach( $urls as & $url ) {
			$url = $this->normalizeMinify($url);
			
			if( strpos( $url, 'catalog/' ) === 0 ) {
				$url = 
					( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : ( defined( 'HTTP_CATALOG' ) ? HTTP_CATALOG : HTTP_SERVER ) ) 
						.
					( version_compare( VERSION, '4', '>=' ) ? 'extension/ocme/' : '' ) 
						. 
					'catalog/' . substr( $url, 8 );
			} else {
				$url = $this->baseUrl() . $url;
			}
		}
		
		return $urls;
	}
	
	protected function normalizeMinifyPath( $path ) {
		$path = $this->normalizeMinify($path);
		
		if( strpos( $path, 'catalog/' ) === 0 ) {
			$path = ( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'ocme/catalog/' : ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) ) . substr( $path, 8 );
		} else {
			$path = $this->baseDir() . $path;
		}
		
		return $path;
	}
	
	private function existsInMinifyArray( $array, $file ) {
		foreach( $array as $item ) {
			if( in_array( $file, $item ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	protected function addScript( $file, $namespace = 'footer' ) {
		if( is_null( $this->cache_path_js_is_writable ) ) {
			$this->cache_path_js_is_writable = is_dir( $this->baseDir() . $this->cache_path_js ) && is_writable( $this->baseDir() . $this->cache_path_js );
		}
		
		if( ! isset( $this->minifier_js[$namespace] ) ) {
			$this->minifier_js[$namespace] = array();
		}
		
		if( ! $this->existsInMinifyArray( $this->minifier_js, $file ) ) {
			$this->minifier_js[$namespace][] = $file;
		}
	}
	
	protected function addStyle( $file, $namespace = 'footer' ) {
		if( is_null( $this->cache_path_css_is_writable ) ) {
			$this->cache_path_css_is_writable = is_dir( $this->baseDir() . $this->cache_path_css ) && is_writable( $this->baseDir() . $this->cache_path_css );
		}
		
		if( ! isset( $this->minifier_css[$namespace] ) ) {
			$this->minifier_css[$namespace] = array();
		}
		
		if( ! $this->existsInMinifyArray( $this->minifier_css, $file ) ) {
			$this->minifier_css[$namespace][] = $file;
		}
	}
	
}