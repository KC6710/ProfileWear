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
 * @property int $language_id
 * @property string $name
 * @property string $description
 * @property string $tag
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keyword
 * 
 * @param Product $product
 * @param Language $language
 */

class ProductDescription extends \Ocme\Database\Model\Description {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_description';
	
	// Functions ///////////////////////////////////////////////////////////////
	
	public function reIndexTags() {
		/* @var $tags array */
		$tags = array_filter( array_map(function( $tag ){
			return trim( $tag );
		}, explode( ',', $this->tag )), function( $tag ){
			return $tag !== '';
		});
		
		/* @var $existing_tags array */
		$existing_tags = array();
		
		/* @var $product_tag ProductTag */
		foreach( ProductTag::where('language_id', $this->language_id)->whereIn('name', $tags)->get() as $product_tag ) {
			$existing_tags[$product_tag->product_tag_id] = mb_strtolower( $product_tag->name );
		}
		
		/* @var $product_tag_ids array */
		$product_tag_ids = array_keys( $existing_tags );
		
		/* @var $diff array */
		$diff = array_filter( $tags, function( $tag ) use( $existing_tags ){
			return ! in_array( mb_strtolower( $tag, 'utf8' ), $existing_tags );
		});
		
		/* @var $tag string */
		foreach( $diff as $tag ) {
			/* @var $product_tag ProductTag */
			$product_tag = ProductTag::create(array(
				'language_id' => $this->language_id,
				'name' => $tag,
			));
			
			$product_tag_ids[] = $product_tag->product_tag_id;
		}
		
		ProductToTag::join('product_tag', 'product_to_tag.product_tag_id', '=', 'product_tag.product_tag_id')
			->where('product_tag.language_id', $this->language_id)
			->where('product_to_tag.product_id', $this->product_id)
			->delete();
		
		foreach( $product_tag_ids as $product_tag_id ) {
			ProductToTag::create(array(
				'product_id' => $this->product_id,
				'product_tag_id' => $product_tag_id,
			));
		}
	}
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeMissingTags( $query ) {
		$query
			->whereRaw('TRIM(`tag`) != ?', array( '' ))
			->whereExists(function($q){
				$q->select(ocme()->db()->raw(1))
					->from('product')
					->whereColumn('product_description.product_id', 'product.product_id');
			})
			->whereNotExists(function($q){
				$q->select(ocme()->db()->raw(1))
					->from('product_to_tag')
					->whereColumn('product_description.product_id', 'product_to_tag.product_id');
			})
			->groupBy('product_id');
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product() {
		return $this->belongsTo('\Ocme\Model\Product', 'product_id', 'product_id');
	}
	
}