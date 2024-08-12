<?php namespace Ocme\Database\Helper;

trait ApplySortOrder {
	
	/**
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $conditions
	 * @return $this
	 */
	public function applySortOrder( \Illuminate\Database\Eloquent\Builder $query, $data, array $sort_data, $defaults ) {
		/* @var $order string */
		if( null != ( $order = ocme()->arr()->get( $data, 'order' ) ) && $order == 'DESC' ) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}
		
		$sort_data = ocme()->collection()->make( $sort_data )->mapWithKeys(function($v){
			return array(
				str_replace('`', '', $v) => $v,
			);
		})->all();
		
		/* @var $sort string */
		if( null != ( $sort = ocme()->arr()->get( $data, 'sort' ) ) && isset( $sort_data[$sort] ) ) {
			$query->orderBy( $sort_data[$sort], $order );
		} else {
			foreach( (array) $defaults as $default ) {
				if( is_array( $default ) ) {
					$query->orderBy( $default[0], $default[1] );
				} else {
					$query->orderBy( $default );
				}
			}
		}
	}
	
}
