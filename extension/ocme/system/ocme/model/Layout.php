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
 * @property int $layout_id
 * @property string $name
 * 
 * @property LayoutModule[] $layout_modules
 * @property LayoutRoute[] $layout_routes
 */

class Layout extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'layout';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'layout_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'name',
	);
	
	public static function boot() {
		parent::boot();
		
		/* @var $layout Layout */
		self::deleted(function( $layout ){
			/* @var $layout Layout */
			
			/* @var $layout_module LayoutModule */
			foreach( $layout->layout_modules as $layout_module ) {
				$layout_module->delete();
			}
			
			/* @var $layout_route LayoutRoute */
			foreach( $layout->layout_routes as $layout_route ) {
				$layout_route->delete();
			}
		});
	}
	
	public static function createTree( array $conditions = array() ) {
		/* @var $nodes array */
		$nodes = array();
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = self::query()->orderBy('name');
		
		/* @var $phrase string */
		if( null !== ( $phrase = ocme()->arr()->get( $conditions, 'phrase' ) ) ) {
			$query->where('name', 'LIKE', '%' . $phrase . '%');
		}
		
		/* @var $results \Illuminate\Contracts\Pagination\LengthAwarePaginator */
		$results = $query->paginate( 100 );
		
		/* @var $store Store */
		foreach( $results->items() as $store ) {
			$nodes[] = self::createTreeNode( $store );
		}
		
		return array(
			'nodes' => $nodes,
			'pagination' => array(
				'per_page' => $results->perPage(),
				'total' => $results->total(),
				'last_page' => $results->lastPage(),
				'current_page' => $results->currentPage()
			)
		);
	}
	
	private static function createTreeNode( $node, array $nodes = array() ) {
		return array(
			'num_nodes' => count( $nodes ),
			'label' => $node->name,
			'value' => $node->layout_id,
			'children' => $nodes,
		);
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function layout_modules() {
		return $this->hasMany('\Ocme\Model\LayoutModule', 'layout_id', 'layout_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function layout_routes() {
		return $this->hasMany('\Ocme\Model\LayoutRoute', 'layout_id', 'layout_id');
	}
	
}