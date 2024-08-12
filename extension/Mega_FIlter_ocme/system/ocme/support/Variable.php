<?php namespace Ocme\Support;

use ArrayAccess;
use Ocme\Support\Arr;
use Ocme\Model\OcmeVariable;

class Variable extends VariablePhpDepends implements ArrayAccess
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = array();
	
	/**
	 * Breakpoints
	 * 
	 * @var array
	 */
	protected $breakpoints;
	
	/**
	 * Current store
	 * 
	 * @var int
	 */
	protected $store_id = 0;
	
	/**
	 * Initialized status
	 * 
	 * @var bool
	 */
	protected $initialized = false;

    /**
     * Create a new configuration repository.
     *
     * @param  array  $items
     * @return void
     */
    public function __construct(array $items = array())
    {
        $this->items = $items;
		$this->store_id = (int) ocme()->ocRegistry()->get('config')->get('config_store_id');
    }
	
	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function breakpoints() {
		if( is_null( $this->breakpoints ) ) {
			$this->breakpoints = ocme()->isInstalled() ? OcmeVariable::where('type', OcmeVariable::TYPE_BREAKPOINT)
				->orderBy(ocme()->db()->raw('`value` * 1'))
				->get() : ocme()->collection();
		}
		
		return $this->breakpoints;
	}
	
	/**
	 * Change current store
	 * 
	 * @param int $store_id
	 * @return \static
	 */
	public function store( $store_id = 0 ) {
		$this->store_id = $store_id;
		
		return $this;
	}
	
	protected function keyName( $key, $store_id = false ) {
		if( strpos( $key, '::' ) !== false ) {
			return $key;
		}
		
		if( $store_id === false ) {
			$store_id = $this->store_id;
		}
		
		return $this->keyStore( $store_id ) . '.' . $key;
	}
	
	protected function keyStore( $store_id ) {
		if( is_null( $store_id ) ) {
			$store_id = 'null';
		}
		
		return '::' . $store_id;
	}

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
		$this->initialize();
		
        return Arr::has($this->items, $this->keyName($key));
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }
		
		$this->initialize();
		
		if( $this->has($this->keyName($key)) ) {
			return Arr::get($this->items, $this->keyName($key), $default);
		}

        return Arr::get($this->items, $this->keyName($key, null), $default);
    }
	
	public function getAsJson($key, $default = null) {
		return Arr::getAsJson($this->get($key), $default);
	}
	
	public function getAsVue($key, $default = null) {
		return Arr::getAsVue($this->get($key), $default);
	}
	
	protected function initialize() {
		if( $this->initialized ) return;
		
		$this->initialized = true;
		
		$this->load(array( null, $this->store_id ));
	}
	
	protected function load( $store_ids ) {
		if( ! ocme()->isInstalled() ) {
			return;
		}
		
		if( ! is_array( $store_ids ) ) {
			$store_ids = array( $store_ids );
		}
		
		$store_ids = array_filter( $store_ids, function( $store_id ){
			return ! $this->has( $this->keyStore( $store_id ) );
		});
		
		if( ! $store_ids ) return;
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = OcmeVariable::query();
		
		/* @var $store_id int|null */
		foreach( $store_ids as $store_id ) {
			if( is_null( $store_id ) ) {
				$query->orWhereNull('store_id');
			} else {
				$query->orWhere('store_id', $store_id);
			}
		}
		
		/* @var $ocme_variable OcmeVariable */
		foreach( $query->orderBy('store_id')->get() as $ocme_variable ) {
			/* @var $key string */
			$key = $ocme_variable->name;
			
			/* @var $value mixed */
			$value = $ocme_variable->value;
			
			if( $ocme_variable->type == OcmeVariable::TYPE_FILTER_GLOBAL ) {
				/* @var $parts array */
				$parts = explode( '.', $ocme_variable->name );

				/* @var $type string */
				$type = array_shift( $parts );

				/* @var $subtype string */
				$subtype = array_shift( $parts );
				
				if( $type == 'trans' && $subtype == 'conditions' ) {
					/* @var $condition_type string */
					$condition_type = array_shift( $parts );

					/* @var $name string */
					$name = array_shift( $parts );
					
					$key = sprintf('%s.%s.%s.%s', $type, $subtype, $condition_type, $name);
					$value = array( implode('.', $parts) => $ocme_variable->value );
				} else {
					$key = sprintf('%s.%s', $type, $subtype);
					
					if( $parts ) {
						$key .= '.' . implode('.', $parts);
						//$value = array( implode('.', $parts) => $ocme_variable->value );
					}
				}
			}
			
			$this->set($this->keyStore( $ocme_variable->store_id ) . '.' . $ocme_variable->type . '.' . $key, $value);
		}
	}

    /**
     * Get many configuration values.
     *
     * @param  array  $keys
     * @return array
     */
    public function getMany($keys)
    {
        $config = array();

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                list($key, $default) = [$default, null];
            }

            $config[$key] = Arr::get($this->items, $this->keyName( $key ), $default);
        }

        return $config;
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $this->keyName($key), $value);
        }
    }

    /**
     * Prepend a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * Push a value onto an array configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key);

        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }
}
