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
 * @property int $product_tag_id
 * @property int $language_id
 * @property string $name
 * 
 * @param ProductTag $product_tag
 * @param Language $language
 */

class ProductTagDescription extends \Ocme\Database\Model\Description {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_tag_description';
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product_tag() {
		return $this->belongsTo('\Ocme\Model\ProductTag', 'product_tag_id', 'product_tag_id');
	}
	
}