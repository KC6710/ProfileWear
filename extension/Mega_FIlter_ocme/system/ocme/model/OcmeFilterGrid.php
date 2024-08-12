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
 * @property int $module_id
 * @property int $parent_id
 * @property string $type
 * @property int $sort_order
 * @property array $settings
 * 
 * @property Module $module
 * @property OcmeFilterGrid $parent
 */

class OcmeFilterGrid extends \Ocme\Database\Model {
	
	const TYPE_ROW = 'row';
	const TYPE_COLUMN = 'column';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ocme_filter_grid';

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
		'module_id', 'parent_id', 'type', 'sort_order', 'settings',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array(
		'parent_id', 'settings',
	);
	
	// Static functions ////////////////////////////////////////////////////////
	
	public static function boot() {
		parent::boot();
		
		self::deleted(function( $ocme_filter_grid ){			
			/* @var $ocme_filter_grid_condition OcmeFilterGridCondition */
			foreach( OcmeFilterGridCondition::where('ocme_filter_grid_id', $ocme_filter_grid->id)->get() as $ocme_filter_grid_condition ) {
				$ocme_filter_grid_condition->delete();
			}
		});
	}
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	public function getSettingsAttribute( $v ) {
		return $v ? json_decode( $v, true ) : array();
	}
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	public function setSettingsAttribute( $v ) {
		$this->attributes['settings'] = $v ? json_encode( $v ) : null;
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function module() {
		return $this->belongsTo('\Ocme\Model\Module', 'module_id', 'module_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function parent() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterGrid', 'parent_id', 'id');
	}
	
}