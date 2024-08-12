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
	
	public function isV3() {
		return $this->isVersion( 3 ) && $this->isVersion( 4, '<' );
	}
	
	public function isV4() {
		return $this->isVersion( 4 ) && $this->isVersion( 5, '<' );
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