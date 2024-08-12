<?php namespace Ocme\Model;

use Ocme\Model\ProductOption;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 * 
 * @property int $option_id
 * @property string $type
 * @property int $sort_order
 * @property array $store_ids
 * @property bool $with_image
 * @property bool $with_color
 * 
 * @property OcmeFilterProperty $ocme_filter_property
 */

class Option extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;
	use \Ocme\Database\MissingOcmeFilterProperty;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'option';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'option_id';

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
		'type', 'sort_order', 'store_ids', 'with_image', 'with_color',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $option Option */
		self::created(function( $option ){
			/* @var $option Option */
			
			self::eventCreated( $option );
		});
		
		/* @var $option Option */
		self::saved(function( $option ){
			/* @var $option Option */
			
			self::eventSaved( $option );
		});
		
		/* @var $option Option */
		self::deleted(function( $option ){
			/* @var $option Option */
			
			self::eventDeleted( $option );
		});
	}
	
	/**
	 * @param \Ocme\Model\Option $option
	 */
	public static function eventCreated( Option $option ) {
		OcmeFilterProperty::create(array(
			'option_id' => $option->id,
		));
	}
	
	/**
	 * @param \Ocme\Model\Option $option
	 */
	public static function eventSaved( Option $option, $force = false ) {
		if( $force || $option->isDirty('type') ) {			
			ProductOption::where('option_id', $option->option_id)
				->where(function($q) use( $option ) {
					$q
						->{$option->type == 'date' ? 'orWhereNull' : 'orWhereNotNull'}('vdate')
						->{$option->type == 'time' ? 'orWhereNull' : 'orWhereNotNull'}('vtime')
						->{$option->type == 'datetime' ? 'orWhereNull' : 'orWhereNotNull'}('vdatetime');
				})
				->update(array(
					'vdate' => $option->type == 'date' ? ocme()->db()->raw('value') : null,
					'vtime' => $option->type == 'time' ? ocme()->db()->raw('value') : null,
					'vdatetime' => $option->type == 'datetime' ? ocme()->db()->raw('value') : null,
				));
		}
	}
	
	/**
	 * @param \Ocme\Model\Option $option
	 */
	public static function eventDeleted( Option $option ) {
		if( $option->ocme_filter_property ) {
			$option->ocme_filter_property->delete();
		}
		
		$option->descriptions()->delete();
		
		/* @var $ocme_filter_condition OcmeFilterCondition */
		foreach( OcmeFilterCondition::where('condition_type', OcmeFilterCondition::CONDITION_TYPE_OPTION)
			->where('record_id', $option->option_id)
			->get() as $ocme_filter_condition 
		) {
			$ocme_filter_condition->delete();
		}
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function ocme_filter_property() {
		return $this->hasOne('\Ocme\Model\OcmeFilterProperty', 'option_id', 'option_id');
	}
	
}