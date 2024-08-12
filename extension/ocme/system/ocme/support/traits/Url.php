<?php

namespace Ocme\Support\Traits;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use Ocme\Module\Filter,
	Ocme\Model\OcmeVariable,
	Ocme\Model\SeoUrl;

trait Url {
	
	/**
	 * @var string
	 */
	protected $filter_url_parameter_name;
	
	/**
	 * @var string
	 */
	protected $filter_url_parameter_value;
	
	/**
	 * @var array
	 */
	protected $seo_keyword_to_query = array();
	
	/**
	 * __construct()
	 */
	protected function initUrlTrait() {
		if( null != ( $this->filter_url_parameter_name = ocme()->arr()->get( ocme()->variable()->get( OcmeVariable::TYPE_FILTER_SEO_CONFIG ), 'url_parameter_name' ) ) ) {
			$this->filter_url_parameter_value = ocme()->request()->query( $this->filter_url_parameter_name );
		}
	}
	
	/**
	 * @return string
	 */
	public function filterUrlParameterName() {
		return $this->filter_url_parameter_name;
	}
	
	/**
	 * @return string
	 */
	public function filterUrlParameterValue() {
		return $this->filter_url_parameter_value;
	}
	
	protected function reverseSeo( $path ) {
		/** @var array $parts */
		$parts = explode('/', trim($path, '/'));
		
		/** @var array $data */
		$data = array();
		
		do {
			/** @var string $part */
			$part = array_shift($parts);
			
			/** @var array $keywords */
			$keywords = array();
			
			if( ! isset( $this->seo_keyword_to_query[$part] ) ) {
				/* @var $seo_url SeoUrl */
				if( null != ( $seo_url = SeoUrl::where('keyword', $part)->where('store_id', ocme()->oc()->registry()->get('config')->get('config_store_id'))->first() ) ) {
					$this->seo_keyword_to_query[$part] = $seo_url;

					$keywords[] = $part;
				} else {
					$this->seo_keyword_to_query[$part] = false;
				}
			} else if( $this->seo_keyword_to_query[$part] ) {
				$keywords[] = $part;
			}
				
			if( $parts ) {
				$part .= '/' . implode('/', $parts);
				
				if( ! isset( $this->seo_keyword_to_query[$part] ) ) {
					if( null != ( $seo_url = SeoUrl::where('keyword', $part)->where('store_id', ocme()->oc()->registry()->get('config')->get('config_store_id'))->first() ) ) {
						$this->seo_keyword_to_query[$part] = $seo_url;

						$keywords[] = $part;
					} else {
						$this->seo_keyword_to_query[$part] = false;
					}
				} else if( $this->seo_keyword_to_query[$part] ) {
					$keywords[] = $part;
				}
			}
			
			/** @var string $keyword */
			foreach( $keywords as $keyword ) {
				$data = $this->seo_keyword_to_query[$keyword]->queryData( $data );
			}
		} while ($parts);

		if( ! isset( $data['route'] ) ) {
			if( isset( $data['product_id'] ) ) {
				$data['route'] = 'product/product';
			} elseif( isset( $data['path'] ) ) {
				$data['route'] = 'product/category';
			} elseif( isset( $data['manufacturer_id'] ) ) {
				$data['route'] = 'product/manufacturer/info';
			} elseif( isset( $data['information_id'] ) ) {
				$data['route'] = 'information/information';
			}
		}
		
		return $data;
	}
	
	/**
	 * @param string $link
	 * @return string
	 */
	public function rewrite( $link ) {
		if( ! $this->filter_url_parameter_value ) {
			return $link;
		}
		
		/* @var $url_info array */
		if( null == ( $url_info = parse_url( str_replace( '&amp;', '&', $link ) ) ) ) {
			return $link;
		}
		
		/* @var $query string */
		if( null == ( $query = ocme()->arr()->get( $url_info, 'query' ) ) ) {
			return $link;
		}

		/* @var $data array */
		$data = array();

		parse_str( $query, $data );
		
		if( ! ocme()->arr()->hasAny( $data, array( 'sort', 'order', 'page', 'limit' ) ) ) {
			return $link;
		}
		
		/* @var $source array */
		$source = $data;
		
		/* @var $route string */
		if( null == ( $route = ocme()->arr()->get( $data, 'route' ) ) ) {
			/* @var $path string */
			if( null != ( $path = ocme()->arr()->get( $url_info, 'path' ) ) ) {
				$source = $this->reverseSeo( $path );
				
				if( null == ( $route = ocme()->arr()->get( $source, 'route' ) ) ) {
					return $link;
				}
			}
		}
		
		if( ! $this->sameAsRequest( $source ) ) {
			return $link;
		}
		
		if( in_array( $route, Filter::supportedRoutes() ) ) {
			$data[$this->filter_url_parameter_name] = $this->filter_url_parameter_value;
			
			/* @var $query string */
			$query = '';

			if( $data ) {
				foreach( $data as $key => $value ) {
					$query .= $query ? '&' : '';
					$query .= rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if( $query ) {
					$query = '?' . $query;

					if( ocme()->oc()->isV3() ) {
						$query = str_replace('&', '&amp;', $query);
					}
				}
			}

			/** @var array $parts */
			$parts = [
				ocme()->arr()->get($url_info,'scheme'),
				'://',
				ocme()->arr()->get($url_info,'host')
			];

			/** @var string $port */
			if( null != ( $port = ocme()->arr()->get($url_info, 'port') ) ) {
				$parts[] = ':'. $port;
			}

			/** @var string $path */
			if( null != ( $path = ocme()->arr()->get($url_info, 'path') ) ) {
                $parts[] = $path;
            }
			
			$parts[] = $query;
			
			return implode('', $parts);
		}
		
		return $link;
	}
	
	/**
	 * @param array $data
	 * @return boolean|null
	 */
	protected function fromRequestCategoryToProduct( array $data ) {
		/* @var $route string */
		if( null == ( $route = ocme()->arr()->get( $data, 'route' ) ) ) {
			return false;
		}
		
		if( ocme()->request()->ocQueryRoute() != 'product/category' || ! ocme()->request()->hasQuery('path') ) {
			return false;
		}
		
		if( $route != 'product/product' || ! ocme()->arr()->has( $data, 'product_id' ) ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param array $data
	 * @return boolean|null
	 */
	protected function fromRequestCategoryToSubCategory( array $data ) {
		/* @var $route string */
		if( null == ( $route = ocme()->arr()->get( $data, 'route' ) ) ) {
			return false;
		}
		
		/* @var $request_path string */
		if( ocme()->request()->ocQueryRoute() != 'product/category' || null == ( $request_path = ocme()->request()->hasQuery('path') ) ) {
			return false;
		}
		
		/* @var $path string */
		if( $route != 'product/category' || null == ( $path = ocme()->arr()->has( $data, 'path' ) ) ) {
			return false;
		}
		
		if( strpos( $path, $request_path ) !== 0 ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param array $data
	 * @return boolean|null
	 */
	protected function sameAsRequest( array $data ) {
		/* @var $route string */
		if( null == ( $route = ocme()->arr()->get( $data, 'route' ) ) ) {
			return false;
		}
		
		/* @var $request_route null|string */
		$request_route = null;
		
		if( null == ( $request_route = ocme()->request()->query( 'ocmef_source_route' ) ) ) {
			if( null != ( $ocmef_url_query = ocme()->request()->query( 'ocmef_url_query' ) ) ) {
				if( null != ( $ocmef_url_query = base64_decode( $ocmef_url_query ) ) ) {
					/* @var $url_query_array */
					parse_str(str_replace('&amp;', '&', $ocmef_url_query), $url_query_array);

					if( ocme()->arr()->has( $url_query_array, 'route' ) ) {
						$request_route = ocme()->arr()->get( $url_query_array, 'route' );
					}
				}
			}
			
			if( null == $request_route ) {
				$request_route = ocme()->request()->ocQueryRoute();
			}
		}
		
		if( $route != $request_route ) {
			return false;
		}
		
		/* @var $params array */
		$params = array(
			'product/category' => array( 'path' ),
			'product/product' => array( 'product_id' ),
			'product/manufacturer/info' => array( 'manufacturer_id' ),
		);
		
		if( ! isset( $params[$route] ) ) {
			return null;
		}
		
		/* @var $param string */
		foreach( $params[$route] as $param ) {
			if( ocme()->arr()->get( $data, $param ) != ocme()->request()->query( $param ) ) {
				return false;
			}
		}
		
		return true;
	}
	
}