<?php namespace Ocme;

use Ocme\Model\CategoryPath;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class Module {
	
	/**
	 * @param int|string $status
	 * @return bool
	 */
	public static function validStatus( $status ) {
		switch( $status ) {
			case 'customers' : {
				return ocme()->oc()->registry()->get('customer')->isLogged() || ocme()->oc()->registry()->get('user')->isLogged();
			}
			case 'guests' : {
				return ! ocme()->oc()->registry()->get('customer')->isLogged() && ! ocme()->oc()->registry()->get('user')->isLogged();
			}
			case 'admin' : {
				return (bool) ocme()->oc()->registry()->get('user')->isLogged();
			}
		}
		
		return ! empty( $status );
	}
	
	/**
	 * @param array $stores
	 * @return bool
	 */
	public static function validStore( $stores ) {
		return $stores && in_array( ocme()->oc()->registry()->get('config')->get('config_store_id'), $stores );
	}
	
	/**
	 * @param array $customer_groups
	 * @return bool
	 */
	public static function validCustomerGroup( $customer_groups ) {
		if( ! $customer_groups ) {
			return true;
		}
		
		return in_array( 
			ocme()->oc()->registry()->get('customer')->isLogged() ? ocme()->oc()->registry()->get('customer')->getGroupId() : ocme()->oc()->registry()->get('config')->get('config_customer_group_id'), 
			$customer_groups 
		);
	}
	
	/**
	 * @param string $start_date
	 * @param string $end_date
	 * @return bool
	 */
	public static function validSchedule( $start_date, $end_date ) {
		if( $start_date && strtotime( $start_date ) > time() ) {
			return false;
		}
		
		if( $end_date && strtotime( $end_date ) < time() ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param array $devices
	 * @return bool
	 */
	public static function validDevice( $devices ) {
		return ! $devices || in_array( ocme()->mdetect()->device(), $devices );
	}
	
	/**
	 * @param array $show_in_categories
	 * @param bool $show_in_categories_with_children
	 * @param array $hide_in_categories
	 * @param bool $hide_in_categories_with_children
	 * @return bool
	 */
	public static function validCategory( $show_in_categories, $show_in_categories_with_children, $hide_in_categories, $hide_in_categories_with_children ) {
		if( in_array( ocme()->request()->query('route', ''), array( 'product/category' ) ) ) {
			/* @var $path string */
			if( null != ( $path = (string) ocme()->request()->query('path') ) ) {
				/* @var $parts array */
				$parts = explode('_', $path);

				/* @var $category_id int */
				if( null != ( $category_id = (int) array_pop( $parts ) ) ) {
					if( $show_in_categories ) {
						if( $show_in_categories_with_children ) {
							return (bool) CategoryPath::whereIn('path_id', $show_in_categories)->where('category_id', $category_id)->first();
						} else {
							return in_array( $category_id, $show_in_categories );
						}
					}

					if( $hide_in_categories ) {
						if( $hide_in_categories_with_children ) {
							return ! (bool) CategoryPath::whereIn('path_id', $hide_in_categories)->where('category_id', $category_id)->first();
						} else {
							return ! in_array( $category_id, $hide_in_categories );
						}
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @param array $show_in_manufacturers
	 * @param array $hide_in_manufacturers
	 * @return bool
	 */
	public static function validManufacturer( $show_in_manufacturers, $hide_in_manufacturers ) {
		if( in_array( ocme()->request()->query('route', ''), array( 'product/manufacturer/info' ) ) ) {
			/* @var $manufacturer_id int */
			if( null != ( $manufacturer_id = ocme()->request()->query('manufacturer_id') ) ) {
				if( $show_in_manufacturers && ! in_array( $manufacturer_id, $show_in_manufacturers ) ) {
					return false;
				}
				
				if( $hide_in_manufacturers && in_array( $manufacturer_id, $hide_in_manufacturers ) ) {
					return false;
				}
			}
		}
		
		return true;
	}
	
}