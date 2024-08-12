<?php

namespace Illuminate\Pagination;

use ArrayIterator;
use Illuminate\Contracts\Support\Htmlable;

abstract class AbstractPaginatorPhpDepends {

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
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items->all());
    }

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * Determine if the given item exists.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->items->has($key);
    }

    /**
     * Get the item at the given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items->get($key);
    }

    /**
     * Set the item at the given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->items->put($key, $value);
    }

    /**
     * Unset the item at the given key.
     *
     * @param  mixed  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->items->forget($key);
    }
	
}