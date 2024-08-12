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
 * @property int $filter_id
 * @property int $language_id
 * @property int $filter_group_id
 * @property string $name
 * 
 * @param Filter $filter
 * @param Language $language
 * @param FilterGroup $filter_group
 */

class FilterDescription extends \Ocme\Database\Model\Description {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filter_description';
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function filter() {
		return $this->belongsTo('\Ocme\Model\Filter', 'filter_id', 'filter_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function filter_group() {
		return $this->belongsTo('\Ocme\Model\FilterGroup', 'filter_group_id', 'filter_group_id');
	}
	
}