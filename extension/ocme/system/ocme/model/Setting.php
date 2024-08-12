<?php namespace Ocme\Model;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 * 
 * @property int $setting_id
 * @property int $store_id
 * @property string $code
 * @property string $key
 * @property mixed $value
 * @property bool $serialized
 * 
 * @property Store $store
 */

class Setting extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'setting';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'setting_id';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'store_id', 'code', 'key', 'value', 'serialized',
	);
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	public function getValueAttribute( $v ) {
		return $this->serialized ? json_decode( $v, true ) : array();
	}
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	public function setValueAttribute( $v ) {
		$this->attributes['serialized'] = is_array( $v ) ? '1' : '0';
		$this->attributes['value'] = $this->attributes['serialized'] ? json_encode( $v ) : $v;
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function store() {
		return $this->belongsTo('\Ocme\Model\Store', 'store_id', 'store_id');
	}
}