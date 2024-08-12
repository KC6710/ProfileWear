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
 */

class ProductTag extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_tag';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'product_tag_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'product_tag_id', 'language_id', 'name',
	);
	
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
	public function language() {
		return $this->belongsTo('\Ocme\Model\Language', 'language_id', 'language_id');
	}
	
}