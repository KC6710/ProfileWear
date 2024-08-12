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
 * @property int $customer_group_id
 * @property int $language_id
 * @property string $name
 * @property string $description
 * 
 * @param CustomerGroup
 * @param Language $language
 */

class CustomerGroupDescription extends \Ocme\Database\Model\Description {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_group_description';
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function customer_group() {
		return $this->belongsTo('\Ocme\Model\CustomerGroup', 'customer_group_id', 'customer_group_id');
	}
	
}