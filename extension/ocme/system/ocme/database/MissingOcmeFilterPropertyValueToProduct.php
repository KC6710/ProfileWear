<?php namespace Ocme\Database;

trait MissingOcmeFilterPropertyValueToProduct {
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	/**
	 * @param Illuminate\Database\Eloquent\Builder $query
	 */
	public function scopeMissingOcmeFilterPropertyValueToProduct( \Illuminate\Database\Eloquent\Builder $query ) {
		/* @var $table string */
		$table = $this->getTable();
		
		/* @var $alias string */
		if( null == ( $alias = $query->getQuery()->getFromAlias() ) ) {
			$alias = $table;
		}
		
		/* @var $column string */
		$column = str_replace( 'product_', '', ocme()->str()->snake( basename(str_replace('\\', '/', get_class( $this ) )) ) ) . '_id';
		
		$query
			->whereNotExists(function($q) use( $alias, $column ){
				$q->select(ocme()->db()->raw(1))
					->from('ocme_filter_property_value_to_product')
					->whereColumn($alias . '.product_id', 'ocme_filter_property_value_to_product.product_id')
					->whereColumn($alias . '.' . $column, 'ocme_filter_property_value_to_product.' . $column);
			});
	}
	
}