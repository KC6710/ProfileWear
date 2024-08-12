<?php namespace Ocme\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Ocme\Model\OcmeVariable;
use Ocme\Model\Event;
use Ocme\Support\Facades\Schema;

class Ocme3010 extends Migration {
	
	/* @var $events array */
	protected $events = array();
	
	/**
	 * Run the migrations
	 * 
	 * @return void
	 */
	public function up() {
		$this->addDbData();

		Event::where('code', 'ocme_mfp_attribute')
			->where('trigger', ocme_extension_path( 'admin/view/catalog/attribute_form/after' ))
			->update(array(
				'trigger' => ocme_extension_path( 'admin/view/catalog/attribute_form/before' ),
				'action' => ocme_extension_path( 'extension/module/ocme_mfp/eventViewBefore' ),
			));

		ocme()->db()->connection()->select('ALTER TABLE `' . DB_PREFIX . 'ocme_variable` CHANGE `value` `value` LONGTEXT NULL');

		OcmeVariable::where('value', '')->update(array(
			'value' => null,
		));
		
		/* @var $ocme_variable OcmeVariable */
		foreach( OcmeVariable::where('type', 'filter')
			->whereIn('name', array(
				'products_wrapper.id',
				'products_wrapper.extra_class',
				'products_wrapper.insert',
			))
			->get() as $ocme_variable
		) {			
			$ocme_variable->fill(array(
				'type' => 'config_global',
			))->save();
		}
		
		/* @var $ocme_variable OcmeVariable */
		foreach( OcmeVariable::where('type', 'filter_global')
			->whereIn('name', array(
				'configuration.javascript.main_selector',
				'configuration.javascript.header_selector',
				'configuration.javascript.pagination_selector',
				'configuration.javascript.first_product_selector',
				'configuration.javascript.breadcrumb_selector',
			))
			->get() as $ocme_variable
		) {			
			$ocme_variable->fill(array(
				'type' => 'config_global',
			))->save();
		}
		
		/* @var $ocme_variable OcmeVariable */
		foreach( OcmeVariable::where('type', 'filter_global_js_hook')->get() as $ocme_variable ) {
			OcmeVariable::firstOrNew(array(
				'type' => 'config_global_js_hook',
				'name' => $ocme_variable->name,
			))->fill(array(
				'value' => $ocme_variable->value,
			))->save();
			
			$ocme_variable->fill(array(
				'value' => '',
			))->save();
		}
					
		$this->upComplete()->addEvents();
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
		$this->removeEvents();
	}
}