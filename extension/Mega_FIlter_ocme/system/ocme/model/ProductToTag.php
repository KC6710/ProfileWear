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
 * @property int $product_tag_id
 * 
 * @property Product $product
 * @property ProductTag $product_tag
 */

class ProductToTag extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_to_tag';

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
		'product_id', 'product_tag_id',
	);
	
	// Functions ///////////////////////////////////////////////////////////////

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        $query->where( 'product_id', $this->product_id )->where( 'product_tag_id', $this->product_tag_id );

        return $query;
    }
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeRedundant( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->where(function($q){
				$q
					->whereNotExists(function($q) {
						$q->select(ocme()->db()->raw(1))
							->from('product')
							->whereColumn('product.product_id', 'product_to_tag.product_id');
					})
					->orWhereNotExists(function($q) {
						$q->select(ocme()->db()->raw(1))
							->from('product_tag')
							->whereColumn('product_tag.product_tag_id', 'product_to_tag.product_tag_id');
					});
			});
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
	public function product_tag() {
		return $this->belongsTo('\Ocme\Model\ProductTag', 'product_tag_id', 'product_tag_id');
	}
	
}