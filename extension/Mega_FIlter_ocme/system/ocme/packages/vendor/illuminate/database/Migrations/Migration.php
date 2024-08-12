<?php namespace Illuminate\Database\Migrations;

use Ocme\Model\Layout,
	Ocme\Model\LayoutRoute,
	Ocme\Model\Store,
	Ocme\Model\Module,
	Ocme\Model\Event,
	Ocme\Model\Setting,
	Ocme\Support\Facades\Schema,
	Illuminate\Database\Schema\Blueprint;

abstract class Migration {

	/**
	 * The name of the database connection to use.
	 *
	 * @var string
	 */
	protected $connection;
	
	/* @var $created_tables array */
	protected $created_tables = array();
		
	/* @var $created_columns array */
	protected $created_columns = array();
	
	/* @var $errors array */
	protected $errors = array();
	
	/* @var $routes array */
	protected $routes = array();
	
	/* @var $events array */
	protected $events = array();
	
	/**
	 * @return $this
	 */
	protected function addEvents() {
		foreach( $this->events as $code => $events ) {
			foreach( $events as $event ) {
				/* @var $action string */
				$action = ocme_extension_path( ocme()->arr()->get( $event, 'action' ) );
				
				Event::create(array(
					'code' => $code,
					'description' => '',
					'trigger' => ocme_extension_path( ocme()->arr()->get( $event, 'trigger' ) ),
					'action' => $action,
					'status' => ocme()->arr()->get( $event, 'status', 1 ),
					'sort_order' => ocme()->arr()->get( $event, 'sort_order', 0 ),
				));
			}
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function removeEvents() {
		if( $this->events ) {
			Event::whereIn('code', array_keys( $this->events ))->delete();
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function upComplete() {
		/* @var $db_changes array */
		$db_changes = array();
		
		/* @var $ocme_mfp_db_changes Setting */
		if( null != ( $ocme_mfp_db_changes = Setting::where('code', 'ocme_mfp_db_changes')->where('key', 'ocme_mfp_db_changes')->first() ) ) {
			$db_changes = (array) $ocme_mfp_db_changes->value;
		}
		
		$db_changes[$this->version()] = array(
			'tables' => $this->created_tables,
			'columns' => $this->created_columns,
			'errors' => $this->errors,
		);
		
		Setting::firstOrNew(array(
			'code' => 'ocme_mfp_db_changes',
			'key' => 'ocme_mfp_db_changes',
		))->fill(array(
			'value' => $db_changes,
		))->save();
		
		if( $this->errors ) {
			ocme()->model('setting/setting')->editSetting('ocme_mfp_installation_error', array(
				'ocme_mfp_installation_error' => date('Y-m-d H:i:s'),
			));
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function addTableError( $table, \Exception $ex ) {
		$this->errors[] = 'Table: ' . $table . ' | ' . $ex->getMessage();
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function addLayouts() {		
		/* @var $route string */
		foreach( $this->routes as $route => $name ) {
			if( ! LayoutRoute::where('route', $route)->first() ) {
				/* @var $layout Layout */
				$layout = Layout::create(array(
					'name' => $name,
				));

				foreach( Store::allStores() as $store ) {
					LayoutRoute::create(array(
						'layout_id' => $layout->layout_id,
						'store_id' => $store->store_id,
						'route' => $route,
					));
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function removeLayouts() {
		/* @var $layout_route LayoutRoute */
		foreach( LayoutRoute::whereIn('route', array_keys( $this->routes ))->get() as $layout_route ) {
			$layout_route->layout->delete();
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function removeModules() {
		/* @var $module Module */
		foreach( Module::where('code', 'LIKE', '%ocme_mfp_filter%')->get() as $module ) {
			$module->delete();
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function addDbData() {		
		/* @var $files array */
		if( null != ( $files = glob( ( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'Mega_FIlter_ocme/system/' : DIR_SYSTEM ) . 'ocme/database/migrations/data/*.json' ) ) ) {
			/* @var $tables array */
			$tables = array(
				'ocme_variable' => array( 'store_id', 'type', 'name' ),
			);
			
			/* @var $version string */
			$version = $this->version();
			
			/* @var $file string */
			foreach( $files as $file ) {				
				if( is_readable( $file ) ) {
					/* @var $content string */
					if( null != ( $content = file_get_contents( $file ) ) ) {
						/* @var $data array */
						if( null != ( $data = json_decode( $content, true ) ) ) {
							if( ocme()->arr()->get( $data, 'version' ) != $version ) {
								continue;
							}
							
							/* @var $oc_from string */
							$oc_from = ocme()->arr()->get( $data, 'oc_from', '*' );
							
							/* @var $oc_to string */
							$oc_to = ocme()->arr()->get( $data, 'oc_to', '*' );

							if( $oc_from == '*' || version_compare( VERSION, $oc_from, '>=' ) ) {
								if( $oc_to == '*' || version_compare( VERSION, $oc_to, '<' ) ) {
									/* @var $table string */
									$table = ocme()->arr()->get( $data, 'table' );
									
									/* @var $class string */
									$class = '\\Ocme\\Model\\' . ocme()->str()->studly( $table );

									/* @var $row array */
									foreach( ocme()->arr()->get( $data, 'rows', array() ) as $row ) {
										if( in_array( $table, $this->created_tables ) ) {
											$class::create($row);
										} else if( isset( $tables[$table] ) ){
											/* @var $query \Illuminate\Database\Eloquent\Builder */
											$query = $class::query();
											
											/* @var $column string */
											foreach( $tables[$table] as $column ) {
												/* @var $value mixed */
												if( null === ( $value = ocme()->arr()->get( $row, $column ) ) ) {
													$query->whereNull( $column );
												} else {
													$query->where( $column, $value );
												}
											}
											
											if( ! $query->first() ) {
												$class::create($row);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $this;
	}
	
	protected function version() {
		return str_replace( 'Ocme', '', basename( str_replace('\\', '/', get_class( $this ) ) ) );
	}
	
	/**
	 * @return $this
	 */
	protected function removeTables( array $tables ) {
		/* @var $db_changes array */
		$db_changes = (array) ocme()->ocRegistry()->get('config')->get('ocme_mfp_db_changes');
		
		/* @var $created_tables array */
		$created_tables = ocme()->arr()->get( $db_changes, $this->version() . '.tables', array() );
		
		/* @var $table string */
		foreach( $tables as $table ) {
			if( in_array( $table, $created_tables ) ) {
				Schema::dropIfExists( $table );
			}
		}
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function removeColumns( array $columns ) {
		/* @var $db_changes array */
		$db_changes = (array) ocme()->ocRegistry()->get('config')->get('ocme_mfp_db_changes');
		
		/* @var $created_columns array */
		$created_columns = ocme()->arr()->get( $db_changes, $this->version() . '.columns', array() );		
		
		foreach( $columns as $table_name => $column_names ) {
			if( isset( $created_columns[$table_name] ) ) {
				Schema::table($table_name, function( Blueprint $table ) use( $table_name, $column_names, $created_columns ){
					foreach( $column_names as $column ) {
						if( in_array( $column, $created_columns[$table_name] ) && Schema::hasColumn( $table->getTable(), $column ) ) {
							$table->dropColumn( $column );
						}
					}
				});
			}
		}
		
		return $this;
	}

	/**
	 * Get the migration connection name.
	 *
	 * @return string
	 */
	public function getConnection()
	{
		return $this->connection;
	}

}
