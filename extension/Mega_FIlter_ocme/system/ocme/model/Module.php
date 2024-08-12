<?php namespace Ocme\Model;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 * 
 * @property int $module_id
 * @property string $name
 * @property string $code
 * @property array $setting
 */

class Module extends \Ocme\Database\Model {
	
	const CODE_FILTER = 'ocme_mfp_filter';
	const CODE_SEARCH = 'ocme_mfp_search';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'module';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'module_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'name', 'code', 'setting',
	);
	
	public static function boot() {
		parent::boot();
		
		self::creating(function( $module ){
			if( ! $module->name && $module->code ) {
				/* @var $count int */
				$count = self::where('code', $module->code)->count() + 1;
				
				$module->name = 'Mega Filter Pack #';
				
				while( self::where('code', $module->code)->where('name', $module->name . $count)->count() ) {
					$count++;
				}
				
				$module->name .= $count;
			}
			
			if( empty( $module->attributes['setting'] ) ) {
				$module->setting = array(
					'name' => $module->name,
				);
			}
		});
		
		self::created(function( $module ){
			if( ocme()->arr()->get( $module->setting, 'module_id' ) && ocme()->arr()->get( $module->setting, 'module_id' ) != $module->module_id ) {
				$module->setting = array_replace( $module->setting, array(
					'module_id' => $module->module_id
				));
				
				$module->save();
			}
		});
		
		self::deleted(function( $module ){
			/* @var $layout_module LayoutModule */
			foreach( LayoutModule::where('code', $module->code . '.' . $module->module_id)->get() as $layout_module ) {
				$layout_module->delete();
			}
			
			/* @var $ocme_filter_condition OcmeFilterCondition */
			foreach( OcmeFilterCondition::where('module_id', $module->module_id)->get() as $ocme_filter_condition ) {
				$ocme_filter_condition->delete();
			}
			
			/* @var $ocme_filter_grid OcmeFilterGrid */
			foreach( OcmeFilterGrid::where('module_id', $module->module_id)->get() as $ocme_filter_grid ) {
				$ocme_filter_grid->delete();
			}
		});
		
		self::saved(function( $module ){
			/* @var $setting array */
			$setting = $module->setting;
			
			/* @var $query_delete_unused_layouts */
			$query_delete_unused_layouts = LayoutModule::where('code', $module->code . '.' . $module->module_id);
			
			/* @var $layout_ids array */
			if( null != ( $layout_ids = ocme()->arr()->get( $setting, 'layouts' ) ) ) {
				/* @var $layout_id int */
				foreach( $layout_ids as $layout_id ) {
					/* @var $layout_module LayoutModule */
					$layout_module = LayoutModule::firstOrNew(array(
						'layout_id' => $layout_id,
						'code' => $module->code . '.' . $module->module_id
					))->fill(array(
						'position' => ocme()->arr()->get( $setting, 'position' ),
						'sort_order' => (int) ocme()->arr()->get( $setting, 'sort_order' ),
					));
					
					$layout_module->save();
					
					$query_delete_unused_layouts->where('layout_module_id', '!=', $layout_module->layout_module_id);
				}
			}
			
			/* @var $layout_module LayoutModule */
			foreach( $query_delete_unused_layouts->get() as $layout_module ) {
				$layout_module->delete();
			}
		});
	}
	
	public static function codeByOcVersion( $type ) {
		if( version_compare( VERSION, '4', '>=' ) ) {
			return 'ocme.' . $type;
		}
		
		return $type;
	}
	
	// Functions ///////////////////////////////////////////////////////////////
	
	public function duplicate() {
		/* @var $name string */
		$name = preg_replace('/ copy ?[0-9]*/', '', $this->name ) . ' copy';
		
		/* @var $count int */
		$count = self::where('code', self::codeByOcVersion('ocme_mfp_filter'))->where('name', 'LIKE', $name . '%')->count();
		
		while( self::where('code', $this->code)->where('name', $name . ( $count > 1 ? ' ' . $count : '' ))->count() ) {
			$count++;
		}
		
		if( $count > 1 ) {
			$name .= ' ' . $count;
		}
		
		/* @var $module Module */
		$module = self::create(array(
			'name' => $name,
			'code' => $this->code,
			'setting' => $this->setting,
		));
		
		/* @var $layout_module LayoutModule */
		foreach( LayoutModule::where('code', $this->code . '.' . $this->id)->get() as $layout_module ) {
			LayoutModule::create(array(
				'layout_id' => $layout_module->layout_id,
				'code' => $this->code . '.' . $module->id,
				'position' => $layout_module->position,
				'sort_order' => $layout_module->sort_order,
			));
		}
		
		if( $this->code == self::CODE_FILTER ) {
			/* @var $conditions_map array */
			$conditions_map = array();
			
			/* @var $ocme_filter_condition OcmeFilterCondition */
			foreach( OcmeFilterCondition::where('module_id', $this->module_id)->get() as $ocme_filter_condition ) {
				/* @var $new_ocme_filter_condition OcmeFilterCondition */
				$new_ocme_filter_condition = OcmeFilterCondition::create(array(
					'module_id' => $module->module_id,
					'condition_type' => $ocme_filter_condition->condition_type,
					'name' => $ocme_filter_condition->name,
					'record_id' => $ocme_filter_condition->record_id,
					'status' => $ocme_filter_condition->status,
					'type' => $ocme_filter_condition->type,
					'sort_order' => $ocme_filter_condition->sort_order,
					'setting' => $ocme_filter_condition->setting,
				));
				
				$conditions_map[$ocme_filter_condition->id] = $new_ocme_filter_condition->id;
			}
			
			/* @var $grids_map array */
			$grids_map = array();
			
			/* @var $ocme_filter_grid OcmeFilterGrid */
			foreach( OcmeFilterGrid::where('module_id', $this->module_id)->orderBy('id')->get() as $ocme_filter_grid ) {
				/* @var $new_ocme_filter_grid OcmeFilterGrid */
				$new_ocme_filter_grid = OcmeFilterGrid::create(array(
					'module_id' => $module->module_id,
					'parent_id' => $ocme_filter_grid->parent_id ? $grids_map[$ocme_filter_grid->parent_id] : null,
					'type' => $ocme_filter_grid->type,
					'sort_order' => $ocme_filter_grid->sort_order,
					'settings' => $ocme_filter_grid->settings,
				));
				
				$grids_map[$ocme_filter_grid->id] = $new_ocme_filter_grid->id;
				
				/* @var $ocme_filter_grid_condition OcmeFilterGridCondition */
				foreach( OcmeFilterGridCondition::where('ocme_filter_grid_id', $ocme_filter_grid->id)->get() as $ocme_filter_grid_condition ) {
					OcmeFilterGridCondition::create(array(
						'ocme_filter_grid_id' => $new_ocme_filter_grid->id,
						'ocme_filter_condition_id' => $conditions_map[$ocme_filter_grid_condition->ocme_filter_condition_id],
						'vid' => $ocme_filter_grid_condition->vid,
						'vtype' => $ocme_filter_grid_condition->vtype,
						'vname' => $ocme_filter_grid_condition->vname,
						'sort_order' => $ocme_filter_grid_condition->sort_order,
					));
				}
			}
		}
		
		return $module;
	}
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	public function getSettingAttribute( $v ) {
		/* @var $setting array */
		$setting = array_replace(array(
			'status' => '1',
			'stores' => array( 0 ),
			'position' => 'column_left',
		), $v ? json_decode( $v, true ) : array());
		
		/**
		 * Default layout / Category
		 */
		if( empty( $setting['layouts'] ) ) {
			/* @var $layout_route LayoutRoute */
			if( null != ( $layout_route = LayoutRoute::where('store_id', 0)->where('route', LayoutRoute::ROUTE_PRODUCT_CATEGORY)->first() ) ) {
				$setting['layouts'][] = $layout_route->layout_id;
			}
		}
		
		/**
		 * Default module title
		 */
		if( empty( $setting['title'] ) ) {
			/* @var $language Language */
			foreach( Language::get() as $language ) {
				$setting['title'][$language->language_id] = 'Mega Filter Pack';
			}
		}
		
		$setting['conditions']['base_attributes']['items'] = [];
		
		return $setting;
	}
	
	// Mutators ////////////////////////////////////////////////////////////////
	
	public function setSettingAttribute( $v ) {
		if( empty( $v['stores'] ) ) {
			$v['stores'] = [ 0 ];
		}
		
		$this->attributes['setting'] = json_encode( $v ? $v : array() );
	}
	
}