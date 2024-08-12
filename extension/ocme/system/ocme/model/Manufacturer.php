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
 * @property int $manufacturer_id
 * @property string $name
 * @property string $image
 * @property int $sort_order
 */

class Manufacturer extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manufacturer';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'manufacturer_id';

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
		'name', 'image', 'sort_order',
	);
	
	public static function createTree( array $conditions = array() ) {
		/* @var $tree array */
		$nodes = array();
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = self::query()->orderBy('name');
		
		/* @var $phrase string */
		if( null !== ( $phrase = ocme()->arr()->get( $conditions, 'phrase' ) ) ) {
			$query->where('name', 'LIKE', '%' . $phrase . '%');
		}
		
		/* @var $results \Illuminate\Contracts\Pagination\LengthAwarePaginator */
		$results = $query->paginate( 100 );
		
		/* @var $manufacturer Manufacturer */
		foreach( $results->items() as $manufacturer ) {
			$nodes[] = self::createTreeNode( $manufacturer );
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
			'value' => $node->manufacturer_id,
			'children' => $nodes,
		);
	}
	
}