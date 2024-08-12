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
 * @property int $category_id
 * @property string $image
 * @property int $parent_id
 * @property int $top
 * @property int $column
 * @property int $sort_order
 * @property bool $status
 * @property string $date_added
 * @property string $date_modified
 */

class Category extends \Ocme\Database\Model {
	
	use \Ocme\Database\WithDescription;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'category';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'category_id';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = array(
		'parent_id' => 0,
		'sort_order' => 0,
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'image', 'parent_id', 'top', 'column', 'sort_order', 'status', 'date_added', 'date_modified',
	);
	
	public static function createTree( array $conditions = array(), $parent_id = null ) {
		/* @var $nodes array */
		$nodes = array();
		
		/* @var $num_nodes bool */
		$num_nodes = true;
		
		/* @var $separator string */
		$separator = md5( microtime( true ) );
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = self::query()
			->withDescription()
			->orderBy('name');
		
		if( ocme()->arr()->get( $conditions, 'with_path' ) ) {
			$query = CategoryPath::select(array(
				'category.*',
				'category_description.*',
				ocme()->db()->connection()->raw("GROUP_CONCAT(`" . DB_PREFIX . "cd2`.`name` ORDER BY `" . DB_PREFIX . "category_path`.`level` SEPARATOR '" . $separator . "') AS `path`")
			))
				->leftJoin('category', 'category_path.category_id', '=', 'category.category_id')
				->leftJoin('category AS c2', 'category_path.path_id', '=', 'c2.category_id')
				->leftJoin('category_description', 'category_path.category_id', '=', 'category_description.category_id')
				->leftJoin('category_description AS cd2', 'category_path.path_id', '=', 'cd2.category_id')
				->where('category_description.language_id', ocme()->oc()->registry()->get('config')->get('config_language_id'))
				->where('cd2.language_id', ocme()->oc()->registry()->get('config')->get('config_language_id'))
				->groupBy('category.category_id');
		}
		
		/* @var $only_ids array */
		if( null != ( $only_ids = ocme()->arr()->get( $conditions, 'only_ids' ) ) ) {
			if( ! is_array( $only_ids ) ) {
				$only_ids = explode( ',', $only_ids );
			}
			
			$query->whereIn('category.category_id', $only_ids);
		}
		
		if( ! is_null( $parent_id ) ) {
			$query->where('category.parent_id', $parent_id);
		}
		
		/* @var $phrase string */
		if( null !== ( $phrase = ocme()->arr()->get( $conditions, 'phrase' ) ) ) {
			$query->where('category_description.name', 'LIKE', '%' . $phrase . '%');
			
			$num_nodes = false;
		}
		
		/* @var $values array */
		if( null == ( $values = ocme()->arr()->get( $conditions, 'values' ) ) ) {
			$values = array();
		}
		
		/* @var $results \Illuminate\Contracts\Pagination\LengthAwarePaginator */
		$results = $query->paginate( max( 1, min( 100, (int) ocme()->request()->input('per_page', 100) ) ) );
		
		/* @var $category Category */
		foreach( $results->items() as $category ) {
			/* @var $node array */
			$node = [
				'num_nodes' => $num_nodes ? self::query()->where('parent_id', $category->category_id)->count() : 0,
				'label' => $category->name,
				'value' => $category->category_id,
				'children' => array()
			];
			
			if( ocme()->arr()->get( $conditions, 'with_path' ) ) {
				$node['path'] = array_slice( explode( $separator, $category->path ), 0, -1 );
			}
			
			$nodes[] = $node;
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
	
}