<?php namespace Ocme\Support;

use Closure;
use DateTime;
use ArrayAccess;
use Carbon\Carbon;

class Cache extends CachePhpDepends implements ArrayAccess {

	/**
	 * The cache store implementation.
	 *
	 * @var \Illuminate\Contracts\Cache\Store
	 */
	protected $store;

	/**
	 * The default number of minutes to store items.
	 *
	 * @var int
	 */
	protected $default = 60;

	/**
	 * Create a new cache repository instance.
	 *
	 * @param  \Illuminate\Contracts\Cache\Store  $store
	 */
	public function __construct(\Illuminate\Contracts\Cache\Store $store)
	{
		$this->store = $store;
	}

	/**
	 * Determine if an item exists in the cache.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function has($key)
	{
		return ! is_null($this->get($key));
	}

	/**
	 * Retrieve an item from the cache by key.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$value = $this->store->get($key);

		return ! is_null($value) ? $value : value($default);
	}
	
	/**
	 * Retrieve an item from the cache from DB by key.
	 * 
	 * @param mixed $query
	 * @param \Closure $callback
	 * @return mixed
	 */
	public function db( $query, Closure $callback ) {
		if( ! ocme()->variable()->get('cache.db.status') ) {
			return $callback( $query );
		}
		
		/* @var $key string */
		$key = 'db.' . md5( is_string( $query ) ? $query : ocme()->db()->queryToRawSql( $query ) );
		
		/* @var $value mixed */
		if( null === ( $value = ocme()->cache()->get( $key ) ) ) {
			$value = $callback( $query );
			
			ocme()->cache()->add( $key, $value, ocme()->variable()->get('cache.db.time', 60) );
		}
		
		return $value;
	}

	/**
	 * Retrieve an item from the cache and delete it.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function pull($key, $default = null)
	{
		$value = $this->get($key, $default);

		$this->forget($key);

		return $value;
	}

	/**
	 * Store an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  \DateTime|int  $minutes
	 * @return void
	 */
	public function put($key, $value, $minutes)
	{
		$minutes = $this->getMinutes($minutes);

		if ( ! is_null($minutes))
		{
			$this->store->put($key, $value, $minutes);
		}
	}

	/**
	 * Store an item in the cache if the key does not exist.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  \DateTime|int  $minutes
	 * @return bool
	 */
	public function add($key, $value, $minutes)
	{
		if (is_null($this->get($key)))
		{
			$this->put($key, $value, $minutes); return true;
		}

		return false;
	}

	/**
	 * Get an item from the cache, or store the default value.
	 *
	 * @param  string  $key
	 * @param  \DateTime|int  $minutes
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public function remember($key, $minutes, Closure $callback)
	{
		// If the item exists in the cache we will just return this immediately
		// otherwise we will execute the given Closure and cache the result
		// of that execution for the given number of minutes in storage.
		if ( ! is_null($value = $this->get($key)))
		{
			return $value;
		}

		$this->put($key, $value = $callback(), $minutes);

		return $value;
	}

	/**
	 * Get an item from the cache, or store the default value forever.
	 *
	 * @param  string   $key
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public function sear($key, Closure $callback)
	{
		return $this->rememberForever($key, $callback);
	}

	/**
	 * Get an item from the cache, or store the default value forever.
	 *
	 * @param  string   $key
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public function rememberForever($key, Closure $callback)
	{
		// If the item exists in the cache we will just return this immediately
		// otherwise we will execute the given Closure and cache the result
		// of that execution for the given number of minutes. It's easy.
		if ( ! is_null($value = $this->get($key)))
		{
			return $value;
		}

		$this->forever($key, $value = $callback());

		return $value;
	}

	/**
	 * Get the default cache time.
	 *
	 * @return int
	 */
	public function getDefaultCacheTime()
	{
		return $this->default;
	}

	/**
	 * Set the default cache time in minutes.
	 *
	 * @param  int   $minutes
	 * @return void
	 */
	public function setDefaultCacheTime($minutes)
	{
		$this->default = $minutes;
	}

	/**
	 * Get the cache store implementation.
	 *
	 * @return \Illuminate\Contracts\Cache\Store
	 */
	public function getStore()
	{
		return $this->store;
	}

	/**
	 * Calculate the number of minutes with the given duration.
	 *
	 * @param  \DateTime|int  $duration
	 * @return int|null
	 */
	protected function getMinutes($duration)
	{
		if ($duration instanceof DateTime)
		{
			$fromNow = Carbon::instance($duration)->diffInMinutes();

			return $fromNow > 0 ? $fromNow : null;
		}

		return is_string($duration) ? (int) $duration : $duration;
	}

	/**
	 * Handle dynamic calls into macros or pass missing methods to the store.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->store, $method), $parameters);
	}

}
