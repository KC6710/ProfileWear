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
 * @property int $store_id
 * @property string $name
 * @property string $url
 * @property string $ssl
 */

class Store extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'store';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'store_id';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'store_id', 'name', 'url', 'ssl',
	);
	
	////////////////////////////////////////////////////////////////////////////
	
	public static function defaultStore() {
		return array(
			'store_id' => 0,
			'name' => ocme()->ocRegistry()->get('config')->get('config_name'),
			'url' => defined( 'HTTP_CATALOG' ) ? HTTP_CATALOG : HTTP_SERVER,
			'ssl' => defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : ( defined( 'HTTPS_SERVER' ) ? HTTPS_SERVER : HTTP_SERVER ),
		);
	}
	
	public static function allStores() {
		/* @var $stores array */
		$stores = array( (object) self::defaultStore() );
		
		foreach( self::orderBy('name')->get() as $store ) {
			$stores[] = $store;
		}
		
		return $stores;
	}
	
	public static function createTree( array $conditions = array() ) {
		/* @var $nodes array */
		$nodes = array( self::createTreeNode( (object) self::defaultStore() ) );
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = self::orderBy('name');
		
		/* @var $phrase string */
		if( null !== ( $phrase = ocme()->arr()->get( $conditions, 'phrase' ) ) ) {
			$nodes = ocme()->arr()->where( $nodes, function( $node ) use( $phrase ){
				return mb_stripos( $node['label'], $phrase, 0, 'utf8' ) !== false;
			});
			
			$query->where('store.name', 'LIKE', '%' . $phrase . '%');
		}
		
		/* @var $store Store */
		foreach( $query->get() as $store ) {
			$nodes[] = self::createTreeNode( $store );
		}
		
		/* @var $keys array */
		$keys = array();
		
		foreach( $nodes as $k => $v ) {
			$keys[$k] = $v['label'];
		}
		
		array_multisort( $keys, SORT_STRING|SORT_ASC, $nodes );
		
		return array(
			'nodes' => $nodes,
			'pagination' => array(
				'per_page' => count( $nodes ),
				'total' => count( $nodes ),
				'last_page' => 1,
				'current_page' => 1
			)
		);
	}
	
	private static function createTreeNode( $node, array $nodes = array() ) {
		return array(
			'num_nodes' => count( $nodes ),
			'label' => $node->name,
			'value' => $node->store_id,
			'children' => $nodes,
		);
	}
}