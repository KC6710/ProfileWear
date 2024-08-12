<?php namespace Ocme\OpenCart\Admin;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

trait Controller {
	
	/**
	 * Module name
	 * 
	 * @var string
	 */
	protected $name = null;
	
	/**
	 * Module path
	 * 
	 * @var string
	 */
	protected $path = null;
	
	/**
	 * View data
	 * 
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * Ajax data
	 * 
	 * @var array
	 */
	protected $ajax_data = array();
	
	/**
	 * Errors
	 * 
	 * @var array
	 */
	protected $error = array();
	
	/**
	 * @var bool
	 */
	private $fatal_error = false;
	
	/**
	 * @var array
	 */
	private $installed_modules;
	
	protected function isInstalledModule( $name ) {
		if( is_null( $this->installed_modules ) ) {
			/* @var $extensions array */
			$extensions = array();
			
			if( version_compare( VERSION, '4', '>=' ) ) {
				$extensions = array_map(function( $module ){
					return ocme()->arr()->get( $module, 'code' );
				}, ocme()->model('setting/extension')->getExtensionsByType('module'));
			} else {
				$extensions = ocme()->model('setting/extension')->getInstalled('module');
			}
			
			$this->installed_modules = array_filter( $extensions, function( $module ){
				return strpos( $module, 'ocme_' ) === 0;
			});
		}
		
		return in_array( $name, $this->installed_modules );
	}
	
	/**
	 * Render view
	 * 
	 * @param string $view
	 * @param string $title
	 */
	protected function render( $view, $title = 'module::global.heading_name', $callback = null ) {
		$this->data['ocme'] = ocme();
		
		if( ! in_array( $view, array( 'connect', 'disconnect' ) ) ) {
			if( ! ocme()->license()->token() ) {
				$this->data['ocme_ak_reminder'] = str_replace( ':url', ocme()->url()->adminLink($this->path.'/connect'), ocme()->trans('module::global.text_enter_ak') );
			}
		}
		
		/**
		 * MijoShop
		 */
		$this->data['HTTP_URL'] = '';
		
		if( version_compare( VERSION, '4', '>=' ) ) {
			$this->data['HTTP_URL'] = HTTP_CATALOG . 'extension/ocme/admin/';
		} else if( class_exists( 'MijoShop' ) ) {
			$this->data['HTTP_URL'] = HTTP_CATALOG . 'opencart/admin/';
		}
		
		$this->data['action_home'] = ocme()->url()->adminLink($this->path);
		$this->data['action_filter'] = $this->isInstalledModule( 'ocme_mfp_filter' ) ? ocme()->url()->adminLink($this->path.'_filter') : null;
		$this->data['action_search'] = ocme()->url()->adminLink($this->path.'/search');
		$this->data['action_seo'] = ocme()->url()->adminLink($this->path.'/seo');
		$this->data['action_config'] = ocme()->url()->adminLink($this->path.'/config');
		$this->data['action_about'] = ocme()->url()->adminLink($this->path.'/about');
		
		////////////////////////////////////////////////////////////////////////
		
		$this->document->setTitle(ocme()->trans($title));
		
		if( ! isset( $this->data['ocme_heading_title'] ) ) {
			$this->data['ocme_heading_title'] = ocme()->trans($title, array(
				'ocme_url' => defined('DIR_EXTENSION') ? HTTP_CATALOG . 'extension/ocme/admin/' : ( defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER ),
			));
		}
		
		/* @var $format string */
		$format = ocme()->request()->query('format');
		
		$this->data['view'] = $view;
		
		if( isset( $this->session->data['success'] ) ) {
			$this->data['success_message'] = $this->session->data['success'];
			
			unset( $this->session->data['success'] );
		}
		
		if( $this->fatal_error ) {
			$this->data[$this->path.'_fatal_error'] = $this->fatal_error;
		}
		
		if( is_callable( $callback ) ) {
			$callback();
		}
		
		$this->data['header'] = $format == 'json' ? '' : $this->renderHeader( $view );
		$this->data['footer'] = $format == 'json' ? '' : $this->renderFooter( $view );
		
		if( $format == 'json' ) {
			return $this->ajaxResponse(array(
				'view' => $this->load->view(ocme_template_path($this->path) . '/' . $view, $this->data),
				'data' => $this->ajax_data,
			));
		}

		$this->response->setOutput($this->load->view(ocme_template_path($this->path) . '/' . $view, $this->data));
	}
	
	private function commonPath( $view ) {
		/* @var $path string */
		$path = ocme_template_path($this->path) . '/';
		
		if( in_array( $view, array( 'attribute_value_list', 'attribute_value_form' ) ) ) {
			$path .= 'attribute';
		}
		
		return $path;
	}
	
	protected function renderHeader( $view ) {
		return $this->load->view($this->commonPath($view).'_header', array_replace($this->data, array(
			'header' => $this->load->controller('common/header'),
			'column_left' => $this->load->controller('common/column_left'),
			'ocme_heading_title' => ocme()->trans('module::global.heading_title', array(
				'ocme_url' => defined('DIR_EXTENSION') ? HTTP_CATALOG . 'extension/ocme/admin/' : ( defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER ),
			))
		)));
	}
	
	protected function renderFooter( $view ) {
		return $this->load->view($this->commonPath($view).'_footer', array_replace($this->data, array(
			'footer' => $this->load->controller('common/footer'),
		)));
	}
	
	protected function ajaxPaginateResponse( \Illuminate\Database\Eloquent\Builder $query, $perPage = null ) {		
		$this->ajaxResponse( $this->ajaxPaginate( $query, $perPage ) );
	}
	
	protected function ajaxPaginate( \Illuminate\Database\Eloquent\Builder $query, $perPage = null ) {		
		\Illuminate\Pagination\Paginator::currentPageResolver(function(){
			return ocme()->request()->input( 'page', 1 );
		});
		
		return ocme()->arr()->only( $query->paginate( $perPage )->toArray(), array( 'per_page', 'total', 'last_page', 'current_page', 'data' ) );
	}
	
	protected function ajaxResponse( $response ) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode( $response ));		
	}
	
	protected function withPagination( $route, $total, $url = '' ) {
		/* @var $page int */
		$page = intval( ocme()->request()->input( 'page', 1 ) );
		
		/** @var int $limit */
		$limit = (int) ocme()->config()->oc('pagination_admin');
		
		if( ocme()->oc()->isV4OrLater() ) {
			$this->data['pagination'] = $this->load->controller('common/pagination', array(
				'total' => $total,
				'page'  => $page,
				'limit' => $limit,
				'url'   => ocme()->url()->adminLink($route, $url . '&page={page}')
			));
		} else {
			/** @var \Pagination $pagination */
			$pagination = new \Pagination();
			$pagination->total = $total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = ocme()->url()->adminLink($route, $url . '&page={page}');

			$this->data['pagination'] = $pagination->render();
		}

		$this->data['pagination_info'] = sprintf(ocme()->trans('module::global.text_pagination'), ($total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit), $total, ceil($total / $limit));
		
		return $this;
	}
	
	protected function with( $key, $value = null ) {
		if( is_array( $key ) ) {
			$this->data = array_replace( $this->data, $key );
		} else {
			$this->data[$key] = $value;
		}
		
		return $this;
	}
	
	protected function withBreadcrumbs( array $breadcrumbs = array(), $url = '' ) {
		$this->data['breadcrumbs'] = array(
			array(
				'text' => ocme()->trans('module::global.text_home'),
				'href' => ocme()->url()->adminLink('common/dashboard')
			),
		);
		
		foreach( $breadcrumbs as $breadcrumb ) {
			$this->data['breadcrumbs'][] = $breadcrumb;
		}
		
		if( $url !== false ) {
			if( $url === '' && null != ( $user_token = ocme()->arr()->get( ocme()->oc()->registry()->get('session')->data, ocme()->url()->userTokenParamName() ) ) ) {
				$url .= ocme()->url()->userTokenParamName() . '=' . $user_token;
			}
			
			$this->data['breadcrumbs'][] = array(
				'text' => ocme()->trans('module::global.heading_name'),
				'href' => ocme()->url()->adminLink($this->name, $url)
			);
		}
		
		return $this;
	}
	
	protected function modelName() {
		return 'model_' . str_replace('/', '_', $this->path);
	}
	
	protected function eventAction($type, &$route, &$args, &$output = null) {}
	
	protected function eventModel($type, &$route, &$args, &$output = null) {
		$this->load->model( $this->path );		
		
		/* @var $parts array */
		$parts = explode( '/', $route );
		
		/* @var $method string */
		$method = array_pop( $parts );
		
		/* @var $event string */
		$event = 'event' . ucfirst( $type ) . ucfirst( $method );
		
		if( $this->{$this->modelName()}->hasEvent( $event ) ) {
			return call_user_func_array( array( $this->{$this->modelName()}, $event ), array_merge( $args, array( $output ) ) );
		}
	}
	
	protected function eventView($type, &$route, &$data, &$output) {}
	
	public function eventModelBefore(&$route, &$args) {
		return $this->eventModel('before', $route, $args);
	}
	
	public function eventModelAfter(&$route, &$args, &$output) {
		return $this->eventModel('after', $route, $args, $output);
	}
	
	public function eventActionBefore(&$route, &$args) {
		return $this->eventAction('before', $route, $args);
	}
	
	public function eventActionAfter(&$route, &$args, &$output) {
		return $this->eventAction('after', $route, $args, $output);
	}
	
	public function eventViewAfter(&$route, &$data, &$output) {
		return $this->eventView('after', $route, $data, $output);
	}
	
	public function eventViewBefore(&$route, &$data, &$template) {
		return $this->eventView('before', $route, $data, $template);
	}
	
}