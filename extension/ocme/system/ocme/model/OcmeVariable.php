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
 * @property int $id
 * @property string $type
 * @property string $name
 * @property mixed $value
 * @property bool $serialized
 */

class OcmeVariable extends \Ocme\Database\Model {
	
	const TYPE_BREAKPOINT = 'breakpoint';
	const TYPE_COLOR = 'color';
	const TYPE_FILTER_GLOBAL = 'filter_global';
	const TYPE_ATTRIBUTE = 'attribute';
	const TYPE_FILTER_SEO_CONFIG = 'filter_seo_config';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ocme_variable';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'type', 'name', 'value', 'serialized',
	);

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array(
		'value',
	);
	
	/**
	 * @var array
	 */
	protected static $cache = array();
	
	// Static functions ////////////////////////////////////////////////////////
	
	public static function boot() {
		parent::boot();
		
		/* @var $ocme_variable OcmeVariable */
		self::saved(function( $ocme_variable ){
			/* @var $ocme_variable OcmeVariable */
			
			if( $ocme_variable->type == 'filter' && $ocme_variable->name == 'include_option_prices' ) {
				Event::firstOrNew(array(
					'code' => 'ocme_mfp_filter',
					'trigger' => 'catalog/model/catalog/product/getProduct/after',
					'action' => ocme_extension_path('extension/module/ocme_mfp_filter/eventGetProduct')
				))->fill(array(
					'status' => $ocme_variable->value ? '1' : '0',
				))->save();
			}
		});
	}
	
	public static function getByType( $variable_type ) {
		if( ! isset( self::$cache[$variable_type] ) ) {
			self::$cache[$variable_type] = array();
			
			/* @var $query \Illuminate\Database\Eloquent\Builder */
			$query = OcmeVariable::where('type', $variable_type)
				->where(function($q){
					$q->whereNull('store_id')->orWhere('store_id', ocme()->oc()->registry()->get('config')->get('conig_store_id'));
				})
				->orderBy('store_id');
				
			/* @var $ocme_variable OcmeVariable */
			foreach( $query->get() as $ocme_variable ) {
				if( $variable_type == self::TYPE_FILTER_GLOBAL ) {
					/* @var $parts array */
					$parts = explode( '.', $ocme_variable->name );

					/* @var $type string */
					$type = array_shift( $parts );

					/* @var $key string */
					$key = array_shift( $parts );

					if( $type == 'trans' && $key == 'conditions' ) {
						/* @var $condition_type string */
						$condition_type = array_shift( $parts );

						/* @var $name string */
						$name = array_shift( $parts );

						self::$cache[$variable_type][$type][$key][$condition_type][$name][implode('.', $parts)] = $ocme_variable->value;
					} else {				
						if( $parts ) {
							self::$cache[$variable_type][$type][$key][implode('.', $parts)] = $ocme_variable->value;
						} else {
							self::$cache[$variable_type][$type][$key] = $ocme_variable->value;
						}
					}
				} else {
					ocme()->arr()->set( self::$cache[$variable_type], $ocme_variable->name, $ocme_variable->value );
				}
			}
		}
		
		return self::$cache[$variable_type];
	}
	
	public static function getFilterGlobal() {
		return self::getByType( self::TYPE_FILTER_GLOBAL );
	}
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	public function getValueAttribute( $v ) {
		return $this->serialized ? json_decode( $v, true ) : $v;
	}
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	public function setValueAttribute( $v ) {
		if( is_array( $v ) || is_object( $v ) ) {
			$v = json_encode( $v );

			$this->attributes['serialized'] = 1;
		} else {
			$this->attributes['serialized'] = 0;

			if( $v === '' ) {
				$v = null;
			}
		}
		
		$this->attributes['value'] = $v;
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
}