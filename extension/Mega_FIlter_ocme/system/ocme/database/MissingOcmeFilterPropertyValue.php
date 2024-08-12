<?php namespace Ocme\Database;

trait MissingOcmeFilterPropertyValue {
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	/**
	 * @param Illuminate\Database\Eloquent\Builder $query
	 */
	public function scopeMissingOcmeFilterPropertyValue( \Illuminate\Database\Eloquent\Builder $query ) {
		/* @var $table string */
		$table = $this->getTable();
		
		/* @var $alias string */
		if( null == ( $alias = $query->getQuery()->getFromAlias() ) ) {
			$alias = $table;
		}
		
		$query
			->whereNotExists(function($q) use( $alias ){
				$q->select(ocme()->db()->raw(1))
					->from('ocme_filter_property_value')
					->whereColumn($alias . '.' . $this->getKeyName(), 'ocme_filter_property_value.' . $this->getKeyName());
			});
	}
	
}