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
 * @property int $language_id
 * @property string $name
 * @property string $description
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keyword
 * 
 * @param Category $category
 * @param Language $language
 */

class CategoryDescription extends \Ocme\Database\Model\Description {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'category_description';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'category_id', 'language_id', 'name', 'description', 'meta_title', 'meta_description', 'meta_keyword',
	);
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function category() {
		return $this->belongsTo('\Ocme\Model\Category', 'category_id', 'category_id');
	}
	
}