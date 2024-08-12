<?php namespace Ocme\Database\Migrations;

use Illuminate\Database\Migrations\Migration,
	Illuminate\Database\Schema\Blueprint,
	Ocme\Support\Facades\Schema;

class Ocme3000 extends Migration {
	
	/* @var $routes array */
	protected $routes = array(
		'browse/catalog' => 'MFP Browse Catalog',
		'product/manufacturer/info' => 'Manufacturer catalog',
	);
	
	/* @var $events array */
	protected $events = array(
		// framework
		'ocme_mfp' => array(
			// startup
			array( 'trigger' => 'admin/controller/*/before', 'action' => 'extension/module/ocme_mfp/eventStartup', 'sort_order' => -1000 ),
			array( 'trigger' => 'catalog/controller/*/before', 'action' => 'extension/module/ocme_mfp/eventStartup', 'sort_order' => -1000 ),
			array( 'trigger' => 'catalog/view/*/before', 'action' => 'extension/module/ocme_mfp/eventViewBefore', 'sort_order' => -1000 ),
		),
		// filter
		'ocme_mfp_filter' => array(
			// add product
			array( 'trigger' => 'admin/controller/catalog/product/add/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),
			array( 'trigger' => 'admin/model/catalog/product/addProduct/after', 'action' => 'extension/module/ocme_mfp/eventModelAfter' ),

			// edit product
			array( 'trigger' => 'admin/controller/catalog/product/edit/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),
			array( 'trigger' => 'admin/model/catalog/product/editProduct/after', 'action' => 'extension/module/ocme_mfp/eventModelAfter' ),

			// copy product
			array( 'trigger' => 'admin/model/catalog/product/copyProduct/after', 'action' => 'extension/module/ocme_mfp/eventModelAfter' ),

			// delete product
			array( 'trigger' => 'admin/model/catalog/product/deleteProduct/before', 'action' => 'extension/module/ocme_mfp/eventModelBefore' ),

			// attribute
			array( 'trigger' => 'admin/controller/catalog/attribute/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),
			array( 'trigger' => 'admin/controller/catalog/attribute_group/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),
			array( 'trigger' => 'admin/controller/catalog/attribute/add/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),
			array( 'trigger' => 'admin/controller/catalog/attribute/edit/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),

			// add/edit attribute value
			array( 'trigger' => 'admin/controller/extension/module/ocme_mfp_attribute/value_add/before', 'action' => 'extension/module/ocme_mfp_filter/eventActionBefore' ),
			array( 'trigger' => 'admin/controller/extension/module/ocme_mfp_attribute/value_edit/before', 'action' => 'extension/module/ocme_mfp_filter/eventActionBefore' ),

			// form
			array( 'trigger' => 'admin/view/catalog/product_form/after', 'action' => 'extension/module/ocme_mfp/eventViewAfter' ),

			// header
			array( 'trigger' => 'admin/controller/common/header/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),

			// footer
			array( 'trigger' => 'admin/controller/common/footer/after', 'action' => 'extension/module/ocme_mfp/eventActionAfter' ),

			// get products
			array( 'trigger' => 'catalog/model/catalog/product/getProducts/before', 'action' => 'extension/module/ocme_mfp_filter/getProducts' ),
			array( 'trigger' => 'catalog/model/catalog/product/getTotalProducts/before', 'action' => 'extension/module/ocme_mfp_filter/getTotalProducts' ),
			array( 'trigger' => 'catalog/model/catalog/product/getProductSpecials/before', 'action' => 'extension/module/ocme_mfp_filter/getProducts' ),
			array( 'trigger' => 'catalog/model/catalog/product/getTotalProductSpecials/before', 'action' => 'extension/module/ocme_mfp_filter/getTotalProducts' ),

			// get product
			array( 'trigger' => 'catalog/model/catalog/product/getProduct/after', 'action' => 'extension/module/ocme_mfp_filter/eventGetProduct', 'status' => '0' ),

			// support for J3
			array( 'trigger' => 'catalog/model/journal3/filter/getProducts/before', 'action' => 'extension/module/ocme_mfp_filter/getProducts' ),
			array( 'trigger' => 'catalog/model/journal3/filter/getTotalProducts/before', 'action' => 'extension/module/ocme_mfp_filter/getTotalProducts' ),
		),
		// attributes
		'ocme_mfp_attribute' => array(
			array( 'trigger' => 'admin/controller/extension/module/ocme_mfp/attribute_value_edit/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),
			array( 'trigger' => 'admin/controller/extension/module/ocme_mfp/attribute_value_add/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),

			array( 'trigger' => 'admin/model/catalog/attribute/addAttribute/after', 'action' => 'extension/module/ocme_mfp/attribute_eventModelAfter' ),
			array( 'trigger' => 'admin/model/catalog/attribute/editAttribute/after', 'action' => 'extension/module/ocme_mfp/attribute_eventModelAfter' ),				
			array( 'trigger' => 'admin/model/catalog/attribute/deleteAttribute/before', 'action' => 'extension/module/ocme_mfp/attribute_eventModelBefore' ),

			array( 'trigger' => 'admin/model/catalog/attribute_group/getAttributeGroups/before', 'action' => 'extension/module/ocme_mfp/attribute_eventModelBefore' ),
			array( 'trigger' => 'admin/model/catalog/attribute_group/getTotalAttributeGroups/before', 'action' => 'extension/module/ocme_mfp/attribute_eventModelBefore' ),
			array( 'trigger' => 'admin/model/catalog/attribute/getTotalAttributes/before', 'action' => 'extension/module/ocme_mfp/attribute_eventModelBefore' ),
			array( 'trigger' => 'admin/model/catalog/attribute/getAttributes/before', 'action' => 'extension/module/ocme_mfp/attribute_eventModelBefore' ),

			array( 'trigger' => 'admin/controller/extension/module/ocme_mfp/attribute_value/before', 'action' => 'extension/module/ocme_mfp/eventActionBefore' ),

			array( 'trigger' => 'admin/view/common/column_left/before', 'action' => 'extension/module/ocme_mfp/eventViewBefore' ),
			array( 'trigger' => 'admin/view/catalog/attribute_form/after', 'action' => 'extension/module/ocme_mfp/eventViewAfter' ),
			array( 'trigger' => 'admin/view/catalog/attribute_list/after', 'action' => 'extension/module/ocme_mfp/eventViewAfter' ),
			array( 'trigger' => 'admin/view/catalog/attribute_list/before', 'action' => 'extension/module/ocme_mfp/eventViewBefore' ),
			array( 'trigger' => 'admin/view/catalog/attribute_group_list/after', 'action' => 'extension/module/ocme_mfp/eventViewAfter' ),
			array( 'trigger' => 'admin/view/catalog/attribute_group_list/before', 'action' => 'extension/module/ocme_mfp/eventViewBefore' ),
		)
	);
	
	/**
	 * Run the migrations
	 * 
	 * @return void
	 */
	public function up() {
		/** @var string $engine */
		$engine = 'MyISAM';

		if (version_compare(VERSION, '4', '>=')) {
			$engine = 'InnoDB';
		}

		/**
		 * Ocme filter condition
		 */
		if( ! Schema::hasTable('ocme_filter_condition') ) {
			try {
				Schema::create('ocme_filter_condition', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('id', true);
					$table->integer('module_id');
					$table->enum('condition_type', array('base_attribute', 'attribute', 'option', 'filter_group', 'feature', 'attribute_group', 'property'));
					$table->string('name', 50)->nullable();
					$table->integer('record_id')->nullable();
					$table->string('status', 20);
					$table->string('type', 20)->nullable();
					$table->mediumInteger('sort_order')->nullable();
					$table->text('setting')->nullable();

					$table->index('module_id', 'index_m');
				});

				$this->created_tables[] = 'ocme_filter_condition';
			} catch (\Exception $ex) {
				$this->addTableError( 'ocme_filter_condition', $ex );
			}
		}
		
		/**
		 * Ocme filter grid
		 */
		if( ! Schema::hasTable('ocme_filter_grid') ) {
			try {
				Schema::create('ocme_filter_grid', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('id', true, true);
					$table->integer('module_id');
					$table->integer('parent_id', false, true)->nullable();
					$table->enum('type', array('row', 'column'));
					$table->mediumInteger('sort_order');
					$table->text('settings')->nullable();

					$table->index('module_id', 'index_m');
					$table->index('parent_id', 'index_p');
				});

				$this->created_tables[] = 'ocme_filter_grid';
			} catch (\Exception $ex) {
				$this->addTableError( 'ocme_filter_grid', $ex );
			}
		}
		
		/**
		 * Ocme filter grid condition
		 */
		if( ! Schema::hasTable('ocme_filter_grid_condition') ) {
			try {
				Schema::create('ocme_filter_grid_condition', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('id', true, true);
					$table->integer('ocme_filter_grid_id', false, true);
					$table->integer('ocme_filter_condition_id', false, true);
					$table->integer('vid')->nullable();
					$table->enum('vtype', array('base_attribute', 'attribute', 'option', 'filter_group', 'feature', 'property'));
					$table->string('vname', 50)->nullable();
					$table->mediumInteger('sort_order');

					$table->index('ocme_filter_grid_id', 'index_ofgi');
					$table->index('ocme_filter_condition_id', 'index_ofci');
				});

				$this->created_tables[] = 'ocme_filter_grid_condition';
			} catch (\Exception $ex) {
				$this->addTableError( 'ocme_filter_grid_condition', $ex );
			}
		}
		
		/**
		 * Ocme filter property
		 */
		if( ! Schema::hasTable('ocme_filter_property') ) {
			try {
				Schema::create('ocme_filter_property', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('id', true, true);
					$table->integer('attribute_id')->nullable();
					$table->integer('option_id')->nullable();
					$table->integer('filter_group_id')->nullable();

					$table->unique('attribute_id', 'index_a');
					$table->unique('option_id', 'index_o');
					$table->unique('filter_group_id', 'index_f');
				});

				$this->created_tables[] = 'ocme_filter_property';
			} catch (\Exception $ex) {
				$this->addTableError( 'ocme_filter_property', $ex );
			}
		}
		
		/**
		 * Ocme filter property value
		 */
		if( ! Schema::hasTable('ocme_filter_property_value') ) {
			try {
				Schema::create('ocme_filter_property_value', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('id', true, true);
					$table->integer('ocme_filter_property_id', false, true);
					$table->integer('attribute_id')->nullable();
					$table->integer('attribute_value_id')->nullable();
					$table->integer('option_id')->nullable();
					$table->integer('option_value_id')->nullable();
					$table->integer('filter_group_id')->nullable();
					$table->integer('filter_id')->nullable();

					$table->index('ocme_filter_property_id', 'index_o');
					$table->unique(array('attribute_id', 'attribute_value_id'), 'index_aa');
					$table->unique(array('option_id', 'option_value_id'), 'index_oo');
					$table->unique(array('filter_group_id', 'filter_id'), 'index_ff');
				});

				$this->created_tables[] = 'ocme_filter_property_value';
			} catch (\Exception $ex) {
				$this->addTableError( 'ocme_filter_property_value', $ex );
			}
		}
		
		/**
		 * Ocme filter property product
		 */
		if( ! Schema::hasTable('ocme_filter_property_value_to_product') ) {
			try {
				Schema::create('ocme_filter_property_value_to_product', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('ocme_filter_property_id', false, true);
					$table->integer('ocme_filter_property_value_id', false, true);
					$table->integer('product_id');
					$table->integer('attribute_id')->nullable();
					$table->integer('attribute_value_id')->nullable();
					$table->integer('option_id')->nullable();
					$table->integer('option_value_id')->nullable();
					$table->integer('filter_group_id')->nullable();
					$table->integer('filter_id')->nullable();

					$table->unique(array('ocme_filter_property_id', 'ocme_filter_property_value_id', 'product_id'), 'index_oop');
					$table->unique(array('product_id', 'attribute_id', 'attribute_value_id'), 'index_paa');
					$table->unique(array('product_id', 'option_id', 'option_value_id'), 'index_poo');
					$table->unique(array('product_id', 'filter_group_id', 'filter_id'), 'index_pff');
				});

				$this->created_tables[] = 'ocme_filter_property_value_to_product';
			} catch (\Exception $ex) {
				$this->addTableError( 'ocme_filter_property_value_to_product', $ex );
			}
		}
		
		/**
		 * Ocme variable
		 */
		if( ! Schema::hasTable('ocme_variable') ) {
			try {
				Schema::create('ocme_variable', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('id', true);
					$table->integer('store_id')->nullable();
					$table->string('type', 64);
					$table->string('name', 128);
					$table->longtext('value')->nullable();
					$table->tinyInteger('serialized');

					$table->index('store_id', 'index_s');
				});

				$this->created_tables[] = 'ocme_variable';
			} catch (\Exception $ex) {
				$this->addTableError( 'ocme_variable', $ex );
			}
		}		
		
		/**
		 * Filter Group
		 */
		try {
			Schema::table('filter_group', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'type') ) {
					$table->string('type', 20)->nullable();

					$this->created_columns['filter_group'][] = 'type';
				}

				if( ! Schema::hasColumn($table->getTable(), 'store_ids') ) {
					$table->text('store_ids')->nullable();

					$this->created_columns['filter_group'][] = 'store_ids';
				}

				if( ! Schema::hasColumn($table->getTable(), 'with_image') ) {
					$table->tinyInteger('with_image')->default('0');

					$this->created_columns['filter_group'][] = 'with_image';
				}

				if( ! Schema::hasColumn($table->getTable(), 'with_color') ) {
					$table->tinyInteger('with_color')->default('0');

					$this->created_columns['filter_group'][] = 'with_color';
				}

				if( ! Schema::hasColumn($table->getTable(), 'values_type') ) {
					$table->enum('values_type', array('string', 'integer', 'float'))->default('string');

					$this->created_columns['filter_group'][] = 'values_type';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'filter_group', $ex );
		}
		
		if( ! Schema::hasTable('filter_group_to_store') ) {
			try {
				Schema::create('filter_group_to_store', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('filter_group_id');
					$table->integer('store_id');

					$table->unique(array('filter_group_id', 'store_id'), 'index_fs');
				});

				$this->created_tables[] = 'filter_group_to_store';
			} catch (\Exception $ex) {
				$this->addTableError( 'filter_group_to_store', $ex );
			}
		}
		
		try {
			Schema::table('filter_group_description', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'seo_url') ) {
					$table->string('seo_url', 255)->nullable();

					$this->created_columns['filter_group_description'][] = 'seo_url';
				}

				if( ! Schema::hasColumn($table->getTable(), 'tooltip') ) {
					$table->string('tooltip', 255)->nullable();

					$this->created_columns['filter_group_description'][] = 'tooltip';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'filter_group_description', $ex );
		}
		
		/**
		 * Filter
		 */
		try {
			Schema::table('filter', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'image') ) {
					$table->string('image', 255)->nullable();

					$this->created_columns['filter'][] = 'image';
				}

				if( ! Schema::hasColumn($table->getTable(), 'color') ) {
					$table->string('color', 25)->nullable();

					$this->created_columns['filter'][] = 'color';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'filter', $ex );
		}
		
		try {
			Schema::table('filter_description', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'seo_url') ) {
					$table->string('seo_url', 255)->nullable();

					$this->created_columns['filter_description'][] = 'seo_url';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'filter_description', $ex );
		}
		
		/**
		 * Attribute
		 */
		try {
			Schema::table('attribute', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'store_ids') ) {
					$table->text('store_ids')->nullable();

					$this->created_columns['attribute'][] = 'store_ids';
				}

				if( ! Schema::hasColumn($table->getTable(), 'with_image') ) {
					$table->tinyInteger('with_image')->default('0');

					$this->created_columns['attribute'][] = 'with_image';
				}

				if( ! Schema::hasColumn($table->getTable(), 'with_color') ) {
					$table->tinyInteger('with_color')->default('0');

					$this->created_columns['attribute'][] = 'with_color';
				}

				if( ! Schema::hasColumn($table->getTable(), 'displayed_values_separator') ) {
					$table->string('displayed_values_separator', 10)->nullable();

					$this->created_columns['attribute'][] = 'displayed_values_separator';
				}

				if( ! Schema::hasColumn($table->getTable(), 'values_type') ) {
					$table->enum('values_type', array('string', 'integer', 'float'))->default('string');

					$this->created_columns['attribute'][] = 'values_type';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'attribute', $ex );
		}
		
		try {
			Schema::table('attribute_description', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'seo_url') ) {
					$table->string('seo_url', 255)->nullable();

					$this->created_columns['attribute_description'][] = 'seo_url';
				}

				if( ! Schema::hasColumn($table->getTable(), 'tooltip') ) {
					$table->string('tooltip', 255)->nullable();

					$this->created_columns['attribute_description'][] = 'tooltip';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'attribute_description', $ex );
		}
		
		if( ! Schema::hasTable('attribute_to_store') ) {
			try {
				Schema::create('attribute_to_store', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('attribute_id');
					$table->integer('store_id');

					$table->unique(array('attribute_id', 'store_id'), 'index_fs');
				});

				$this->created_tables[] = 'attribute_to_store';
			} catch (\Exception $ex) {
				$this->addTableError( 'attribute_to_store', $ex );
			}
		}
		
		if( ! Schema::hasTable('attribute_value') ) {
			try {
				Schema::create('attribute_value', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('attribute_value_id', true, true);
					$table->integer('attribute_id');
					$table->string('image', 255)->nullable();
					$table->mediumInteger('sort_order');
					$table->string('color', 25)->nullable();
					$table->bigInteger('vinteger')->nullable();
					$table->decimal('vfloat', 15, 8)->nullable();

					$table->index('attribute_id', 'index_a');
				});

				$this->created_tables[] = 'attribute_value';
			} catch (\Exception $ex) {
				$this->addTableError( 'attribute_value', $ex );
			}
		}
		
		if( ! Schema::hasTable('attribute_value_description') ) {
			try {
				Schema::create('attribute_value_description', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('attribute_value_id', false, true);
					$table->integer('language_id');
					$table->string('name', 255);
					$table->string('seo_url', 255)->nullable();

					$table->index('language_id', 'index_l');
				});

				$this->created_tables[] = 'attribute_value_description';
			} catch (\Exception $ex) {
				$this->addTableError( 'attribute_value_description', $ex );
			}
		}
		
		try {
			Schema::table('product_attribute', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'sort_order') ) {
					$table->mediumInteger('sort_order')->default('0');

					$this->created_columns['product_attribute'][] = 'sort_order';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'product_attribute', $ex );
		}
		
		if( ! Schema::hasTable('product_attribute_value') ) {
			try {
				Schema::create('product_attribute_value', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('product_attribute_value_id', true, true);
					$table->integer('product_id');
					$table->integer('attribute_id');
					$table->integer('attribute_value_id');
					$table->mediumInteger('sort_order')->default('0');

					$table->index('product_id', 'index_pi');
					$table->index('attribute_id', 'index_ai');
					$table->index('attribute_value_id', 'index_avi');
				});

				$this->created_tables[] = 'product_attribute_value';
			} catch (\Exception $ex) {
				$this->addTableError( 'product_attribute_value', $ex );
			}
		}
		
		/**
		 * Option
		 */
		try {
			Schema::table('option', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'store_ids') ) {
					$table->text('store_ids')->nullable();

					$this->created_columns['option'][] = 'store_ids';
				}

				if( ! Schema::hasColumn($table->getTable(), 'with_image') ) {
					$table->tinyInteger('with_image')->default('0');

					$this->created_columns['option'][] = 'with_image';
				}

				if( ! Schema::hasColumn($table->getTable(), 'with_color') ) {
					$table->tinyInteger('with_color')->default('0');

					$this->created_columns['option'][] = 'with_color';
				}

				if( ! Schema::hasColumn($table->getTable(), 'values_type') ) {
					$table->enum('values_type', array('string', 'integer', 'float'))->default('string');

					$this->created_columns['option'][] = 'values_type';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'option', $ex );
		}
		
		try {
			Schema::table('option_description', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'seo_url') ) {
					$table->string('seo_url', 255)->nullable();

					$this->created_columns['option_description'][] = 'seo_url';
				}

				if( ! Schema::hasColumn($table->getTable(), 'tooltip') ) {
					$table->string('tooltip', 255)->nullable();

					$this->created_columns['option_description'][] = 'tooltip';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'option_description', $ex );
		}
		
		if( ! Schema::hasTable('option_to_store') ) {
			try {
				Schema::create('option_to_store', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('option_id');
					$table->integer('store_id');

					$table->unique(array('option_id', 'store_id'), 'index_os');
				});

				$this->created_tables[] = 'option_to_store';
			} catch (\Exception $ex) {
				$this->addTableError( 'option_to_store', $ex );
			}
		}
		
		try {
			Schema::table('option_value', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'color') ) {
					$table->string('color', 25)->nullable();

					$this->created_columns['option_value'][] = 'color';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'option_value', $ex );
		}
		
		try {
			Schema::table('option_value_description', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'seo_url') ) {
					$table->string('seo_url', 255)->nullable();

					$this->created_columns['option_value_description'][] = 'seo_url';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'option_value_description', $ex );
		}
		
		/**
		 * Product
		 */
		try {
			Schema::table('product', function( Blueprint $table ) {
				if( ! Schema::hasColumn($table->getTable(), 'ocme_filter_indexed_at') ) {
					$table->datetime('ocme_filter_indexed_at')->nullable();

					$this->created_columns['product'][] = 'ocme_filter_indexed_at';
				}
			});
		} catch (\Exception $ex) {
			$this->addTableError( 'product', $ex );
		}
		
		if( ! Schema::hasTable('product_tag') ) {
			try {
				Schema::create('product_tag', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('product_tag_id', true, true);
					$table->integer('language_id');
					$table->string('name', 255);

					$table->index('language_id', 'index_l');
				});

				$this->created_tables[] = 'product_tag';
			} catch (\Exception $ex) {
				$this->addTableError( 'product_tag', $ex );
			}
		}
		
		if( ! Schema::hasTable('product_to_tag') ) {
			try {
				Schema::create('product_to_tag', function( Blueprint $table ) use ( $engine ) {
					$table->engine = $engine;

					$table->integer('product_id');
					$table->integer('product_tag_id', false, true);

					$table->unique(array('product_id', 'product_tag_id'), 'index_pp');
				});

				$this->created_tables[] = 'product_to_tag';
			} catch (\Exception $ex) {
				$this->addTableError( 'product_to_tag', $ex );
			}
		}
		
		$this->upComplete()->addEvents()->addLayouts()->addDbData();
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
		$this->removeTables(array(
			'ocme_filter_condition',
			'ocme_filter_grid',
			'ocme_filter_grid_condition',
			'ocme_filter_property',
			'ocme_filter_property_value',
			'ocme_filter_property_value_to_product',
			'ocme_variable',
			
			'filter_group_to_store',
			
			'attribute_to_store',
			'attribute_value',
			'attribute_value_description',
			'product_attribute_value',
			
			'option_to_store',
			
			'product_tag',
			'product_to_tag',
		));
		
		$this->removeColumns(array(
			'filter_group' => array( 'type', 'store_ids', 'with_image', 'with_color', 'values_type' ),
			'filter_group_description' => array( 'seo_url', 'tooltip' ),
			'filter' => array( 'image', 'color' ),
			'filter_description' => array( 'seo_url' ),
			
			'attribute' => array( 'store_ids', 'with_image', 'with_color', 'displayed_values_separator', 'values_type' ),
			'attribute_description' => array( 'seo_url', 'tooltip' ),
			
			'option' => array( 'store_ids', 'with_image', 'with_color', 'values_type' ),
			'option_description' => array( 'seo_url', 'tooltip' ),
			'option_value' => array( 'color' ),
			'option_value_description' => array( 'seo_url' ),
			
			'product' => array( 'ocme_filter_indexed_at' ),
		));
		
		$this->removeModules();		
		$this->removeLayouts();
		
		ocme()->model('setting/setting')->deleteSetting('ocme_mfp_license');
		ocme()->model('setting/setting')->deleteSetting('ocme_mfp_db_changes');
		ocme()->model('setting/setting')->deleteSetting('ocme_mfp_installed_at');
    }
}