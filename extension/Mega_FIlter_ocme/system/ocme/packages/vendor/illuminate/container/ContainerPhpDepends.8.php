<?php namespace Illuminate\Container;

use ArrayAccess;

abstract class ContainerPhpDepends implements ArrayAccess {

	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key):bool
	{
		return isset($this->bindings[$key]);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key):mixed
	{
		return $this->make($key);
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet($key, $value):void
	{
		// If the value is not a Closure, we will make it one. This simply gives
		// more "drop-in" replacement functionality for the Pimple which this
		// container's simplest functions are base modeled and built after.
		if ( ! $value instanceof Closure)
		{
			$value = function() use ($value)
			{
				return $value;
			};
		}

		$this->bind($key, $value);
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key):void
	{
		unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
	}
	
}