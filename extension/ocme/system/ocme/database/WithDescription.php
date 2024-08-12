<?php namespace Ocme\Database;

trait WithDescription {
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	/**
	 * @param Illuminate\Database\Eloquent\Builder $query
	 */
	public function scopeWithDescription( \Illuminate\Database\Eloquent\Builder $query, $table_description_alias = null ) {
		/* @var $table string */
		$table = $this->getTable();
		
		/* @var $foreign_table string */
		if( null == ( $foreign_table = $query->getQuery()->getFromAlias() ) ) {
			$foreign_table = $table;
		}
		
		/* @var $table_description */
		$table_description = $table . '_description';
		
		if( is_null( $table_description_alias ) ) {
			$table_description_alias = $table_description;
		} else {
			$table_description .= ' AS ' . $table_description_alias;
		}
		
		/* @var $key string */
		$key = $this->getKeyName();
		
		$query
			->leftJoin($table_description, $table_description_alias . '.' . $key, '=', $foreign_table . '.' . $key)
			->where($table_description_alias . '.language_id', ocme()->oc()->registry()->get('config')->get('config_language_id'));
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function descriptions() {
		return $this->hasMany(get_class( $this ) . 'Description', $this->getKeyName(), $this->getKeyName());
	}
	
}