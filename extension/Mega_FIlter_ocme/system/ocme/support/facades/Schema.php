<?php namespace Ocme\Support\Facades;

/**
 * @see \Illuminate\Database\Schema\Builder
 */
class Schema extends \Illuminate\Support\Facades\Schema {

	/**
	 * Get a schema builder instance for a connection.
	 *
	 * @param  string  $name
	 * @return \Illuminate\Database\Schema\Builder
	 */
	public static function connection($name) {
		return ocme()->db()->connection($name)->getSchemaBuilder();
	}

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {
		return ocme()->db()->connection()->getSchemaBuilder();
	}

}
