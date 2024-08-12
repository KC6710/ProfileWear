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
 * @property int $seo_url_id
 * @property int $store_id
 * @property int $language_id
 * @property string $query
 * @property string $keyword
 * 
 * @property Store $store
 */

class SeoUrl extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seo_url';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'seo_url_id';

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
	protected $fillable = array();

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct( array $attributes = array() ) {
		if( version_compare( VERSION, '4', '>=' ) ) {
			$this->fillable = array( 'store_id', 'language_id', 'key', 'value', 'keyword' );
		} else {
			$this->fillable = array( 'store_id', 'language_id', 'query', 'keyword', );
		}
		
		parent::__construct( $attributes );
	}
	
	// Functions ///////////////////////////////////////////////////////////////
	
	/**
	 * @param array $data
	 * @return array
	 */
	public function queryData( array $data = array() ) {
		/* @var $url array */
		$url = version_compare( VERSION, '4', '>=' ) ? array( $this->key, $this->value ) : explode('=', $this->query);
		
		switch( $url[0] ) {
			case 'route':
			case 'language':
			case 'product_id':
			case 'manufacturer_id':
			case 'information_id': {
				$data[$url[0]] = $url[1];
				
				break;
			}
			case 'path':
			case 'category_id': {
				if( isset( $data['path'] ) ) {
					$data['path'] = implode( '_', array_unique( array_merge( explode( '_', $data['path'] ), explode( '_', $url[1] ) ) ) );
				} else {
					$data['path'] = $url[1];
				}
				
				break;
			}
			default : {
				$data['route'] = implode( '=', $url );
				
				break;
			}
		}
		
		return $data;
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function store() {
		return $this->belongsTo('\Ocme\Model\Store', 'store_id', 'store_id');
	}
}