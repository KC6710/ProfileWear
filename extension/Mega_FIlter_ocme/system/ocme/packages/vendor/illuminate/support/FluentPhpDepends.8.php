<?php namespace Illuminate\Support;

use ArrayAccess;
use JsonSerializable;

abstract class FluentPhpDepends implements ArrayAccess, JsonSerializable {

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	public function jsonSerialize():mixed
	{
		return $this->toArray();
	}

	/**
	 * Determine if the given offset exists.
	 *
	 * @param  string  $offset
	 * @return bool
	 */
	public function offsetExists($offset):bool
	{
		return isset($this->{$offset});
	}

	/**
	 * Get the value for a given offset.
	 *
	 * @param  string  $offset
	 * @return mixed
	 */
	public function offsetGet($offset):mixed
	{
		return $this->{$offset};
	}

	/**
	 * Set the value at the given offset.
	 *
	 * @param  string  $offset
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet($offset, $value):void
	{
		$this->{$offset} = $value;
	}

	/**
	 * Unset the value at the given offset.
	 *
	 * @param  string  $offset
	 * @return void
	 */
	public function offsetUnset($offset):void
	{
		unset($this->{$offset});
	}
	
}