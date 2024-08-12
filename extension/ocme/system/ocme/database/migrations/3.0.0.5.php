<?php namespace Ocme\Database\Migrations;

use Illuminate\Database\Migrations\Migration,
	Ocme\Model\OcmeFilterCondition;

class Ocme3005 extends Migration {
	
	/* @var $events array */
	protected $events = array(
		// attribute groups
		'ocme_mfp_attribute' => array(
			array( 'trigger' => 'admin/model/catalog/attribute_group/deleteAttributeGroup/before', 'action' => 'extension/module/ocme_mfp/eventModelBefore' ),
		),
		// filter
		'ocme_mfp_filter' => array(
			array( 'trigger' => 'admin/model/catalog/filter/deleteFilter/before', 'action' => 'extension/module/ocme_mfp/eventModelBefore' ),
		)
	);
	
	/**
	 * Run the migrations
	 * 
	 * @return void
	 */
	public function up() {
		// fix not existing relationships
		foreach( OcmeFilterCondition::query()
			->addFromAlias('`ofc`')
			->where(function($q){
				$q->where(function($q){
					$q->where('`ofc`.condition_type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE)->whereNotExists(function($q){
						$q->select( ocme()->db()->raw(1) )->from('attribute AS `a`')->whereColumn('`ofc`.record_id', '`a`.attribute_id');
					})->orWhere('`ofc`.condition_type', OcmeFilterCondition::CONDITION_TYPE_ATTRIBUTE_GROUP)->whereNotExists(function($q){
						$q->select( ocme()->db()->raw(1) )->from('attribute_group AS `ag`')->whereColumn('`ofc`.record_id', '`ag`.attribute_group_id');
					})->orWhere('`ofc`.condition_type', OcmeFilterCondition::CONDITION_TYPE_OPTION)->whereNotExists(function($q){
						$q->select( ocme()->db()->raw(1) )->from('option AS `o`')->whereColumn('`ofc`.record_id', '`o`.option_id');
					})->orWhere('`ofc`.condition_type', OcmeFilterCondition::CONDITION_TYPE_FILTER_GROUP)->whereNotExists(function($q){
						$q->select( ocme()->db()->raw(1) )->from('filter_group AS `fg`')->whereColumn('`ofc`.record_id', '`fg`.filter_group_id');
					});
				});
			})->get() as $ocme_filter_condition 
		) {
			$ocme_filter_condition->delete();	
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