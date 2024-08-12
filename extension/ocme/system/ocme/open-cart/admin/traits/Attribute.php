<?php namespace Ocme\OpenCart\Admin\Traits;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use Ocme\Model\Attribute as AttributeModel,
	Ocme\Model\AttributeValue,
	Ocme\Model\AttributeValueDescription,
	Ocme\Model\AttributeGroup,
	Ocme\Model\ProductAttribute,
	Ocme\Model\ProductAttributeValue;

trait Attribute {
	
	/**
	 * @var Attribute
	 */
	protected $attribute;
	
	/**
	 * @var AttributeValue
	 */
	protected $attribute_value;

	private function attributeInitialize() {
		$this->initialize();
		
		/* @var $attribute_id int */
		$attribute_id = intval( ocme()->request()->input( 'filter_attribute_id' ) );
		
		/* @var $attribute_value_id int */
		if( null != ( $attribute_value_id = intval( ocme()->request()->input( 'attribute_value_id' ) ) ) ) {
			if( null != ( $this->attribute_value = AttributeValue::withDescription()->find( $attribute_value_id ) ) ) {
				$attribute_id = $this->attribute_value->attribute_id;
			}
		}
		
		if( null != $attribute_id ) {
			if( null != ( $this->attribute = AttributeModel::withDescription()->with(array('attribute_group' => function($q){$q->withDescription();}))->find( $attribute_id ) ) ) {
				$this->data['attribute'] = $this->attribute;
			}
		}
	}
	
	private function attributeWithBreadcrumbs( array $breadcrumbs = array(), $url = false ) {
		/* @var $url_params array */
		$url_params = array( 'sort', 'order', 'page' );
		
		/* @var $attribute Attribute */
		if( $this->attribute ) {
			$breadcrumbs = array(
				array(
					'text' => $this->attribute->attribute_group->name,
					'href' => ocme()->url()->adminLink('catalog/attribute_group')
				),
				array(
					'text' => $this->attribute->name,
					'href' => ocme()->url()->adminLink('catalog/attribute')
				)
			);

			$url_params[] = 'attribute_id';
		}
		
		return $this->withBreadcrumbs( $breadcrumbs, $url );
	}

	protected function attributeRenderList( $type, array $url_params = array(), array $sorts = array( 'name', 'sort_order' ), $callback = null ) {
		$this->attributeInitialize();
		
		$this->data['error_warning'] = ocme()->arr()->get( $this->error, 'warning', '' );
		$this->data['selected'] = (array) ocme()->request()->input( 'selected', array() );
		
		$this->data['sort'] = ocme()->request()->input( 'sort' );
		$this->data['order'] = ocme()->request()->input( 'order', 'ASC' );
		
		$url_params = array_merge( $url_params, array( 'sort', 'order' ) );

		/* @var $url string */
		$url = ocme()->model('ocme_mfp')->createUrlParams( $url_params );
		
		/* @var $url_sort string */
		$url_sort = ocme()->model('ocme_mfp')->createUrlParams( ocme()->arr()->except( $url_params, array( 'sort', 'order' ) ) ) . '&order=' . ( $this->data['order'] == 'ASC' ? 'DESC' : 'ASC' );

		/* @var $key string */
		foreach( $sorts as $key ) {
			$this->data['sort_' . $key] = ocme()->url()->adminLink($this->name . '/attribute' . ( $type ? '_' . $type : '' ), '&sort=' . $key . $url_sort);
		}
		
		$this->data['add'] = ocme()->url()->adminLink($this->name . '/attribute_' . ( $type ? $type . '_' : '' ) . 'add', $url);
		$this->data['delete'] = ocme()->url()->adminLink($this->name . '/attribute_' . ( $type ? $type . '_' : '' ) . 'delete', $url);
		
		/* @var $page int */
		$page = intval( ocme()->request()->input( 'page', 1 ) );
		
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = ocme()->model('attribute')->{'createAttribute' . ( $type ? ucfirst( $type ) : '' ) . 'sQuery'}();
		
		switch( $type ) {
			case 'value' : {
				$query->with(array(
					'attribute' => function( $q ) {
						$q->withDescription();
					}
				));
				
				ocme()->model('attribute')->applyAttributeValueConditions( $query, ocme()->request()->input() );
				
				break;
			}
		}
		
		/* @var $total int */
		$total = $query->count();
		
		ocme()->model('attribute')
			->applyPagination( $query, ( $page - 1 ) * (int) ocme()->config()->oc('pagination_admin'), ocme()->config()->oc('pagination_admin') );

		$this->data['results'] = array();

		/* @var $result array */
		foreach( $query->get() as $result ) {
			/* @var $url string */
			$url = ocme()->model('ocme_mfp')->createUrlParams( $url_params );
			
			/* @var $url_filter_by_attribute string */
			$url_filter_by_attribute = null;
		
			if( $result instanceof Attribute ) {
				$url .= '&attribute_id=' . $result->attribute_id;
			} else if( $result instanceof AttributeValue ) {
				$url .= '&attribute_value_id=' . $result->attribute_value_id;
				$url_filter_by_attribute = ocme()->model('ocme_mfp')->createUrlParams( ocme()->collection()->make( $url_params )->reject( 'filter_attribute_id' )->all() ) . '&filter_attribute_id=' . $result->attribute_id;
			} else if( $result instanceof AttributeGroup ) {
				$url .= '&attribute_group_id=' . $result->attribute_group_id;
			}
			
			$this->data['results'][] = array(
				'o' => $result,
				'edit' => ocme()->url()->adminLink($this->name . '/attribute_' . ($type ? $type . '_' : '' ) . 'edit', $url),
				'delete' => ocme()->url()->adminLink($this->name . '/attribute_' . ($type ? $type . '_' : '' ) . 'delete', $url),
				'filter_by_attribute' => ocme()->url()->adminLink($this->name . '/attribute_' . $type, $url_filter_by_attribute),
			);
		}
		
		return $this
			->attributeWithBreadcrumbs()
			->withPagination($this->name . '/attribute_' . $type, $total, ocme()->model('ocme_mfp')->createUrlParams( ocme()->arr()->except( $url_params, 'page' ) ))
			->render('attribute_' . ( $type ? $type . '_' : '' ) . 'list', 'module::global.heading_title_attribute' . ( $type ? '_' . $type : '' ), $callback);
	}
	
	public function attribute_value() {
		if( ocme()->request()->filled('filter_attribute_id') && ocme()->request()->filled('adding_attribute_value') ) {
			$this->response->redirect(ocme()->url()->adminLink($this->name . '/attribute_value_add', ocme()->model('ocme_mfp')->createUrlParams('filter_attribute_id')));
		}
		
		$this
			->attributeRenderList( 'value', array(
				'filter_name', 'filter_attribute_id',
			), array(), function(){
				$this->addScript('view/ocme/javascript/attribute-value/filter.js', true);
			});
	}
	
	public function attribute_value_add() {
		$this->attributeRenderForm('value', array(
			'filter_name', 'filter_attribute_id',
		));
	}
	
	public function attribute_value_edit() {
		$this->attributeRenderForm('value', array(
			'filter_name', 'attribute_value_id', 'filter_attribute_id',
		));
	}
	
	public function attribute_value_delete() {
		/* @var $selected array */
		$selected = array_filter( (array) ocme()->request()->input( 'selected', array( ocme()->request()->input( 'attribute_value_id' ) ) ) );
		
		if( $selected && $this->validateDelete( 'value' ) ) {
			/* @var $attribute_value_id int */
			foreach( $selected as $attribute_value_id ) {
				ocme()->model('attribute')->deleteValue( $attribute_value_id );
			}

			ocme()->msg()->success('module::global.success_updated');
		}

		$this->response->redirect(ocme()->url()->adminLink($this->name . '/attribute_value', ocme()->model('ocme_mfp')->createUrlParams('sort', 'order', 'page', 'filter_attribute_id')));
	}
	
	protected function attributeRenderForm( $type = '', array $url_params = array() ) {
		$this->attributeInitialize();
		
		/* @var $key string */
		$key = 'attribute' . ( $type ? '_' . $type : '' ) . '_id';
		
		/* @var $class string */
		$class = '\Ocme\Model\Attribute' . ( $type ? ucfirst( $type ) : '' );
		
		/* @var $record \Ocme\Database\Model */
		$record = $this->{'attribute' . ( $type ? '_' . $type : '' )} = $class::firstOrNew(array(
			$key => ocme()->request()->input( $key ),
		));
		
		if( $type == 'value' ) {
			if( ! $record->exists ) {
				$record->attribute_id = ocme()->request()->query('filter_attribute_id');
			}
			
			if( ! $record->attribute_id ) {
				if( ocme()->oc()->isV3() ) {
					$this->session->data['success'] = ocme()->trans('module::attribute.text_to_add_value_please_select_attribute');
				}
				
				$this->response->redirect(ocme()->url()->adminLink('catalog/attribute', 'adding_attribute_value=1'));
			}
		}
		
		/* @var $id int */
		$id = ocme()->request()->input( $key );
		
		if( ocme()->request()->methodIsPost() ) {
			/* @var $data array */
			$data = ocme()->request()->post();
			
			if( ! $id ) {
				$data = array_replace( $data, array(
					'attribute_id' => ocme()->request()->query('filter_attribute_id'),
				));
			}
			
			if( $type == 'value' ) {
				/* @var $descriptions array */
				if( null == ( $descriptions = ocme()->arr()->get( $data, 'descriptions', array() ) ) ) {
					$descriptions = array();
					
					/* @var $values_type string */
					$values_type = $this->detectValuesType( $record );
					
					switch( $values_type ) {
						case AttributeModel::VALUES_TYPE_FLOAT :
						case AttributeModel::VALUES_TYPE_INTEGER : {
							/* @var $language Language */
							foreach( \Ocme\Model\Language::all() as $language ) {
								$descriptions[$language->language_id] = array( 'name' => ocme()->arr()->get( $data, 'v' . $values_type ) );
							}

							break;
						}
					}
					
					ocme()->arr()->set( $data, 'descriptions', $descriptions );
					
					unset( $descriptions );
				}
			}
			
			
			if( $this->validateForm( $type, $record, $data ) ) {
				/* @var $attribute_value AttributeValue */
				$attribute_value = null;
				
				if( $id ) {
					$attribute_value = ocme()->model('attribute')->editValue($id, $data);
				} else {
					$attribute_value = ocme()->model('attribute')->addValue($data);
				}
				
				if( ocme()->request()->ajax() ) {
					return $this->ajaxResponse(array(
						'status' => 'success',
						'attribute_value' => AttributeValue::withDescription()->find( $attribute_value->attribute_value_id ),
						'msg' => ocme()->trans('module::global.success_updated'),
					));
				}
				
				ocme()->msg()->success('module::global.success_updated');

				$this->response->redirect(ocme()->url()->adminLink($this->name . '/attribute' . ( $type ? '_' . $type : '' ), ocme()->model('ocme_mfp')->createUrlParams('sort', 'order', 'page', 'filter_attribute_id')));
			} else if( $record ){
				$record->fill( ocme()->request()->post() );
			}
		}
		
		$this->data['error_warning'] = ocme()->arr()->get( $this->error, 'warning', '' );
		$this->data['errors'] = ocme()->arr()->except( $this->error, 'warning' );
		$this->data['text_form'] = ocme()->trans( $id ? 'module::global.text_edit' : 'module::global.text_add');
		
		$url_params = array_merge( $url_params, array( 'sort', 'order', 'page' ) );

		/* @var $url string */
		$url = ocme()->model('ocme_mfp')->createUrlParams( $url_params );
		
		$this->data['action'] = ocme()->url()->adminLink($this->name . '/attribute_' . ( $type ? $type . '_' : '' ) . ( $id ? 'edit' : 'add' ), $url);
		$this->data['back'] = ocme()->url()->adminLink($this->name . '/attribute_' . $type, ocme()->model('ocme_mfp')->createUrlParams( array_merge( $url_params, array( 'attribute_id' ) ) ));
		$this->data['record'] = $record;
		
		if( $this->attribute->values_type == AttributeModel::VALUES_TYPE_STRING ) {
			$this->data['descriptions'] = array_replace( ocme()->request()->post('descriptions', array()), ocme()->collection()->make( $record->descriptions )->mapWithKeys(function($v){
				return array( ocme()->arr()->get( $v, 'language_id' ) => $v );
			})->toArray());
		}
		
		if( $type == 'value' ) {
			$this->data['attribute_'.($type?'_'.$type:'')] = $record;
			
			$this->data['edit_attribute_action'] = ocme()->url()->adminLink(
				ocme()->oc()->isVersion('4.0.2.0', '>=') ? 'catalog/attribute.form' : (
					ocme()->oc()->isV4() ? 'catalog/attribute|form' : 'catalog/attribute/edit'
				), 
				ocme()->model('ocme_mfp')->createUrlParams( 'attribute_id', array( 'attribute_id' => $this->attribute->attribute_id ) ) 
			);
			
			/**
			 * Image
			 */
			if( $this->attribute->with_image ) {
				$this->data['image'] = ocme()->request()->post( 'image', $record->image );

				$this->load->model('tool/image');

				if( ocme()->request()->hasPost( 'image' ) && is_file( DIR_IMAGE . ocme()->request()->post( 'image' ) ) ) {
					$this->data['thumb'] = $this->model_tool_image->resize( ocme()->request()->post('image'), 100, 100 );
				} elseif ( $record && $record->image && is_file( DIR_IMAGE . $record->image ) ) {
					$this->data['thumb'] = $this->model_tool_image->resize( $record->image, 100, 100 );
				} else {
					$this->data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
				}

				$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		return $this->attributeWithBreadcrumbs()
			->render('attribute_' . ( $type ? $type . '_' : '' ) . 'form', 'module::global.heading_title_attribute' . ( $type ? '_' . $type : '' ), function(){
				$this->addBaseLibraries();
			});
	}
	
	protected function detectValuesType( $record ) {
		/* @var $values_type string */
		$values_type = 'string';

		if( $record instanceof AttributeValue && $record->exists && $record->attribute->values_type ) {
			$values_type = $record->attribute->values_type;
		} else if( null != ( $attribute_id = ocme()->request()->query('filter_attribute_id') ) ) {
			$values_type = AttributeModel::find( $attribute_id )->values_type;
		}
		
		return $values_type;
	}

	protected function validateForm( $type, $record = null, $data = null ) {
		if( ! $this->validateAccess() ) {
			$this->error['warning'] = ocme()->trans('module::global.error_permission');
		}
		
		if( is_null( $data ) ) {
			$data = ocme()->request()->post();
		}
		
		if( $type == 'value' ) {
			/* @var $values_type string */
			$values_type = $this->detectValuesType( $record );
			
			switch( $values_type ) {
				case AttributeModel::VALUES_TYPE_INTEGER : {
					if( filter_var( ocme()->arr()->get( $data, 'vinteger' ), FILTER_VALIDATE_INT) === false ) {
						$this->error['vinteger'] = ocme()->trans('module::attribute.error_name_not_integer');
					}

					break;
				}
				case AttributeModel::VALUES_TYPE_FLOAT : {
					if( ! is_numeric( ocme()->arr()->get( $data, 'vfloat' ) ) ) {
						$this->error['vfloat'] = ocme()->trans('module::attribute.error_name_not_float');
					}

					break;
				}
				default: {
					foreach( ocme()->arr()->get($data, 'descriptions', array()) as $language_id => $value) {
					/* @var $name string */
					$name = trim( ocme()->arr()->get($value, 'name', '') );

					if( ( ocme()->str()->length( $name, 'utf8' ) < 1 ) || ( ocme()->str()->length( $name, 'utf8' ) > 64 ) ) {
						$this->error['name'][$language_id] = ocme()->trans('module::attribute.error_name');
					} else if( $record instanceof AttributeValue ) {
						/* @var $query \Illuminate\Database\Eloquent\Builder */
						$query = AttributeValueDescription::query()
							->join('attribute_value', 'attribute_value.attribute_value_id', '=', 'attribute_value_description.attribute_value_id')
							->where('attribute_value.attribute_id', $record->attribute_id)
							->where('attribute_value_description.name', $name)
							->where('attribute_value_description.language_id', $language_id);

						if( $record->exists ) {
							$query->where('attribute_value_description.attribute_value_id', '!=', $record->attribute_value_id);
						}

						if( $query->first() ) {
							$this->error['name'][$language_id] = ocme()->trans('module::attribute.error_name_exists');
						}
					}
				}
					
					break;
				}
			}
		}

		return ! $this->error;
	}

	protected function validateDelete( $type ) {
		if (!$this->validateAccess()) {
			$this->error['warning'] = ocme()->trans('module::global.error_permission');
		}

		if( $type == 'attribute' ) {
			$this->load->model('catalog/product');

			foreach( ocme()->request()->post('selected') as $attribute_id) {
				$product_total = $this->model_catalog_product->getTotalProductsByAttributeId($attribute_id);

				if ($product_total) {
					$this->error['warning'] = sprintf(ocme()->trans('module::attribute.error_product'), $product_total);
				}
			}
		}

		return !$this->error;
	}
	
	protected function applyConditions( \Illuminate\Database\Eloquent\Builder $query, $name, $method ) {
		/* @var $id int */
		if( null != ( $id = intval( ocme()->request()->input( 'filter_' . $name ) ) ) ) {
			$query->where(function($q) use( $query, $id, $name, $method ){
				$q->where($query->getQuery()->getFromAlias() . '.' . $name, $id)->orWhere(function($q) use( $query, $id, $name, $method ){
					$q->where($query->getQuery()->getFromAlias() . '.' . $name, '!=', $id);
					
					ocme()->model('attribute')->{$method}( $q, $this->request->post );
				});
			})->orderBy( ocme()->db()->raw( 'IF(' . $query->getQuery()->getFromAlias() . '.`' . $name . '` = ' . $id . ', 0, 1)' ) );
		} else {
			ocme()->model('attribute')->{$method}( $query, $this->request->post );
		}
		
		return $this;
	}
	
	public function attributes() {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = ocme()->model('attribute')->createAttributesQuery()
			->withAttributeGroup('`ag`')
			->with(array(
				'attribute_group' => function( $q ) {
					$q->withDescription();
				}
			));
		
		return $this->applyConditions( $query, 'attribute_id', 'applyAttributeConditions' )->ajaxPaginateResponse( $query->orderBy('`ad`.name'), 50 );
	}
	
	protected function _attribute_values( $data ) {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = ocme()->model('attribute')->createAttributeValuesQuery()
			->with(array(
				'attribute' => function( $q ) {
					$q->withDescription()->with(array(
						'attribute_group' => function($q){
							$q->withDescription();
						}
					));
				},
				'descriptions',
			))
			->orderBy('`avd`.name');
		
		ocme()->model('attribute')
			->applyAttributeValueConditions( $query, $data );
		
		return $this->ajaxPaginate( $query, 50 );
	}
	
	public function attribute_values() {
		if( ocme()->request()->isXmlHttpRequest() ) {
			return $this->ajaxResponse( $this->_attribute_values( $this->request->post ) );
		}
		
		return $this->attribute_value();
	}
	
	public function multiple_attribute_values() {
		/* @var $responses array */
		$responses = array();
		
		/* @var $requests array */
		if( null != ( $requests = ocme()->request()->post( 'requests' ) ) ) {
			/* @var $request array */
			foreach( $requests as $request ) {
				$responses[] = array(
					'key' => ocme()->arr()->get( $request, 'key' ),
					'data' => $this->_attribute_values( ocme()->arr()->get( $request, 'data' ) ),
				);
			}
		}
		
		return $this->ajaxResponse( compact( 'responses' ) );
	}
	
	public function attribute_groups() {
		/* @var $query \Illuminate\Database\Eloquent\Builder */
		$query = ocme()->model('attribute')->createAttributeGroupsQuery();
		
		return $this->applyConditions( $query, 'attribute_group_id', 'applyAttributeGroupConditions' )->ajaxPaginateResponse( $query->orderBy('`agd`.name'), 50 );
	}
	
	public function product_attributes() {
		/* @var $response array */
		$response = array(
			'status' => 'error',
		);
		
		/* @var int $product_id */
		if( null != ( $product_id = ocme()->request()->query( 'product_id' ) ) ) {
			if( ProductAttribute::where('product_id', $product_id)->first() && ! ProductAttributeValue::where('product_id', $product_id)->first() ) {
				/* @var $product_attribute ProductAttribute */
				foreach( ProductAttribute::where('product_id', $product_id)->get() as $product_attribute ) {
					$product_attribute->reCreate();
				}
			}
			
			/* @var $product_attribute ProductAttribute */
			foreach( ProductAttribute::query()
				->addFromAlias('`pa`')
				->where('`pa`.product_id', $product_id)
				->with(array(
					'attribute' => function($q){
						$q->withDescription()->with(array(
							'attribute_group' => function($q){
								$q->withDescription();
							}
						));
					}
				))
				->orderBy('`pa`.sort_order')
				->get() as $product_attribute 
			) {
				if( ! isset( $response['attributes'][$product_attribute->attribute_id] ) ) {
					$response['attributes'][$product_attribute->attribute_id] = array(
						'attribute_id' => $product_attribute->attribute_id,
						'name' => $product_attribute->attribute->name,
						'attribute_group' => array(
							'name' => $product_attribute->attribute->attribute_group->name,
						),
						'sort_order' => $product_attribute->sort_order,
						'values' => array(),
					);
				}
				
			}
			
			/* @var $product_attribute_value ProductAttributeValue */
			foreach( ProductAttributeValue::query()
				->addFromAlias('`pav`')
				->where('`pav`.product_id', $product_id)
				->with(array(
					'attribute_value' => function($q){
						$q->withDescription()->with('descriptions');
					},
				))
				->orderBy('`pav`.sort_order')
				->get() as $product_attribute_value 
			) {				
				$response['attributes'][$product_attribute_value->attribute_id]['values'][] = array(
					'product_attribute_value_id' => $product_attribute_value->product_attribute_value_id,
					'attribute_value_id' => $product_attribute_value->attribute_value_id,
					'name' => $product_attribute_value->attribute_value ? $product_attribute_value->attribute_value->name : '[Missing translation - check the consistency of the database]',
					'descriptions' => $product_attribute_value->attribute_value ? $product_attribute_value->attribute_value->descriptions : array(),
				);
			}
				
			$response['attributes'] = array_values( ocme()->arr()->get( $response, 'attributes', array() ) );
			$response['status'] = 'success';
		}
		
		return $this->ajaxResponse( $response );
	}
	
	public function attribute_eventModelBefore(&$route, &$args) {
		$this->path = 'extension/module/ocme_mfp_attribute';
		
		return $this->eventModel('before', $route, $args);
	}
	
	public function attribute_eventModelAfter(&$route, &$args, &$output) {
		$this->path = 'extension/module/ocme_mfp_attribute';
		
		return $this->eventModel('after', $route, $args, $output);
	}
	
}