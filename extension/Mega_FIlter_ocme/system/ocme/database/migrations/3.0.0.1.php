<?php namespace Ocme\Database\Migrations;

use Illuminate\Database\Migrations\Migration,
	Illuminate\Database\Schema\Blueprint,
	Ocme\Support\Facades\Schema;

class Ocme3001 extends Migration {
	
	/* @var $routes array */
	protected $routes = array(
		'product/special' => 'Special',
	);
	
	/* @var $events array */
	protected $events = array(
		// options
		'ocme_mfp_option' => array(
			array( 'trigger' => 'admin/model/catalog/option/addOption/after', 'action' => 'extension/module/ocme_mfp/eventModelAfter' ),
			array( 'trigger' => 'admin/model/catalog/option/editOption/after', 'action' => 'extension/module/ocme_mfp/eventModelAfter' ),
			array( 'trigger' => 'admin/model/catalog/option/deleteOption/before', 'action' => 'extension/module/ocme_mfp/eventModelBefore' ),
		)
	);
	
	/**
	 * Run the migrations
	 * 
	 * @return void
	 */
	public function up() {		
		try {
			Schema::table('product_option', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'vdate') ) {
					$table->date('vdate')->nullable();

					$this->created_columns['product_option'][] = 'vdate';
				}
				
				if( ! Schema::hasColumn($table->getTable(), 'vtime') ) {
					$table->time('vtime')->nullable();

					$this->created_columns['product_option'][] = 'vtime';
				}
				
				if( ! Schema::hasColumn($table->getTable(), 'vdatetime') ) {
					$table->dateTime('vdatetime')->nullable();

					$this->created_columns['product_option'][] = 'vdatetime';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'product_option', $ex );
		}
		
		$this->upComplete()->addEvents()->addLayouts();
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {		
		$this->removeColumns(array(			
			'product_option' => array( 'vdate', 'vtime', 'vdatetime' ),
		));
		
		$this->removeEvents()->removeLayouts();
    }
}