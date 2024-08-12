<?php namespace Illuminate\Database\Query\Processors;

class MySqlProcessor extends Processor {

	/**
	 * Process the results of a column listing query.
	 *
	 * @param  array  $results
	 * @return array
	 */
	public function processColumnListing($results)
	{
		$mapping = function($r)
		{
			$r = (array) $r;
			$r = (object) array_combine( array_map(function( $k ){
				return strtolower( $k );
			}, array_keys( $r )), array_values( $r ) );

			return $r->column_name;
		};

		return array_map($mapping, $results);
	}

}
