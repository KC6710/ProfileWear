<?php namespace Ocme\Database\Attribute;

/**
 * @property bool $status
 */

trait Status {
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeStatusEnabled( $query ) {
		$query->where('status', '1');
	}
	
	public function scopeStatusDisabled( $query ) {
		$query->where('status', '0');
	}
	
	// Accessors ////////////////////////////////////////////////////////////////
	
	/**
	 * @param bool $value
	 * @return bool|null
	 */
	public function getStatusAttribute( $value ) {
		return $value === null ? null : (bool) $value;
	}
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	/**
	 * @var bool $value
	 */
	public function setStatusAttribute( $value ) {
		$this->attributes['status'] = $value ? '1' : '0';
	}
	
}
