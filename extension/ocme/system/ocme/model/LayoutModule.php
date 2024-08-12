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
 * @property int $layout_module_id
 * @property int $layout_id
 * @property string $code
 * @property string $position
 * @property int $sort_order
 * 
 * @property Layout $layout
 */

class LayoutModule extends \Ocme\Database\Model {
	
	const POSITION_CONTENT_TOP = 'content_top';
	const POSITION_CONTENT_BOTTOM = 'content_bottom';
	const POSITION_COLUMN_LEFT = 'column_left';
	const POSITION_COLUMN_RIGHT = 'column_right';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'layout_module';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'layout_module_id';

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
		'layout_id', 'code', 'position', 'sort_order',
	);
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function layout() {
		return $this->belongsTo('\Ocme\Model\Layout', 'layout_id', 'layout_id');
	}
	
}