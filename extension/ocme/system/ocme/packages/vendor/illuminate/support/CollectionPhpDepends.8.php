<?php namespace Illuminate\Support;

use Countable;
use ArrayAccess;
use ArrayIterator;
use Traversable;
use JsonSerializable;
use IteratorAggregate;

abstract class CollectionPhpDepends implements ArrayAccess, JsonSerializable, Countable, IteratorAggregate {

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator():Traversable
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count():int
	{
		return count($this->items);
	}

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
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists($key):bool
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	public function offsetGet($key):mixed
	{
		return $this->items[$key];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet($key, $value):void
	{
		if (is_null($key))
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$key] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key):void
	{
		unset($this->items[$key]);
	}
	
}