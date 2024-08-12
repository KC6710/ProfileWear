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

use Ocme\Model\ProductOption,
	Ocme\Model\Option as OptionModel;

class Option {
	
	use \Ocme\Database\Helper\ApplySortOrder;
	use \Ocme\Database\Helper\ApplyPagination;
	
	////////////////////////////////////////////////////////////////////////////
	
	public function eventAfterEditProduct( $args, $output = null ) {
		return $this->updateProduct( $args[0], $args[1] );
	}
	
	public function eventAfterAddProduct( $args, $output = null ) {
		return $this->updateProduct( $output, $args[0] );
	}
	
	public function eventAfterCopyProduct( $args, $output = null ) {
		return $this->updateProduct( $args[0], array() );
	}
	
	public function updateProduct( $product_id, array $data ) {
		/* @var $product_option ProductOption */
		foreach( ProductOption::missing()->where('product_id', $product_id)->get() as $product_option ) {
			$product_option->reCreate();
		}
	}
	
	/**
	 * Update option
	 * 
	 * @param int $option_id
	 * @param array $data
	 */
	public function updateOption( $option_id, $data ) {
		/* @var $option OptionModel */
		if( null != ( $option = OptionModel::find( $option_id ) ) ) {
			$option->fill(array(
				'with_image' => ocme()->arr()->get( $data, 'with_image', '0' ),
				'with_color' => ocme()->arr()->get( $data, 'with_color', '0' ),
				'values_type' => ocme()->arr()->get( $data, 'values_type', 'string' ),
			))->save();
		}
		
		return $option;
	}
	
	public function eventAfterEditOption( $args, $output = null ) {
		/* @var $option OptionModel */
		if( null != ( $option = $this->updateOption( $args[0], $args[1] ) ) ) {
			OptionModel::eventSaved( $option, true );
		}
	}
	
	public function eventAfterAddOption( $args, $output = null ) {
		/* @var $option OptionModel */
		if( null != ( $option = $this->updateOption( $output, $args[0] ) ) ) {		
			OptionModel::eventCreated( $option );
		}
	}
	
	public function eventBeforeDeleteOption( $args ) {
		/* @var $option OptionModel */
		if( null != ( $option = OptionModel::find( $args[0] ) ) ) {		
			OptionModel::eventDeleted( $option );
		}
	}
	
	public function eventBeforeDeleteProduct( $args ) {}
	
	public function eventAfterDeleteProduct( $args ) {}
	
}