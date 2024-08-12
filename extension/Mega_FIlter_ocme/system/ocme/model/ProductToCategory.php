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
 * @property int $product_id
 * @property int $category_id
 * 
 * @property Product $product
 * @property Category $category
 */

class ProductToCategory extends \Ocme\Database\Model {

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_to_category';

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
		'product_id', 'category_id',
	);
	
	// Functions ///////////////////////////////////////////////////////////////

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        $query->where( 'product_id', $this->product_id )->where( 'category_id', $this->category_id );

        return $query;
    }
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product() {
		return $this->belongsTo('\Ocme\Model\Product', 'product_id', 'product_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function category() {
		return $this->belongsTo('\Ocme\Model\Category', 'category_id', 'category_id');
	}
	
}