<?php namespace Ocme\Support;

use ArrayAccess;

abstract class VariablePhpDepends implements ArrayAccess {

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key):bool
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key):mixed
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value):void
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key):void
    {
        $this->set($key, null);
    }
	
}