<?php namespace Ocme\Database\Model;

abstract class Description extends \Ocme\Database\Model {

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = null;

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;
	
	// Boot ////////////////////////////////////////////////////////////////////
	
	public static function boot() {
		parent::boot();
		
		self::creating(function( $row ){
			if( is_null( $row->seo_url ) ) {
				$row->seo_url = ocme()->str()->slug( $row->name );
			}
		});
	}
	
	// Functions ///////////////////////////////////////////////////////////////

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
		/* @var $key string */
        $key = $this->getRelationshipKeyName();

        return $query
			->where( $key, isset( $this->original[$key] ) ? $this->original[$key] : $this->getAttribute( $key ) )
			->where( 'language_id', isset( $this->original['language_id'] ) ? $this->original['language_id'] : $this->getAttribute( 'language_id' ) );
    }
	
	/**
	 * Get relationship key name
	 * 
	 * @return string
	 */
	public function getRelationshipKeyName() {
		/* @var $key string */
        $key = class_basename( $this );
		$key = snake_case( $key );
		$key = explode( '_', $key );
		$key = array_slice( $key, 0, -1 );
		$key = implode( '_', $key ) . '_id';
		
		return $key;
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function language() {
		return $this->belongsTo('\Ocme\Model\Language', 'language_id', 'language_id');
	}
	
}