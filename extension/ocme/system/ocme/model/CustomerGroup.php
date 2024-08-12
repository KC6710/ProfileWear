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
 * @property int $approval
 * @property int $sort_order
 */

class CustomerGroup extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_group';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'customer_group';

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
		'approval', 'sort_order',
	);
	
	public static function createTree( array $conditions = array() ) {
		/* @var $nodes array */
		$nodes = array();
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = self::query()
			->leftJoin('customer_group_description', 'customer_group_description.customer_group_id', '=', 'customer_group.customer_group_id')
			->where('customer_group_description.language_id', ocme()->oc()->registry()->get('config')->get('config_language_id'))
			->orderBy('name');
		
		/* @var $phrase string */
		if( null !== ( $phrase = ocme()->arr()->get( $conditions, 'phrase' ) ) ) {
			$query->where('name', 'LIKE', '%' . $phrase . '%');
		}
		
		/* @var $results \Illuminate\Contracts\Pagination\LengthAwarePaginator */
		$results = $query->paginate( 100 );
		
		/* @var $customer_group CustomerGroup */
		foreach( $query->get() as $customer_group ) {
			$nodes[] = self::createTreeNode( $customer_group );
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
			'value' => $node->customer_group_id,
			'children' => $nodes,
		);
	}
	
}