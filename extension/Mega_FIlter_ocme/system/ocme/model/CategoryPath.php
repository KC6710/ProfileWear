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
 * @property int $category_id
 * @property int $path_id
 * @property int $level
 * 
 * @param Category $category
 * @param Category $path
 */

class CategoryPath extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'category_path';

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
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'category_id', 'path_id', 'level',
	);
	
	// Functions ///////////////////////////////////////////////////////////////

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        $query->where( 'category_id', $this->category_id )->where( 'path_id', $this->path_id );

        return $query;
    }
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function category() {
		return $this->belongsTo('\Ocme\Model\Category', 'category_id', 'category_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function path() {
		return $this->belongsTo('\Ocme\Model\Category', 'path_id', 'category_id');
	}
	
}