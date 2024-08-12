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
 * @property int $event_id
 * @property string $code
 * @property string $trigger
 * @property string $action
 * @property bool $status
 * @property int $sort_order
 */

class Event extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'event_id';

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
		'code', 'trigger', 'action', 'status', 'sort_order',
	);
	
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct( array $attributes = array() ) {
		if( version_compare( VERSION, '4', '>=' ) ) {
			$this->fillable[] = 'description';
		}
		
		parent::__construct( $attributes );
	}
}