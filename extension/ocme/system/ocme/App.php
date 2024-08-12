<?php namespace Ocme;

/**
 * Base aliases
 * 
 * @method \Ocme\Support\Arr arr()
 * @method \Ocme\Support\Carbon carbon()
 * @method \Ocme\Support\Collection collection()
 * @method \Ocme\Support\Config config()
 * @method \Ocme\Support\Str str()
 * @method \Ocme\Utils utils()
 * @method \Ocme\Database\Connection db()
 * @method \Ocme\Support\Request request()
 * @method \Ocme\Support\Oc oc()
 * @method \Cart\User user()
 * @method \Ocme\Support\Data data()
 * @method \Ocme\Support\Variable variable()
 * @method Support\MobileDetect mdetect()
 * @method \Ocme\Msg msg()
 * @method \Ocme\Api api()
 * @method \Ocme\License license()
 * @method \Ocme\Url url()
 * @method \Ocme\Translator translator()
 * @method \Ocme\Support\Cache cache()
 */

class App extends \Illuminate\Container\Container {
	
	/**
	 * @var array
	 */
	protected static $models = array();
	
	/**
	 * @var string
	 */
	protected static $ak = array();
	
	/**
	 * @var array
	 */
	protected static $trans = array();
	
	/**
	 * @var bool
	 */
	protected static $ajax_rendering = false;
	
	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	
	public function __call( $name, $arguments ) {
		return $this->make( $name, $arguments );
	}
	
	public function ajaxRendering( $ajax_rendering = null ) {
		if( is_null( $ajax_rendering ) ) {
			return self::$ajax_rendering;
		}
		
		self::$ajax_rendering = $ajax_rendering;
	}
	
	public function isInstalled() {
		return $this->oc()->registry()->get('config')->get('ocme_mfp_installed_at');
	}
	
	public function environment() {
		if( version_compare( VERSION, '4', '>=' ) ) {
			return $this->oc()->registry()->get('config')->get('application');
		}
		
		if(
			ocme()->str()->endsWith( DIR_APPLICATION, 'catalog/' )
				||
			ocme()->str()->endsWith( DIR_APPLICATION, 'catalog' . DIRECTORY_SEPARATOR )
		) {
			return 'Catalog';
		}
		
		return 'Admin';
	}
	
	public function model( $name ) {
		if( strpos( $name, '/' ) !== false ) {
			$this->oc()->registry()->get('load')->model( $name );
			
			return $this->oc()->registry()->get( 'model_' . str_replace( '/', '_', $name ) );
		}
		
		/* @var $class string */
		$class = '\\Ocme\OpenCart\\' . $this->environment() . '\\Model\\' . ocme()->str()->studly( $name );
		
		if( ! in_array( $class, self::$models ) ) {
			self::$models[$class] = new $class;
		}
		
		return self::$models[$class];
	}
	
	public function trans( $key = null, array $replace = array() ) {
		if( is_null( $key ) ) {
			return $this->oc()->registry()->get('language');
		}
		
		if( strpos( $key, ' ' ) === false ) {
			if( strpos( $key, '::' ) !== false ) {
				/* @var $trans mixed */
				$trans = ocme()->translator()->get( $key );
			} else if( strpos( $key, '/' ) !== false ) {
				/* @var $prefix string */
				$prefix = md5( $key );

				$this->oc()->registry()->get('load')->language( $key, $prefix );

				if( version_compare( VERSION, '4', '>=' ) ) {
					$trans = $this->oc()->registry()->get('language')->all( $prefix );
				} else {
					$trans = $this->oc()->registry()->get('language')->get( $prefix )->all();
				}
			} else {
				/* @var $trans string */
				$trans = $this->oc()->registry()->get('language')->get( $key );
			}
		} else {
			$trans = $key;
		}
		
		if( is_string( $trans ) ) {
			foreach( $replace as $search => $replacement ) {
				$trans = str_replace( ':' . $search, $replacement, $trans );
			}
		}
		
		return $trans;
	}
	
}