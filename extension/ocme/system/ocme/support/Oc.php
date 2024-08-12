<?php namespace Ocme\Support;

class Oc
{
	
	protected $registry;
	
	public function __construct( $registry ) {
		$this->registry = $registry;
	}
	
	public function registry() {
		return $this->registry;
	}

	public function view( $route, $data ) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		$route = ocme_template_path($route);

		/** @var string $path */ 
		$path = $this->registry->get('config')->get('template_directory');

		/** @var string $ext */
		$ext = 'twig';

		/** @var array $parts */
		$parts = array_merge( array( '' ), explode('.', VERSION) );

		/** @var string $postfix */
		$postfix = '';

		$data['ocme'] = ocme();

		foreach( $parts as $part ) {
			$postfix .= $part;

			/** @var string $template */
			$template = $route . ( $postfix == '' ? '' : '_oc' ) . $postfix;

			/** @var string $file */
			$file = DIR_TEMPLATE . $path . $template . '.' . $ext;

			if( file_exists( $file ) ) {
				return $this->registry->get('load')->view($template, $data);
			}
		}
		
		return $this->registry->get('load')->view($route, $data);
	}
		
	/**
	 * @return int
	 */
	public function layoutId() {
		/* @var $layout_id int */
		$layout_id = 0;
		
		/* @var $route string */
		$route = ocme()->request()->ocQueryRoute();

		/* @var $path string */
		if( $route == 'product/category' && null != ( $path = ocme()->request()->query('path') ) ) {
			/* @var $parts array */
			$parts = explode( '_', (string) $path);

			$layout_id = ocme()->model('catalog/category')->getCategoryLayoutId(end( $parts ));
		} else
		/* @var $product_id int */
		if( $route == 'product/product' && null != ( $product_id = ocme()->request()->query('product_id' ) ) ) {
			$layout_id = ocme()->model('catalog/product')->getProductLayoutId( $product_id );
		} else
		/* @var $information_id int */
		if( $route == 'information/information' && null != ( $information_id = ocme()->request()->query('information_id' ) ) ) {
			$layout_id = ocme()->model('catalog/information')->getInformationLayoutId( $information_id );
		}

		if( ! $layout_id ) {
			$layout_id = ocme()->model('design/layout')->getLayout( $route );
		}

		if( ! $layout_id ) {
			$layout_id = ocme()->oc()->registry()->get('config')->get('config_layout_id');
		}
		
		return $layout_id;
	}
	
	public function isV2() {
		return $this->isVersion( 2 ) && $this->isVersion( 3, '<' );
	}

	public function isV2OrLater() {
		return $this->isVersion( 2, '>=' );
    }
	
	public function isV3() {
		return $this->isVersion( 3 ) && $this->isVersion( 4, '<' );
	}

	public function isV3OrLater() {
        return $this->isVersion( 3, '>=' );
    }
	
	public function isV4() {
		return $this->isVersion( 4 ) && $this->isVersion( 5, '<' );
	}

	public function isV4OrLater() {
        return $this->isVersion( 4, '>=' );
    }
	
	public function isVersion( $version, $operator = '>=' ) {
		return version_compare( VERSION, $version, $operator );
	}
	
	public function toJS() {
		return ocme()->arr()->getAsVue(array(
			'version' => VERSION,
		));
	}
	
}