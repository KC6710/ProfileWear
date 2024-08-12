<?php namespace Illuminate\Support;

use Countable;
use JsonSerializable;

abstract class MessageBagPhpDepends implements Countable, JsonSerializable {

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Get the number of messages in the container.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->messages, COUNT_RECURSIVE) - count($this->messages);
	}
	
}