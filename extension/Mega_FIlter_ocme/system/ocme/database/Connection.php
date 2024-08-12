<?php namespace Ocme\Database;

use Illuminate\Database\Capsule\Manager,
	Illuminate\Events\Dispatcher,
	Illuminate\Container\Container;

/**
 * @method \Illuminate\Database\Connection connection(string $connection = null) Get a connection instance from the global manager.
 */

class Connection {
	
	/**
	 * @var Manager
	 */
	protected $manager;
	
	public function __construct() {
		$this->manager = new Manager;
	}

	public function connect() {
		$this->manager->addConnection(array(
			'driver' => 'mysql',
			'host' => DB_HOSTNAME,
			'database' => DB_DATABASE,
			'username' => DB_USERNAME,
			'password' => DB_PASSWORD,
			'port' => DB_PORT,
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => DB_PREFIX
		));

		$this->manager->setEventDispatcher(new Dispatcher(new Container()));
		$this->manager->setAsGlobal();
		$this->manager->bootEloquent();
	}
	
	public function __call( $name, $arguments ) {
		return call_user_func_array( array( $this->manager, $name ), $arguments );
	}
	
	/**
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function newQuery() {
		return new \Illuminate\Database\Query\Builder( $this->connection(), $this->connection()->getQueryGrammar(), $this->connection()->getPostProcessor() );
	}

	/**
	 * Get a new raw query expression.
	 *
	 * @param  mixed  $value
	 * @return \Illuminate\Database\Query\Expression
	 */
	public function raw( $value ) {
		return $this->connection()->raw( $value );
	}
	
	/**
	 * Convert query to raw sql
	 * 
	 * @param $query
	 * @return string
	 */
	public function queryToRawSql( $query ) {
		$sql = $query->toSql();
		$bindings = $query->getBindings();

		while( $bindings ) {
			$pos = strpos($sql, '?');
			$val = array_shift( $bindings );

			if( $pos !== false ) {
				$sql = substr_replace($sql, "'".$val."'", $pos, 1);
			}
		}
		
		return $sql;
	}
	
}