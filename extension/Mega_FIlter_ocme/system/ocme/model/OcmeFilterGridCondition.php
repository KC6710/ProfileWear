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
 * @property int $id
 * @property int $ocme_filter_grid_id
 * @property int $ocme_filter_condition_id
 * @property int $vid
 * @property string $vtype
 * @property string $vname
 * @property int $sort_order
 * 
 * @property OcmeFilterGrid $ocme_filter_grid
 * @property OcmeFilterCondition $ocme_filter_condition
 */

class OcmeFilterGridCondition extends \Ocme\Database\Model {
	
	const VTYPE_BASE_ATTRIBUTE = 'base_attribute';
	const VTYPE_ATTRIBUTE = 'attribute';
	const VTYPE_OPTION = 'option';
	const VTYPE_FILTER_GROUP = 'filter_group';
	const VTYPE_FEATURE = 'feature';
	const VTYPE_PROPERTY = 'property';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ocme_filter_grid_condition';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = array(
		'sort_order' => 0,
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'ocme_filter_grid_id', 'ocme_filter_condition_id', 'vid', 'vtype', 'vname', 'sort_order',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array(
		'vid', 'vname',
	);
	
	public static function boot() {
		parent::boot();
	}
	
	// Static functions ////////////////////////////////////////////////////////
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_grid() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterGrid', 'ocme_filter_grid_id', 'id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_condition() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterCondition', 'ocme_filter_condition_id', 'id');
	}
	
}