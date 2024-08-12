<?php namespace Illuminate\Database\Eloquent;

use ArrayAccess;
use JsonSerializable;

abstract class ModelPhpDepends implements ArrayAccess, JsonSerializable {

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
	 * Determine if the given attribute exists.
	 *
	 * @param  mixed  $offset
	 * @return bool
	 */
	public function offsetExists($offset):bool
	{
		return isset($this->$offset);
	}

	/**
	 * Get the value for a given offset.
	 *
	 * @param  mixed  $offset
	 * @return mixed
	 */
	public function offsetGet($offset):mixed
	{
		return $this->$offset;
	}

	/**
	 * Set the value for a given offset.
	 *
	 * @param  mixed  $offset
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet($offset, $value):void
	{
		$this->$offset = $value;
	}

	/**
	 * Unset the value for a given offset.
	 *
	 * @param  mixed  $offset
	 * @return void
	 */
	public function offsetUnset($offset):void
	{
		unset($this->$offset);
	}
	
}