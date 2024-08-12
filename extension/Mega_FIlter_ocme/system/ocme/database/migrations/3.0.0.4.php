<?php namespace Ocme\Database\Migrations;

use Illuminate\Database\Migrations\Migration;

class Ocme3004 extends Migration {
	
	/**
	 * Run the migrations
	 * 
	 * @return void
	 */
	public function up() {
		$this->addDbData()->upComplete();
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
}