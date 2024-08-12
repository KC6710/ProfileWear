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

use Ocme\Model\OcmeVariable;

trait OcmeMfp {
	
	protected static $initialized;
	
	protected static $url_parameters = array();
	
	public function eventStartup(&$route) {
		if( self::$initialized ) {
			if( ocme()->ajaxRendering() && in_array( $route, array( 'common/column_left', 'common/column_right', 'common/content_top', 'common/content_bottom', 'common/footer') ) ) {
				return ' ';
			}
			
			return;
		}
		
		self::$initialized = true;
		
		ocme_startup( $this->registry );
		
		ocme()->singleton('url', function(){
			if( version_compare( VERSION, '4', '>=' ) ) {
				return new \Ocme\OpenCart\Catalog\Controller\Oc4SeoUrl( $this->registry );
			}
			
			return new \Ocme\OpenCart\Catalog\Controller\OcSeoUrl( $this->registry );
		});
		
		$this->registry->get('url')->addRewrite( ocme()->url() );
	}
	
	private function urlParameters( $url_parameter_name = null ) {
		if( is_null( $url_parameter_name ) ) {
			$url_parameter_name = ocme()->variable()->get( OcmeVariable::TYPE_FILTER_SEO_CONFIG . '.url_parameter_name', 'ocmef' );
		}
		
		if( ! isset( self::$url_parameters[$url_parameter_name] ) ) {
			self::$url_parameters[$url_parameter_name] = '';

			/* @var $url_parameter_value string|null */
			if( null != ( $url_parameter_value = ocme()->request()->query( $url_parameter_name ) ) ) {
				/* @var $params array */
				$params = array();
				
				/* @var $url_value array */
				foreach( ocme()->model('filter')->getUrlValues() as $url_value ) {
					/* @var $values array */
					if( null != ( $values = ocme()->arr()->get( $url_value, 'values' ) ) ) {
						$params[] = ocme()->arr()->get( $url_value, 'title' ) . ': ' . ocme()->collection()->make( $values )->implode( 'label', ', ');
					}
				}

				self::$url_parameters[$url_parameter_name] = implode(', ', ocme()->arr()->flatten( $params ));
			}
		}
		
		return self::$url_parameters[$url_parameter_name];
	}
	
	public function eventViewBefore($type, &$data) {
		if( in_array( $type, array(
				'common/header',
			))
		) {
			if( isset( $data['title'] ) && $this->urlParameters() ) {
				$this->document->setTitle( $data['title'] .= ' | ' . $this->urlParameters() );
			}
		}
		
		if( in_array( $type, \Ocme\Module\Filter::supportedRoutes() ) ) {
			if( ocme()->ajaxRendering() ) {
				$data['header'] = $data['footer'] = $data['column_left'] = $data['column_right'] = $data['content_top'] = $data['content_bottom'] = '';
			}
			
			if( ocme()->variable()->get('filter.products_wrapper.insert') == 'between_content_top_and_content_bottom' ) {
				$data['content_top'] .= sprintf( '<div id="%s" class="ocme-mfp-f-main-container %s">', ocme()->variable()->get('filter.products_wrapper.id'), ocme()->variable()->get('filter.products_wrapper.extra_class') );
				$data['content_bottom'] = '</div>' . $data['content_bottom'];
			}
			
			if( isset( $data['breadcrumbs'] ) ) {
				/* @var $url_parameter_name string */
				$url_parameter_name = ocme()->variable()->get( OcmeVariable::TYPE_FILTER_SEO_CONFIG . '.url_parameter_name', 'ocmef' );
				
				/* @var $url_parameters string */
				if( null != ( $url_parameters = $this->urlParameters() ) ) {
					/* @var $url_params array */
					$url_params = array(
						'product/category' => array( 'path' ),
						'product/manufacturer/info' => array( 'manufacturer_id' ),
						'product/search' => array( 'search', 'description' ),
					);

					/* @var $url string */
					$url = '';

					$url_params[$type][] = $url_parameter_name;

					foreach( $url_params[$type] as $key ) {
						if( ocme()->request()->hasQuery( $key ) ) {
							$url .= $url ? '&' : '';
							$url .= $key . '=' . ocme()->request()->query( $key );
						}
					}

					$data['breadcrumbs'][] = array(
						'text' => $url_parameters,
						'href' => $this->url->link( $type, $url, true )
					);
				}
			}
		}
	}
	
}