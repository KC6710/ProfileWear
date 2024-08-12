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
 * @property int $layout_route_id
 * @property int $layout_id
 * @property int $store_id
 * @property string $route
 * 
 * @property Layout $layout
 * @property Store $store
 */

class LayoutRoute extends \Ocme\Database\Model {
	
	const ROUTE_PRODUCT_CATEGORY = 'product/category';
	const ROUTE_MANUFACTURER_INFO = 'product/manufacturer/info';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'layout_route';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'layout_route_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'layout_id', 'store_id', 'route',
	);
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function layout() {
		return $this->belongsTo('\Ocme\Model\Layout', 'layout_id', 'layout_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function store() {
		return $this->belongsTo('\Ocme\Model\Store', 'store_id', 'store_id');
	}
	
}