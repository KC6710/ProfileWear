<?php namespace Ocme\Database\Helper;

trait ApplyPagination {
	
	/**
	 * @param type $query
	 * @param int $offset
	 * @param int $limit
	 * @return $this
	 */
	public function applyPagination( \Illuminate\Database\Eloquent\Builder $query, $offset, $limit ) {
		if( ! is_null( $offset ) && ! is_null( $limit ) ) {
			if( $offset < 0 ) {
				$offset = 0;
			}
			
			if( $limit < 1 ) {
				$limit = 20;
			}
			
			$query->offset( $offset )->limit( $limit );
		}
		
		return $this;
	}
	
}
