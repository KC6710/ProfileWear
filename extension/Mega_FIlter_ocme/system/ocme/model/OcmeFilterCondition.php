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
 * @property string $condition_type
 * @property string $name
 * @property int $record_id
 * @property string $status
 * @property string $type
 * @property int $sort_order
 * @property array $setting
 * 
 * @property Module $module
 * @property Attribute $attribute
 * @property AttributeGroup $attribute_group
 * @property FilterGroup $filter_group
 * @property Option $option
 */

class OcmeFilterCondition extends \Ocme\Database\Model {
	
	const CONDITION_TYPE_BASE_ATTRIBUTE = 'base_attribute';
	const CONDITION_TYPE_ATTRIBUTE = 'attribute';
	const CONDITION_TYPE_ATTRIBUTE_GROUP = 'attribute_group';
	const CONDITION_TYPE_OPTION = 'option';
	const CONDITION_TYPE_FILTER_GROUP = 'filter_group';
	const CONDITION_TYPE_FEATURE = 'feature';
	const CONDITION_TYPE_PROPERTY = 'property';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ocme_filter_condition';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'module_id', 'condition_type', 'name', 'record_id', 'status', 'type', 'sort_order', 'setting',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array(
		'name', 'record_id', 'type', 'sort_order', 'setting',
	);
	
	// Static functions ////////////////////////////////////////////////////////
	
	public static function boot() {
		parent::boot();
		
		self::deleted(function( $ocme_filter_condition ){			
			/* @var $ocme_filter_grid_condition OcmeFilterGridCondition */
			foreach( OcmeFilterGridCondition::where('ocme_filter_condition_id', $ocme_filter_condition->id)->get() as $ocme_filter_grid_condition ) {
				$ocme_filter_grid_condition->delete();
			}
		});
	}
	
	/**
	 * @param array $params
	 * @return null|OcmeFilterCondition
	 */
	public static function addOrUpdate( array $params ) {
		/* @var $module_id int */
		if( null == ( $module_id = ocme()->arr()->get( $params, 'module_id' ) ) ) {
			return false;
		}
		
		/* @var $condition_type string */
		if( null == ( $condition_type = ocme()->arr()->get( $params, 'condition_type' ) ) ) {
			return false;
		}
		
		if( ! in_array( $condition_type, array( self::CONDITION_TYPE_ATTRIBUTE, self::CONDITION_TYPE_ATTRIBUTE_GROUP, self::CONDITION_TYPE_BASE_ATTRIBUTE, self::CONDITION_TYPE_FEATURE, self::CONDITION_TYPE_FILTER_GROUP, self::CONDITION_TYPE_OPTION ) ) ) {
			return false;
		}
		
		/* @var $name string|null */
		$name = ocme()->arr()->get( $params, 'name' );
		
		if( in_array( $condition_type, array( self::CONDITION_TYPE_BASE_ATTRIBUTE ) ) && ! $name ) {
			return false;
		}
		
		/* @var $record_id int|null */
		$record_id = ocme()->arr()->get( $params, 'record_id' );
		
		if( in_array( $condition_type, array( self::CONDITION_TYPE_ATTRIBUTE, self::CONDITION_TYPE_ATTRIBUTE_GROUP, self::CONDITION_TYPE_FEATURE, self::CONDITION_TYPE_FILTER_GROUP, self::CONDITION_TYPE_OPTION ) ) && ! $record_id ) {
			return false;
		}
		
		$ocme_filter_condition = self::firstOrNew( compact( 'module_id', 'condition_type', 'name', 'record_id' ) )->fill(array(
			'status' => ocme()->arr()->get( $params, 'status', 0 ),
			'type' => ocme()->arr()->get( $params, 'type' ),
			'sort_order' => ocme()->arr()->get( $params, 'sort_order' ),
			'setting' => ocme()->arr()->get( $params, 'setting' )
		));
		
		$ocme_filter_condition->save();
		
		return $ocme_filter_condition;
	}
	
	public static function getStatuses() {
		/* @var $statuses array */
		$statuses = array( '1' );

		if( ocme()->ocRegistry()->get('customer')->isLogged() /*|| ocme()->user()->isLogged()*/ ) {
			$statuses[] = 'customers';
		}

		if( ! ocme()->ocRegistry()->get('customer')->isLogged() /*&& ! ocme()->user()->isLogged()*/ ) {
			$statuses[] = 'guests';
		}

		if( ocme()->user()->isLogged() ) {
			$statuses[] = 'admin';
		}
		
		return $statuses;
	}
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeCheckStatus( $query ) {
		$query->whereIn('status', self::getStatuses());
	}
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	public function getSettingAttribute( $v ) {
		return $v ? json_decode( $v, true ) : array();
	}
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	public function setSortOrderAttribute( $v ) {
		$this->attributes['sort_order'] = is_null( $v ) || $v === '' ? null : $v;
	}
	
	public function setSettingAttribute( $v ) {
		if( ! $v ) {
			$v = null;
		} else if( is_array( $v ) || is_object( $v ) ) {
			$v = json_encode( $v );
		}
		
		$this->attributes['setting'] = $v;
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
	public function attribute() {
		return $this->belongsTo('\Ocme\Model\Attribute', 'record_id', 'attribute_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function attribute_group() {
		return $this->belongsTo('\Ocme\Model\AttributeGroup', 'record_id', 'attribute_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function filter_group() {
		return $this->belongsTo('\Ocme\Model\FilterGroup', 'record_id', 'filter_group_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function option() {
		return $this->belongsTo('\Ocme\Model\Option', 'record_id', 'option_id');
	}
	
}