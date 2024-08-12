<?php
namespace Opencart\Admin\Controller\Codevoc;
include_once DIR_SYSTEM . 'library/sendgridapimail/sendgridapimail.php';
use \Opencart\System\Library\SendGridApiMail\SendgridapiMail;
class B2bmanagerOrder extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function index() {
		$this->load->language('codevoc/b2bmanager_order');
		$this->document->addStyle('view/stylesheet/pw4_b2bmanager.css');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_order');

		$this->getList();
	}

	public function add() {
		$this->load->language('codevoc/b2bmanager_order');$this->document->addStyle('view/stylesheet/pw4_b2bmanager.css');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_order');

		$this->getForm();
	}

	public function edit() {
		$this->load->language('codevoc/b2bmanager_order');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/javascript/jquery-fileuploader/css/jquery.fileupload.css');
		$this->document->addStyle('view/stylesheet/pw4_b2bmanager.css');
        $this->document->addStyle('view/javascript/jquery-fileuploader/css/jquery.fileupload-ui.css');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/vendor/jquery.ui.widget.js');
		$this->document->addScript('//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js');
        $this->document->addScript('//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.iframe-transport.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-process.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-image.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-validate.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/spw-fileuploader.js');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_order');

		$this->getForm();
	}

	public function delete() {
		$this->load->language('codevoc/b2bmanager_order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->session->data['success'] = $this->language->get('text_success');

		$url = '';

        //v7
			if (isset($this->request->get['filter_search'])) {
				$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
			}
			//v7

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->response->redirect($this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . $url, true));
	}

	protected function getList() {


	    //v7
		if (isset($this->request->get['filter_search'])) {
			$filter_search = $this->request->get['filter_search'];
		} else {
			$filter_search = '';
		}
		// //v7


		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_order_status'])) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = '';
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = '';
		}

		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['assignee']) && $this->request->get['assignee'] != 0) {
			$assigneeFilter = $this->request->get['assignee'];
		} else {
			$assigneeFilter = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = '';
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$url = '';

        //v7
		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
		}
		//v7

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		if (isset($this->request->get['assignee']) && $this->request->get['assignee'] != 0) {
			$url .= '&assignee=' . $this->request->get['assignee'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['shipping'] = $this->url->link('codevoc/b2bmanager_order.purchaseorder', 'user_token=' . $this->session->data['user_token'], true);
		$data['add'] = $this->url->link('codevoc/b2bmanager_quotation.add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = str_replace('&amp;', '&', $this->url->link('codevoc/b2bmanager_order.delete', 'user_token=' . $this->session->data['user_token'] . $url, true));
		$data['order_url'] = $this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['orders'] = array();

		$filter_data = array(
			'filter_order_id'        => $filter_order_id,
			'filter_customer'	     => $filter_customer,
			'filter_order_status'    => $filter_order_status,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_total'           => $filter_total,
			'filter_date_added'      => $filter_date_added,
			'filter_date_modified'   => $filter_date_modified,
			'filter_assignee'		 => $assigneeFilter,
		    'filter_search'			 =>$filter_search,
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit'                  => $limit
		);
		$this->load->model('user/user');

		$filter_data_users = array(
        'sort'  => 'username',
        'order' => 'ASC'
        );

		$data['system_users'] = $this->model_user_user->getUsers($filter_data_users);

		$order_total = $this->model_codevoc_b2bmanager_order->getTotalOrders($filter_data);

		$results = $this->model_codevoc_b2bmanager_order->getOrders($filter_data);

		$this->load->model('codevoc/b2bmanager_quotation');

		foreach ($results as $result) {
			//get custom fields
			$companyname='';
			$order_info = $this->model_codevoc_b2bmanager_order->getOrderCustomfield($result['order_id']);
			$check_purchase = $this->db->query("SELECT  purchase_id FROM " . DB_PREFIX . "codevoc_purchase_order where order_id = '".$result['order_id']."'");
			if($check_purchase->num_rows>0)
			{
			   $purchase_id=$check_purchase->row['purchase_id'];
			   $purchase_url=$this->url->link('codevoc/b2bmanager_purchase.edit', 'user_token=' . $this->session->data['user_token'] . '&purchase_id=' . $check_purchase->row['purchase_id'], true);
			}else{
				$purchase_id=0;
				$purchase_url=0;
			}
			$custom_field=$order_info['custom_field'];

			if(!empty($order_info['custom_field'] && is_array($order_info['custom_field']) && array_key_exists('1',$custom_field)))
			{
				$companyname=$custom_field[1];
			}

			//get custom fields

			// get production
			 $data['productions']='';
			 $data['productionurl']='';

			 $check_production = $this->db->query("SELECT  * FROM " . DB_PREFIX . "codevoc_production where order_id = '".$result['order_id']."' order by production_id DESC limit 1 ");
			 if($check_production->num_rows>0)
			 {
			 	$production_id=$check_production->row['production_id'];
				$production_url=$this->url->link('codevoc/b2bmanager_production.edit', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $check_production->row['production_id'], true);
			 }else{
			 	$production_id=0;
			 	$production_url=0;
			 }
			// get production

			$data['orders'][] = array(
				'order_id'            => $result['order_id'],
				'customer'            => $result['customer'],
				'payment_method'      => $result['payment_method'],
				'companyname'	      => $companyname,
				'production'          => $production_id,
				'production_url'      => $production_url,
				'purchase' 	      	  => $purchase_id,
				'purchase_url' 	      => $purchase_url,
				'quotation_id'        => $result['quotation_id'],
				'assignee'            => $result['assignee'],
 				'quotation_link'      => $this->url->link('codevoc/b2bmanager_quotation.edit', 'user_token=' . $this->session->data['user_token'] . '&quotation_id=' . $result['quotation_id'] . $url, true),
				'order_type'          => $result['order_type'],
				'order_status'  	  => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'         	  => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'date_added'      	  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'shipping_code' 	  => $result['shipping_code'],
				'shipping'      	  => $this->url->link('codevoc/b2bmanager_order.purchaseorder', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true),
				'edit'            	  => $this->url->link('codevoc/b2bmanager_order.edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true)
			);
		}


		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		//v7
		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
		}
		//v7

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_order'] = $this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url, true);
		$data['sort_customer'] = $this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_status'] = $this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . '&sort=order_status' . $url, true);
		$data['sort_total'] = $this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.total' . $url, true);
		$data['sort_date_added'] = $this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_added' . $url, true);
		$data['filter_order_status_id'] = $filter_order_status_id;

		$url = '';

        //v7
		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
		if (isset($this->request->get['assignee']) && $this->request->get['assignee'] != 0) {
			$url .= '&assignee=' . $this->request->get['assignee'];
		}
		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
		//v7
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $order_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('codevoc/b2bmanager_order', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}' , true)
		]);

		$data['limit'] = $limit;
		$data['assigneeFilter'] = $assigneeFilter;

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($order_total - $this->config->get('config_pagination_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $order_total, ceil($order_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

        //v7
		$data['filter_search'] = $filter_search;
		//v7

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		// API login
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTP_CATALOG : HTTP_CATALOG;

		// API login
		$this->load->model('user/api');

		$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'codevoc/b2bmanager_order')) {
			$session = new \Opencart\System\Library\Session($this->config->get('session_engine'), $this->registry);

			$session->start();

			$this->model_user_api->deleteSessionBySessionId($session->getId());

			$this->model_user_api->addSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('codevoc/b2bmanager_orderlist', $data));
	}

	public function getForm() {

		$url = '';

        //v7
		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
		}
		//v7

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['shipping']='';

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_codevoc_b2bmanager_order->getOrder($this->request->get['order_id']);
			$data['shipping'] = $this->url->link('sale/order/shipping&order_id='.$this->request->get['order_id'], 'user_token=' . $this->session->data['user_token'], true);
			$data['link_receipt'] = $this->url->link('codevoc/b2bmanager_order.purchaseorder&order_id='.$this->request->get['order_id'], 'user_token=' . $this->session->data['user_token'] . '&request_type=receipt', true);
			$data['link_delivery_note'] = $this->url->link('codevoc/b2bmanager_order.purchaseorder&order_id='.$this->request->get['order_id'], 'user_token=' . $this->session->data['user_token'], true);
		}
			$data['cancel'] = $this->url->link('codevoc/b2bmanager_order&user_token='. $this->session->data['user_token'], true);

		if (!empty($order_info)) {
			$data['order_id'] = $this->request->get['order_id'];
			$data['store_id'] = $order_info['store_id'];
			$data['store_url'] = $this->request->server['HTTPS'] ? HTTP_CATALOG : HTTP_CATALOG;
			$data['store_name'] = $order_info['store_name'];
			$data['customer'] = $order_info['customer'];
			$data['customer_id'] = $order_info['customer_id'];
			$data['customer_group_id'] = $order_info['customer_group_id'];
			$data['firstname'] = $order_info['firstname'];
			$data['lastname'] = $order_info['lastname'];
			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];
			$data['account_custom_field'] = $order_info['custom_field'];
			$data['quotation_id'] = $order_info['quotation_id'];
			$data['order_type'] = $order_info['order_type'];
			$data['this_order_status_id'] = $order_info['order_status_id'];
			$this->load->model('codevoc/b2bmanager_shipmondo');
			$shipmentDetails = $this->model_codevoc_b2bmanager_shipmondo->getShipmentDetails($order_info['order_id']);
			if(count($shipmentDetails) > 0){
				foreach($shipmentDetails as $shipment){
					$product = $this->getShipmondoProduct($shipment['product_code']);
					$services = $product[0]->available_services;
					$addons_db = explode(",",$shipment['service_codes']);
					$packages = $this->getPackageType($shipment['product_code']);
					foreach($packages as $pack){
						if($pack->code == $shipment['package_type']){
							$package = $pack->description;
						}
					}
					$addons = array();
					foreach($services as $service){
						if(in_array($service->code, $addons_db)){
							$addons[] = $service->name; 
						}
					}
					$data['shipment_details'][] = array(
						'id'			 => $shipment['id'],
						'shipment_id'    => $shipment['shipment_id'],
						'carrier'        => $this->getCarrierName($shipment['carrier_code']),
						'weight'         => $shipment['weight'],
						'quantity'       => $shipment['quantity'],
						'date_created'   => date('Y-m-d',strtotime($shipment['created_at'])),
						'product_type'   => $product[0]->name,
						'service_addons' => $addons,
						'package_type'   => $package
					);
				}
				
			}else{
				$data['shipment_details'] = '';
			}

			$this->load->model('codevoc/b2bmanager_quotation');
			$quotation_details = $this->model_codevoc_b2bmanager_quotation->getQuotation($data['quotation_id']);
			if($quotation_details){
				$data['quotation_date'] = date($this->language->get('date_format_short'), strtotime($quotation_details['date_added']));
			}
			$quotation_others = $this->model_codevoc_b2bmanager_quotation->getQuotationOtherdetails($data['quotation_id']);  

			if ($order_info['quotation_id']) {
				$data['button_quotation'] = $this->url->link('codevoc/b2bmanager_quotation.edit&user_token=' . $this->session->data['user_token'].'&quotation_id='.$order_info['quotation_id'], true);
			} else {
				$data['button_quotation'] = '';
			}


			if ($order_info['customer_id']) {
				$data['customerurl'] = $this->url->link('customer/customer/edit&customer_id='.$order_info['customer_id'], 'user_token=' . $this->session->data['user_token'], true);
			} else {
				$data['customerurl'] = '';
			}

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			//addon point 5
			 $data['productions']['production_id']='';
			 $data['productions']['productionurl']='';
			 $data['productions']['date_created']='';
			 $check_production = $this->db->query("SELECT  * FROM " . DB_PREFIX . "codevoc_production where order_id = '".$this->request->get['order_id']."' order by production_id DESC limit 1 ");
			 if($check_production->num_rows>0)
			 {
			 	$data['productions']['production_id'] = $check_production->row['production_id'];
				$data['productions']['date_created'] = date($this->language->get('date_format_short'), strtotime($check_production->row['created_at']));
				$data['productions']['due_date'] = date($this->language->get('date_format_short'), strtotime($check_production->row['delivery_date']));
				$data['productions']['productionurl'] = $this->url->link('codevoc/b2bmanager_production.edit', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $check_production->row['production_id'], true);
			 }
			 else
			 {
				//get quotation file url
				$codevocb2bquotationfile = $this->model_codevoc_b2bmanager_order->getCodevocB2bQuotationfile($order_info['quotation_id']);
				if($codevocb2bquotationfile) { $data['codevocb2bquotationfile'] = $codevocb2bquotationfile;
				}else{$data['codevocb2bquotationfile'] = '';}
				//get quotation file url

				//$p_companyname = $this->request->get['companyname'];
				$data['addproductions']=$this->url->link('codevoc/b2bmanager_production.add', 'user_token=' . $this->session->data['user_token'] . $url.'&order_id='.$this->request->get['order_id'].'&order_date='.$order_info['date_added'].'&name='.$order_info['firstname'].' '.$order_info['lastname'].'&type=order&file='.$data['codevocb2bquotationfile'], true);
			 }
			 $check_purchase = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_purchase_order where order_id = '".$this->request->get['order_id']."'");
			 if($check_purchase->num_rows>0)
			 {
				$data['purchase']['purchase_id']=$check_purchase->row['purchase_id'];
				$data['purchase']['purchase_url']=$this->url->link('codevoc/b2bmanager_purchase.edit', 'user_token=' . $this->session->data['user_token'] . '&purchase_id=' . $check_purchase->row['purchase_id'], true);
				$data['purchase']['vendor']='';
				$data['purchase']['create_date']=date($this->language->get('date_format_short'), strtotime($check_purchase->row['created_at']));
			 }else{
				$data['purchase']['purchase_id']='';
				$data['purchase']['purchase_url']='';
				$data['purchase']['vendor']='';
				$data['purchase']['create_date']='';
			 }

			 $check_purchase_cost = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_order_costs where order_id = '".$this->request->get['order_id']."'");
			 if($check_purchase_cost->num_rows>0)
			 {
				$purchase_cost = 0;
				foreach($check_purchase_cost->rows as $cost){
					$purchase_cost += $cost['cost'];
				}
				$data['purchase']['cost']=$purchase_cost;
			 }else{
				$data['purchase']['cost']='';
			 }
			//addon point 5
			$this->load->model('customer/customer');
			$data['addresses'] = $this->model_customer_customer->getAddresses($order_info['customer_id']);

			$data['payment_address_id'] = $order_info['payment_address_id'];
			$data['payment_firstname'] = $order_info['payment_firstname'];
			$data['payment_lastname'] = $order_info['payment_lastname'];
			$data['payment_company'] = $order_info['payment_company'];
			$data['payment_address_1'] = $order_info['payment_address_1'];
			$data['payment_address_2'] = $order_info['payment_address_2'];
			$data['payment_city'] = $order_info['payment_city'];
			$data['payment_postcode'] = $order_info['payment_postcode'];
			$data['payment_country_id'] = $order_info['payment_country_id'];
			$data['payment_zone_id'] = $order_info['payment_zone_id'];
			$data['payment_custom_field'] = $order_info['payment_custom_field'];
			$data['payment_method'] = $order_info['payment_method'];
			$data['payment_methods'] = $this->getPaymentMethods();
			$data['payment_code'] = $order_info['payment_code'];

			$data['shipping_address_id'] = $order_info['shipping_address_id'];
			$data['shipping_firstname'] = $order_info['shipping_firstname'];
			$data['shipping_lastname'] = $order_info['shipping_lastname'];
			$data['shipping_company'] = $order_info['shipping_company'];
			$data['shipping_address_1'] = $order_info['shipping_address_1'];
			$data['shipping_address_2'] = $order_info['shipping_address_2'];
			$data['shipping_city'] = $order_info['shipping_city'];
			$data['shipping_postcode'] = $order_info['shipping_postcode'];
			$data['shipping_country_id'] = $order_info['shipping_country_id'];
			$data['shipping_zone_id'] = $order_info['shipping_zone_id'];
			$data['shipping_custom_field'] = $order_info['shipping_custom_field'];
			$data['shipping_method'] = $order_info['shipping_method'];
			$data['shipping_methods'] = $this->getShippingMethods();
			$data['shipping_code'] = $order_info['shipping_code'];

			//get fortnox invoice nr
			$codevocb2bfortnox = $this->model_codevoc_b2bmanager_order->getCodevocB2bFortnoxInvoice($this->request->get['order_id']);
			if($codevocb2bfortnox) { $data['codevocb2bfortnox'] = $codevocb2bfortnox;
			}else{$data['codevocb2bfortnox'] = '';}
			//get fortnox inovice nr

			// fortnox create manual invoice nr
			$data['codevocb2bfortnoxformAction'] = $this->url->link('codevoc/b2bmanager_order/createfortnoxinvoice', 'user_token=' . $this->session->data['user_token'] . $url, true);
		

			//get codevoc_b2b_order table data
			$data['codevocb2border']=array();

			$codevocb2border = $this->model_codevoc_b2bmanager_order->getCodevocB2bOrder($this->request->get['order_id']);
			if($codevocb2border)
			{
				$data['codevocb2border']['assignee']=$codevocb2border['assignee'];
				$data['codevocb2border']['vatnr']=$codevocb2border['vatnr'];

			}
			else
			{
				$data['codevocb2border']['assignee']='';
				$data['codevocb2border']['vatnr']='';
				$data['codevocb2border']['payment_company']= array();
				$data['codevocb2border']['shipping_company']= array();

			}
			//get codevoc_b2b_order table data

			// Products
			$this->load->model('catalog/product');
			$this->load->model('localisation/tax_rate');
			$data['order_products'] = array();

			$products = $this->model_codevoc_b2bmanager_order->getOrderProducts($this->request->get['order_id']);
			foreach ($products as $product) {
				$price_discount_percentage=0;
				$discount_percentage=0;
				$sort_order='';
				$tax_class_id=$product['tax_class_id'];
				$this->load->model('localisation/tax_class');
				$tax_rules = $this->model_localisation_tax_class->getTaxRules($tax_class_id);

				//get custom discoutn filds using new table
				$b2border_product=$this->model_codevoc_b2bmanager_order->getCodevocB2bOrderProducts($this->request->get['order_id'],$product['order_product_id']);
				if($b2border_product)
				{
					$price_discount_percentage=$b2border_product['discount'];
					$sort_order=$b2border_product['sort'];
					$tax_class_id=$b2border_product['tax_class_id'];
				}
				//get custom discoutn filds using new table

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				$ooption=array();
				$order_options=$this->model_codevoc_b2bmanager_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);
				foreach($order_options as $order_option)
				{

					$ooption[$order_option['product_option_id']]=$order_option['product_option_value_id'];
				}
				$product_options=$this->model_codevoc_b2bmanager_order->getProductOptionsSel($product['product_id'],$ooption);
				
				//v6
					$hiddenprice=$product['price'];
				//v6

				//discount price calculation
				if(array_key_exists('price',$product_info)){
					$org_price = $product_info['price'];
					$org_price_base = $product_info['price'];
				}else{
					$org_price = $product['price'];
					$org_price_base = $product['price'];
				}
				//discount price calculation

				//v5
				$productoptions=array();
				foreach($product_options as $poption)
				{
					$opprice='';
					foreach($poption['product_option_value'] as $opt)
					{

						$option_value = "";
							if($opt['selected']=='selected')
							{
								$opprice=$opt['price_prefix'].'_'.$opt['price'];
								//v6
									if ($opt['price_prefix'] == '+') {
										$hiddenprice -= $opt['price'];
										//discount price calculation
										$org_price += $opt['price'];
										//discount price calculation
									} elseif ($opt['price_prefix'] == '-') {
										$hiddenprice += $opt['price'];
										//discount price calculation
										$org_price -= $opt['price'];
										//discount price calculation
									}
									$option_value = $opt['option_value'];
								
								//v6
								break;
							}

					}
					$productoptions[] = array(
						'product_option_id'    => $poption['product_option_id'],
						'product_option_value' => $poption['product_option_value'],
						'option_id'            => $poption['option_id'],
						'name'                 => $poption['name'],
						'opprice'			   => $opprice,
						'type'                 => $poption['type'],
						'value'                => $option_value,
						'required'             => $poption['required']
					);


				}
				$product_options=$productoptions;

				$total_tax = 0;	
				foreach($tax_rules as $tax_rule){
					$tax_rates = $this->model_localisation_tax_rate->getTaxRate($tax_rule['tax_rate_id']);
					$total_tax = $total_tax + $tax_rates['rate'];
				}
				//v5
				$data['order_products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'=>$product_options,
					'quantity'   => $product['quantity'],
					'price'      => number_format($product['price'],2),
					'hiddenprice'=>number_format($hiddenprice,2,'.',''),
					'org_price'=>number_format($org_price,2,'.',''),//discount price calculation
					'org_price_base'=>number_format($org_price_base,2,'.',''),//discount price calculation
					'price_discount_percentage'=>number_format($price_discount_percentage,2),
					'sort_order'=>$sort_order,
					'tax' => $product['tax'],
					'total_tax_rate' => $total_tax,
					'tax_class_id' =>$tax_class_id,
					'total'      => number_format($product['total'],2),
					// 'reward'     => $product['reward']
				);
			}

			// Vouchers
			$data['order_vouchers'] = $this->model_codevoc_b2bmanager_order->getOrderVouchers($this->request->get['order_id']);

			$data['coupon'] = '';
			$data['voucher'] = '';
			$data['reward'] = '';

			$data['order_totals'] = array();

			$order_totals = $this->model_codevoc_b2bmanager_order->getOrderTotals($this->request->get['order_id']);

			foreach ($order_totals as $total) {
					$data['order_totals'][] = array(
						'title' => $total['title'],
						'text'  => number_format($total['value'],2),
						'code' => $total['code']
					);
				}
			$data['order_status_id'] = $order_info['order_status_id'];
			$data['comment'] = $order_info['comment'];
			$data['affiliate_id'] = $order_info['affiliate_id'];
			$data['affiliate'] = $order_info['affiliate_firstname'] . ' ' . $order_info['affiliate_lastname'];
			$data['currency_code'] = $order_info['currency_code'];
			// $this->load->model('codevoc/b2bmanager_unifaun');
			// $data['unifaun_pdfs'] = $this->model_codevoc_b2bmanager_unifaun->getUnifaunPdfs($this->request->get['order_id']);
			// $data['unifaun_pdf_link'] = $this->url->link('codevoc/b2bmanager_unifaun/viewPdf', 'user_token=' . $this->session->data['user_token'] , true);

			$this->load->model('codevoc/b2bmanager_purchase');
			$data['costs'] = $this->model_codevoc_b2bmanager_purchase->getPurchaseCosts($this->request->get['order_id']);
			$data['all_suppliers'] = $this->model_codevoc_b2bmanager_purchase->getAllSuppliers();

		} else {
			//addon point 5
			$data['addproductions']='';
			//addon point 5
			$data['order_id'] = 0;
			$data['store_id'] = 0;
			$data['store_url'] = $this->request->server['HTTPS'] ? HTTP_CATALOG : HTTP_CATALOG;
			$data['store_name'] = '';
			$data['customer'] = '';
			$data['customer_id'] = '';
			$data['customer_group_id'] = $this->config->get('config_customer_group_id');
			$data['firstname'] = '';
			$data['lastname'] = '';
			$data['email'] = '';
			$data['telephone'] = '';
			$data['customer_custom_field'] = array();
			$data['customerurl'] = '';
			$data['addresses'] = array();
			$data['codevocb2border']=array();
			//addon point 3
			$data['codevocb2border']['assignee']=$this->user->getId();
			//addon point 3
			$data['codevocb2border']['vatnr']='';
			$data['codevocb2border']['payment_company']= array();
			$data['codevocb2border']['shipping_company']= array();
			$data['payment_firstname'] = '';
			$data['payment_lastname'] = '';
			$data['payment_company'] = '';
			$data['payment_address_1'] = '';
			$data['payment_address_2'] = '';
			$data['payment_city'] = '';
			$data['payment_postcode'] = '';
			$data['payment_country_id'] = '';
			$data['payment_zone_id'] = '';
			$data['payment_custom_field'] = array();
			$data['payment_method'] = '';
			$data['payment_code'] = '';

			$data['shipping_firstname'] = '';
			$data['shipping_lastname'] = '';
			$data['shipping_company'] = '';
			$data['shipping_address_1'] = '';
			$data['shipping_address_2'] = '';
			$data['shipping_city'] = '';
			$data['shipping_postcode'] = '';
			$data['shipping_country_id'] = '';
			$data['shipping_zone_id'] = '';
			$data['shipping_custom_field'] = array();
			$data['shipping_method'] = '';
			$data['shipping_code'] = '';

			$data['order_products'] = array();
			$data['order_vouchers'] = array();
			$data['order_totals'] = array();

			$data['order_status_id'] = $this->config->get('config_order_status_id');
			$data['comment'] = '';
			$data['affiliate_id'] = '';
			$data['affiliate'] = '';
			$data['currency_code'] = $this->config->get('config_currency');

			$data['coupon'] = '';
			$data['voucher'] = '';
			$data['reward'] = '';
			$data['unifaun_pdfs'] = [];
			$data['costs'] = [];
			$data['all_suppliers'] = [];
			$data['shipment_details'] = '';
		}

		// Stores
		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			);
		}

		// Tax Classes
		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		// Customer Groups
		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		// Custom Fields
		$this->load->model('customer/custom_field');

		$data['custom_fields'] = array();

		$filter_data = array(
			'sort'  => 'cf.sort_order',
			'order' => 'ASC'
		);

		$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

		foreach ($custom_fields as $custom_field) {
			$data['custom_fields'][] = array(
				'custom_field_id'    => $custom_field['custom_field_id'],
				'custom_field_value' => $this->model_customer_custom_field->getValues($custom_field['custom_field_id']),
				'name'               => $custom_field['name'],
				'value'              => $custom_field['value'],
				'type'               => $custom_field['type'],
				'location'           => $custom_field['location'],
				'sort_order'         => $custom_field['sort_order']
			);
		}

		// System Users
		$this->load->model('user/user');
		$filter_data = array(
					'sort'  => 'username',
					'order' => 'ASC'
					);
		$data['system_users'] = $this->model_user_user->getUsers($filter_data);

		if($data['codevocb2border']['assignee']){
			$data['order_assignee'] = $this->model_user_user->getUser($data['codevocb2border']['assignee']);
		}
		if(array_key_exists('assignee',$quotation_others)){
			$data['quotation_assignee'] = $this->model_user_user->getUser($quotation_others['assignee']);
		}

		// System Users

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$data['voucher_min'] = $this->config->get('config_voucher_min');

		$this->load->model('sale/voucher_theme');

		$data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

		// API login
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTP_CATALOG : HTTP_CATALOG;

		// API login
		$this->load->model('user/api');

		$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'codevoc/b2bmanager_order')) {
			$session = new \Opencart\System\Library\Session($this->config->get('session_engine'), $this->registry);

			$session->start();

			$this->model_user_api->deleteSessionBySessionId($session->getId());

			$this->model_user_api->addSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('codevoc/b2bmanager_orderform', $data));
	}
	public function changeOrderType(){
		$this->load->model('codevoc/b2bmanager_order');
		$json['response'] = $this->model_codevoc_b2bmanager_order->changeOrderType($this->request->get['order_id'],$this->request->post['order_type']);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function call(): void {
		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		if (isset($this->request->get['language'])) {
			$language = $this->request->get['language'];
		} else {
			$language = $this->config->get('config_language');
		}

		if (isset($this->request->get['action'])) {
			$action = $this->request->get['action'];
		} else {
			$action = '';
		}

		if (isset($this->session->data['api_session'])) {
			$session_id = $this->session->data['api_session'];
		} else {
			$session_id = '';
		}

		if ($action) {
			// 1. Create a store instance using loader class to call controllers, models, views, libraries
			$this->load->model('setting/store');

			$store = $this->model_setting_store->createStoreInstance($store_id, $language, $session_id);

			// 2. Add the request vars and remove the unneeded ones
			$store->request->get = $this->request->get;
			$store->request->post = $this->request->post;

			$store->request->get['route'] = 'api/' . $action;

			// 3. Remove the unneeded keys
			unset($store->request->get['action']);
			unset($store->request->get['user_token']);

			// Call the required API controller
			$store->load->controller($store->request->get['route']);

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($store->response->getOutput());
		}
	}
	public function createAsanaTaskForOrder() {
		$order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : null;
		if($order_id) {
			$this->load->model('codevoc/b2bmanager_order');
			$order_info = $this->model_codevoc_b2bmanager_order->getOrder($order_id);
			$customer_name = $order_info['customer'];
			$customer_email = $order_info['email'];
			$customer_quotation_id = $order_info['quotation_id'];
			$task_title = "Order #$order_id  > $customer_name > #$customer_quotation_id ($customer_email)";
			$this->load->library('asana');
			$response = $this->asana->createTask($task_title);
		}

	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'codevoc/b2bmanager_order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}


	public function history() {
		$this->load->language('codevoc/b2bmanager_order');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$this->load->model('codevoc/b2bmanager_order');

		$results = $this->model_codevoc_b2bmanager_order->getOrderHistories($this->request->get['order_id'], ($page - 1) * 10, 10);

		$order_info = $this->model_codevoc_b2bmanager_order->getOrder($this->request->get['order_id']); 

		$assignee_id = $order_info['assignee'];

		// System Users
		$this->load->model('user/user');
		$filter_data = array(
					'sort'  => 'username',
					'order' => 'ASC'
					);
		$system_users = $this->model_user_user->getUsers($filter_data);
		if($assignee_id){
			foreach($system_users as $user){
				if($user['user_id'] == $assignee_id){
					$assignee_name = $user['username'];
					break;
				}
			}
		}else{
			$assignee_name = '';
		}

		foreach ($results as $result) {
			$data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'status'     => $result['status'],
				'user'       => $assignee_name,
				'comment'    => nl2br($result['comment']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_codevoc_b2bmanager_order->getTotalOrderHistories($this->request->get['order_id']);


		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $history_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('codevoc/b2bmanager_order.history', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->get['order_id'] . '&page={page}', true)
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_history', $data));
	}

	//for getting products autocomplete
	public function productsautocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/manufacturer');
			$this->load->model('codevoc/b2bmanager_order');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 15;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_codevoc_b2bmanager_order->getFindproducts($filter_data);

			foreach ($results as $result)
			{
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);

				if ($manufacturer_info)
				{
					$manufacturer = $manufacturer_info['name'];
				}
				else
				{
					$manufacturer = '';
				}

				$productstr='';
				if($manufacturer!='')
				{
					$productstr='['.$result['model'].'] > '.' ['.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).'] - ['.$manufacturer.']';
				}
				else
				{
					$productstr='['.$result['model'].'] > '.' ['.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).']';
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name' =>$productstr,
					'model'      => $result['model'],
					'product_name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	//for getting products autocomplete

	//getting single product
	public function loadproduct()
	{
		$json=array();
		$this->load->model('catalog/product');
		$this->load->model('codevoc/b2bmanager_order');
		//v4
		$this->load->model('customer/customer');
		$discounts = $this->model_catalog_product->getDiscounts($this->request->get['product_id']);
		$customer_id = (isset($this->request->get['customer_id']) && !empty($this->request->get['customer_id'])) ? $this->request->get['customer_id'] : '';
		//v4
		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

		$options = $this->model_codevoc_b2bmanager_order->getProductOptions($this->request->get['product_id']);
		$json['options'] = $options;

		if (!empty($product_info))
		{

			//$json['price']  = $product_info['price']; discount price calculation OLD CODE

			//discount price calculation
			$query_price = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$this->request->get['product_id'] . "'");
			$json['price']  = $query_price->row['price'];
			$discount_price=0.00;
			$original_price  = $query_price->row['price'];
			$json['oprice']  = $query_price->row['price'];
			$founddiscount=0;
		  	//discount price calculation


			//v4
					 $discount_val = 0.0;
					 if($customer_id != "")
					 {
					  	$customer_info = $this->model_customer_customer->getCustomer($customer_id);
						foreach ($discounts as $discount)
						{
                    		if (intval($this->request->get['quantity']) >= intval($discount['quantity'])&& $discount['customer_group_id'] == $customer_info['customer_group_id'])
							{
                      		   //$json['price'] = $discount['price']; discount price calculation OLD CODE
							   //discount price calculation
							   $founddiscount=1;
							   $discount_price=$discount['price'];
							   //discount price calculation
                    		}
                		}
					}
			//v4
			//discount price calculation
			if($discount_price>0  && $founddiscount>0)
			{
				$json['price_discount_percentage']=($original_price-$discount_price)/$original_price*100;
			}
			else
			{
				$json['price_discount_percentage'] =$discount_val;
			}

			$this->load->model('localisation/tax_class');
			$tax_rules = $this->model_localisation_tax_class->getTaxRules($product_info['tax_class_id']);
			$this->load->model('localisation/tax_rate');
			$total_tax = 0;	
			foreach($tax_rules as $tax_rule){
				$tax_rates = $this->model_localisation_tax_rate->getTaxRate($tax_rule['tax_rate_id']);
				$tax_rate_info[] = array(
					'name'  => $tax_rates['name'],
					'rate'  => $tax_rates['rate'],
					'based' => $tax_rule['based']
				);
				$total_tax = $total_tax + $tax_rates['rate'];
			}
			$json['total_tax'] = $total_tax;
			//discount price calculation
			$json['tax_class_id']  = $product_info['tax_class_id'];
		}



		if($product_info){$json['success']=1;}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	//getting single product

	//get product option value price v4
	public function getpoprice()
	{
		$json=array();

		//discount price calculation
		$this->load->model('catalog/product');
		$this->load->model('codevoc/b2bmanager_quotation');
		$this->load->model('customer/customer');
		$discounts = $this->model_catalog_product->getDiscounts($this->request->get['product_id']);
		$customer_id = (isset($this->request->get['customer_id']) && !empty($this->request->get['customer_id'])) ? $this->request->get['customer_id'] : '';
		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		$discount_price=0.00;
		$foundiscount=0;
		$query_price = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$this->request->get['product_id'] . "'");
		$original_price  = $query_price->row['price'];

		 			 $discount_val = 0.0;
					 if($customer_id != "")
					 {
					  	$customer_info = $this->model_customer_customer->getCustomer($customer_id);
						foreach ($discounts as $discount)
						{
                    		if (intval($this->request->get['quantity']) >= intval($discount['quantity'])&& $discount['customer_group_id'] == $customer_info['customer_group_id'])
							{
                      		  $discount_price=$discount['price'];
							  $foundiscount=1;
							}
                		}
					}
		if(isset($this->request->post['order_products']))
		{
				if(array_key_exists($this->request->get['rowcount'],$this->request->post['order_products']))
				{
					if(array_key_exists('option',$this->request->post['order_products'][$this->request->get['rowcount']]))
					{
						foreach($this->request->post['order_products'][$this->request->get['rowcount']]['option'] as $option_s)
						{
							$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "product_option_value where product_option_value_id = '".$option_s."'");
							if($query->num_rows>0)
			 				{

									if ($query->row['price_prefix'] == '+') {
										$original_price +=$query->row['price'];
										$discount_price +=$query->row['price'];
									} elseif ($query->row['price_prefix'] == '-') {
										$original_price -=$query->row['price'];
										$discount_price -=$query->row['price'];
									}

							}

						}
					}
				}
		}
		//discount price calculation

		if(isset($this->request->get['product_option_value_id']))
		{
			 $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "product_option_value where product_option_value_id = '".$this->request->get['product_option_value_id']."'");
			 if($query->num_rows>0)
			 {

									if ($query->row['price_prefix'] == '+') {
										$option_price = '+_'.$query->row['price'];
									} elseif ($query->row['price_prefix'] == '-') {
										$option_price = '-_'.$query->row['price'];
									}
					$json['success']=$option_price;
			}
			else
			{
				$json['success']='';
			}
		}
		else
		{
			$json['success']='';
		}
		//discount price calculation
		if($discount_price>0 && $foundiscount>0)
		{
			$json['price_discount_percentage']=($original_price-$discount_price)/$original_price*100;
		}
		else
		{
			$json['price_discount_percentage'] =$discount_price;
		}
		//discount price calculation
		$this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
	}
	//get product option value price v4

	//save custom filds assigne and vatnr
    public function b2borderdata()
	{
			$json=array();
			$json['success']=0;
			if(isset($this->request->get['order_id']))
			{
				$assignee='';$vatnr='';
				if(isset($this->request->post['assignee']))
				{
						$assignee=$this->request->post['assignee'];
				}
				if(isset($this->request->post['vatnr']))
				{
						$vatnr=$this->request->post['vatnr'];
				}

				$this->load->model('codevoc/b2bmanager_order');
				$order_id=$this->request->get['order_id'];
				$order_data=$this->model_codevoc_b2bmanager_order->getOrderCustomeFilds($order_id);
				if($order_data)
				{
					$shipping_custom_field=$order_data['shipping_custom_field'];
					$payment_custom_field=$order_data['payment_custom_field'];
					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_b2b_order` WHERE order_id = '" . (int)$order_id . "'");
					if($query->num_rows>0)
					{
						$this->db->query("UPDATE  `" . DB_PREFIX . "codevoc_b2b_order` set assignee='".$assignee."',vatnr='".$this->db->escape($vatnr)."',payment_company='".$this->db->escape($payment_custom_field)."',shipping_company='".$this->db->escape($shipping_custom_field)."' WHERE order_id = '" . (int)$order_id . "'");
					}
					else
					{
					     $this->db->query("INSERT into `" . DB_PREFIX . "codevoc_b2b_order` set order_id = '" . (int)$order_id . "',assignee='".$assignee."',vatnr='".$this->db->escape($vatnr)."',payment_company='".$this->db->escape(@$payment_custom_field)."',shipping_company='".$this->db->escape(@$shipping_custom_field)."'");
					}
					$json['success']=1;
				}
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}
	//save custom filds assigne and vatnr

	//addon point 1
	public function getCustomerData() {
		$json = array();

		if (isset($this->request->get['customer_id']) || isset($this->request->get['customer_id'])) {
			if (isset($this->request->get['customer_id'])) {
				$customer_id = $this->request->get['customer_id'];
			} else {
				$customer_id = '';
			}


			$this->load->model('customer/customer');


			$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "' and c.customer_id='".$customer_id."'";
			$query = $this->db->query($sql);
				if($query->num_rows>0)
				{
					 	$result = $query->row;
						$json['customer_id']       = $result['customer_id'];
						$json['customer_group_id'] = $result['customer_group_id'];
						$json['name']              = strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'));
						$json['customer_group']    = $result['customer_group'];
						$json['firstname']         = $result['firstname'];
						$json['lastname']          = $result['lastname'];
						$json['email']             = $result['email'];
						$json['telephone']         = $result['telephone'];
						$json['custom_field']      = json_decode($result['custom_field'], true);
						$json['address']           = $this->model_customer_customer->getAddresses($result['customer_id']);

				}

		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	//addon point 1
	public function purchaseorder() {
		$this->load->language('sale/order');

		$data['title'] = $this->language->get('text_shipping');

		if (isset($this->request->get['request_type'])) {
			$data['request_type'] = $this->request->get['request_type'];
		}

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$this->load->model('sale/order');

		$this->load->model('catalog/product');

		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}
		foreach ($orders as $order_id) {
			$order_info = $this->model_sale_order->getOrder($order_id);
			// Make sure there is a shipping method
			if ($order_info && $order_info['shipping_method']) {
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				if ($store_info) {
					$store_address = $store_info['config_address'];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
				}

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}
				if ($order_info['payment_address_format']) {
					#$format = $order_info['payment_address_format'];
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{postcode} {city}';
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{postcode} {city}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => '',
					'country'   => ''
				);

				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				if ($order_info['shipping_address_format']) {
					#$format = $order_info['shipping_address_format'];
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{postcode} {city}';
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{postcode} {city}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => '',
					'country'   => ''
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();
				$this->load->language('sale/order');
				$products = $this->model_sale_order->getProducts($order_id);
				if($products){
					foreach ($products as $product) {
						$option_weight = '';
						$voucher_data = array();
						$total_data = array();
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
	
						if ($product_info) {
							$option_data = array();
	
							$options = $this->model_sale_order->getOptions($order_id, $product['order_product_id']);
	
							foreach ($options as $option) {
								if ($option['type'] != 'file') {
									$value = $option['value'];
								} else {
									$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
	
									if ($upload_info) {
										$value = $upload_info['name'];
									} else {
										$value = '';
									}
								}
	
								$option_data[] = array(
									'name'  => $option['name'],
									'value' => $value
								);
	
								$product_option_value_info = $this->model_catalog_product->getProductOptionValue($product['product_id'], $option['product_option_value_id']);
	
								// if ($product_option_value_info) {
								// 	if ($product_option_value_info['weight_prefix'] == '+') {
								// 		$option_weight += $product_option_value_info['weight'];
								// 	} elseif ($product_option_value_info['weight_prefix'] == '-') {
								// 		$option_weight -= $product_option_value_info['weight'];
								// 	}
								// }
							}
	
							$product_data[] = array(
								'name'     => $product_info['name'],
								'model'    => $product_info['model'],
								'option'   => $option_data,
								'quantity' => $product['quantity'],
								'location' => $product_info['location'],
								'sku'      => $product_info['sku'],
								'upc'      => $product_info['upc'],
								'ean'      => $product_info['ean'],
								'jan'      => $product_info['jan'],
								'isbn'     => $product_info['isbn'],
								'mpn'      => $product_info['mpn'],
								'weight'   => $this->weight->format(($product_info['weight'] + (float)$option_weight) * $product['quantity'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point')),
								'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
								'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
							);
	
							$vouchers = $this->model_sale_order->getVouchers($order_id);
	
							foreach ($vouchers as $voucher) {
							  $voucher_data[] = array(
								'description' => $voucher['description'],
								'amount'      => $voucher['amount']
							  );
							}
	
							$totals = $this->model_sale_order->getTotals($order_id);
	
							foreach ($totals as $total) {
							  $total_data[] = array(
								'title' => $total['title'],
								'text'  => $total['value']
							  );
							}
	
						}	
					}
				}else{
						$voucher_data = '';
						$total_data = '';
				}

				$data['orders'][] = array(
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_pdf'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'quotation_id'     => $order_info['quotation_id'],
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'payment_address'  => $payment_address,
					'payment_method'   => $order_info['payment_method'],
					'product'          => $product_data,
					'voucher'          => ($voucher_data) ? $voucher_data : '',
					'total'            => ($total_data) ? $total_data : '',
					'comment'          => nl2br($order_info['comment'])
				);
			}
		}
		

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_purchaseorder', $data));
	}

	public function updateOrderStatus() {
		if(isset($this->request->get['order_id'])){

			$order_id = $this->request->get['order_id'];
			$status = $this->request->post['status'];

			$this->load->model('codevoc/b2bmanager_order');
			$this->model_codevoc_b2bmanager_order->updateOrderStatus($order_id, $status);
		}
	}

	public function updateAssignee() {
		if(isset($this->request->get['order_id'])){

			$order_id = $this->request->get['order_id'];
			$assignee_id = $this->request->post['assignee_id'];

			$this->load->model('codevoc/b2bmanager_order');
			$this->model_codevoc_b2bmanager_order->updateAssignee($order_id, $assignee_id);
		}
	}

	public function deleteOrder() {
		if(isset($this->request->get['order_id'])){

			$order_id = $this->request->get['order_id'];

			$this->load->model('codevoc/b2bmanager_order');
			$this->model_codevoc_b2bmanager_order->deleteOrder($order_id);
		}
	}
	public function updateOrderType() {
		if(isset($this->request->get['order_id'])){
			$order_id = $this->request->get['order_id'];
			$status = $this->request->post['status'];
			$this->load->model('codevoc/b2bmanager_order');
			$this->model_codevoc_b2bmanager_order->updateOrderType($order_id, $status);
		}
	}
	// public function createfortnoxinvoice() {
	// 	$url .= '&order_id=' . $this->request->post['id'];
	// 	$orderid = $this->request->post['id'];
	// 	$invoicenr = $this->request->post['invoicenr'];
	// 	#print $invoicenr; exit;
	// 	$this->load->model('codevoc/b2bmanager_order');
	// 	$this->model_codevoc_b2bmanager_order->createFortnoxInvoicenr($invoicenr, $orderid);
	// 	$this->response->redirect($this->url->link('codevoc/b2bmanager_order.edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
	// }	
	public function getPaymentMethods() {
		$this->load->language('extension/payment');

		$available = [];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getPaths('%/admin/controller/payment/%.php');

		foreach ($results as $result) {
			$available[] = basename($result['path'], '.php');
		}

		$installed = [];

		$extensions = $this->model_setting_extension->getExtensionsByType('payment');

		foreach ($extensions as $extension) {
			if (in_array($extension['code'], $available)) {
				$installed[] = $extension['code'];
			} else {
				$this->model_setting_extension->uninstall('payment', $extension['code']);
			}
		}

		$data['extensions'] = [];

		if ($results) {
			foreach ($results as $result) {
				$extension = substr($result['path'], 0, strpos($result['path'], '/'));

				$code = basename($result['path'], '.php');

				$this->load->language('extension/' . $extension . '/payment/' . $code, $code);

				$text_link = $this->language->get($code . '_text_' . $code);

				if ($text_link != $code . '_text_' . $code) {
					$link = $text_link;
				} else {
					$link = '';
				}

				$data['extensions'][] = [
					'name'       => $this->language->get($code . '_heading_title'),
					'link'       => $link,
					'status'     => $this->config->get('payment_' . $code . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('payment_' . $code . '_sort_order'),
					'install'    => $this->url->link('extension/payment.install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'uninstall'  => $this->url->link('extension/payment.uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'installed'  => in_array($code, $installed),
					'code'		 => $code,
					'edit'       => $this->url->link('extension/' . $extension . '/payment/' . $code, 'user_token=' . $this->session->data['user_token'])
				];
			}
		}

		return $data['extensions'];
	}
	public function getShippingMethods(){

		$this->load->language('extension/shipping');
		$this->load->model('setting/extension');
		$results = $this->model_setting_extension->getPaths('%/admin/controller/shipping/%.php');

		foreach ($results as $result) {
			$available[] = basename($result['path'], '.php');
		}

		$extensions = $this->model_setting_extension->getExtensionsByType('shipping');

		foreach ($extensions as $extension) {
			if (in_array($extension['code'], $available)) {
				$installed[] = $extension['code'];
			} else {
				$this->model_setting_extension->uninstall('shipping', $extension['code']);
			}
		}
		$data['extensions'] = [];

		if ($results) {
			$this->load->model('setting/setting');
			foreach ($results as $result) {
				$extension = substr($result['path'], 0, strpos($result['path'], '/'));

				$code = basename($result['path'], '.php');

				$this->load->language('extension/' . $extension . '/shipping/' . $code, $code);

				$shipping_cost = $this->model_setting_setting->getSetting('shipping_'.$code);
				if(array_key_exists('shipping_'.$code.'_cost',$shipping_cost)){
					$cost = $shipping_cost['shipping_'.$code.'_cost'];
					$tax_class_id = $shipping_cost['shipping_'.$code.'_tax_class_id'];
				}else{
					$cost = '';
					$tax_class_id = '';
				}

				$this->load->model('localisation/tax_class');
				$tax_rules = $this->model_localisation_tax_class->getTaxRules((int)$tax_class_id);
				$this->load->model('localisation/tax_rate');
				$total_tax = 0;	
				foreach($tax_rules as $tax_rule){
					$tax_rates = $this->model_localisation_tax_rate->getTaxRate($tax_rule['tax_rate_id']);
					$total_tax = $total_tax + $tax_rates['rate'];
				}

				$cost_with_tax = (float)$cost + ((float)$cost*(float)$total_tax/100); 

				$data['extensions'][] = [
					'name'       => $this->language->get($code . '_heading_title'),
					'status'     => $this->config->get('shipping_' . $code . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('shipping_' . $code . '_sort_order'),
					'install'    => $this->url->link('extension/shipping.install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'uninstall'  => $this->url->link('extension/shipping.uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'installed'  => in_array($code, $installed),
					'code'		 => $code,
					'cost'		 => $cost,
					'total_cost' => $cost_with_tax,
					'tax_rate'	 => $total_tax,	
					'edit'       => $this->url->link('extension/' . $extension . '/shipping/' . $code, 'user_token=' . $this->session->data['user_token'])
				];
			}
		}
        return $data['extensions'];

	}

	public function editorder(){
		//custom data
		$order_data = array();
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if(isset($this->request->post['assignee'])){$order_data['assignee']=$this->request->post['assignee'];}else{$order_data['assignee']='';}
		if(isset($this->request->post['vatnr'])){$order_data['vatnr']=$this->request->post['vatnr'];}else{$order_data['vatnr']='';}
		if(isset($this->request->post['payment_company'])){$order_data['payment_company']=$this->request->post['payment_company'];}else{$order_data['payment_company']='';}
		if(isset($this->request->post['shipping_company'])){$order_data['shipping_company']=$this->request->post['shipping_company'];}else{$order_data['shipping_company']='';}
		if(isset($this->request->post['create_date'])&& $this->request->post['create_date'] != null){$order_data['create_date']=date("Y-m-d",strtotime($this->request->post['create_date']));}else{$order_data['create_date']=date("Y-m-d");}
		if(isset($this->request->post['expiration_date'])&& $this->request->post['expiration_date'] != null){$order_data['expiration_date']=date("Y-m-d",strtotime($this->request->post['expiration_date']));}else{$order_data['expiration_date']=date("Y-m-d");}
		if(isset($this->request->post['shippment_terms'])){$order_data['shippment_terms']=$this->request->post['shippment_terms'];}else{$order_data['shippment_terms']='';}
		if(isset($this->request->post['rate_delay'])){$order_data['rate_delay']=$this->request->post['rate_delay'];}else{$order_data['rate_delay']='';}
		if(isset($this->request->post['custom_ordernr'])){$order_data['custom_ordernr']=$this->request->post['custom_ordernr'];}else{$order_data['custom_ordernr']='';}

		$order_data['customer_id'] = $this->request->post['customer_id'];
		$order_data['vatnr'] = isset($this->request->post['vatnr']) ? $this->request->post['vatnr'] : '' ;
		$order_data['firstname'] = $this->request->post['input_firstname'];
		$order_data['lastname'] = $this->request->post['input_lastname'];
		$order_data['email'] = $this->request->post['email'];
		$order_data['telephone'] = $this->request->post['telephone'];
		$order_data['custom_field'] = $this->request->post['custom_field'];
		if(array_key_exists('payment_address',$this->request->post) && $this->request->post['payment_address'] != 0){
			$order_data['payment_address_id'] = $this->request->post['payment_address'];
			$this->load->model('customer/customer');
			$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			$order_data['payment_lastname'] = $this->request->post['input_payment_lastname'];
			$order_data['payment_company'] = $this->request->post['input_payment_company'];
			$order_data['payment_address_1'] = $this->request->post['input_payment_address_1'];
			$order_data['payment_address_2'] = $this->request->post['input_payment_address_2'];
			$order_data['payment_city'] = $this->request->post['input_payment_city'];
			$order_data['payment_postcode'] = $this->request->post['input_payment_postcode'];
			$order_data['payment_custom_field'] = $this->request->post['payment_custom_field'];
			$this->load->model('localisation/zone');
			$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
			$order_data['payment_zone'] = $payment_zone['name'];
			$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
			$this->load->model('localisation/country');
			$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
			$order_data['payment_country'] = $payment_country['name'];
			$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
		}else{
			$order_data['payment_address_id'] = '';
			if(isset($this->request->post['input_payment_firstname'])){
				$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			}else{
				$order_data['payment_firstname'] = '';
			}
			if(isset($this->request->post['input_payment_firstname'])){
				$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			}else{
				$order_data['payment_firstname'] = '';
			}
			if(isset($this->request->post['input_payment_lastname'])){
				$order_data['payment_lastname'] = $this->request->post['input_payment_lastname'];
			}else{
				$order_data['payment_lastname'] = '';
			}
			if(isset($this->request->post['input_payment_company'])){
				$order_data['payment_company'] = $this->request->post['input_payment_company'];
			}else{
				$order_data['payment_company'] = '';
			}
			if(isset($this->request->post['input_payment_address_1'])){
				$order_data['payment_address_1'] = $this->request->post['input_payment_address_1'];
			}else{
				$order_data['payment_address_1'] = '';
			}
			if(isset($this->request->post['input_payment_address_2'])){
				$order_data['payment_address_2'] = $this->request->post['input_payment_address_2'];
			}else{
				$order_data['payment_address_2'] = '';
			}
			if(isset($this->request->post['input_payment_city'])){
				$order_data['payment_city'] = $this->request->post['input_payment_city'];
			}else{
				$order_data['payment_city'] = '';
			}
			if(isset($this->request->post['input_payment_postcode'])){
				$order_data['payment_postcode'] = $this->request->post['input_payment_postcode'];
			}else{
				$order_data['payment_postcode'] = '';
			}
			if(array_key_exists("input_payment_zone_id",$this->request->post)){
				$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
				$this->load->model('localisation/zone');
				$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
				$order_data['payment_zone'] = $payment_zone['name'];
			}else{
				$order_data['payment_zone_id'] = '';
				$order_data['payment_zone'] = '';
			}
			if($this->request->post['input_payment_country_id'] != ''){
				$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
				$this->load->model('localisation/country');
				$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
				$order_data['payment_country'] = $payment_country['name'];
			}else{
				$order_data['payment_country_id'] = '';
				$order_data['payment_country'] = '';
			}			
			if(isset($this->request->post['payment_custom_field'])){
				$order_data['payment_custom_field'] = $this->request->post['payment_custom_field'];
			}else{
				$order_data['payment_custom_field'] = '';
			}
		}
		if(array_key_exists('shipping_address',$this->request->post) && $this->request->post['shipping_address'] != 0){
			$order_data['shipping_address_id'] = $this->request->post['shipping_address'];
			$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			$order_data['shipping_lastname'] = $this->request->post['input_shipping_lastname'];
			$order_data['shipping_company'] = $this->request->post['input_shipping_company'];
			$order_data['shipping_address_1'] = $this->request->post['input_shipping_address_1'];
			$order_data['shipping_address_2'] = $this->request->post['input_shipping_address_2'];
			$order_data['shipping_city'] = $this->request->post['input_shipping_city'];
			$order_data['shipping_postcode'] = $this->request->post['input_shipping_postcode'];
			$order_data['shipping_custom_field'] = $this->request->post['shipping_custom_field'];
			$this->load->model('localisation/zone');
			$shipping_zone = $this->model_localisation_zone->getZone($this->request->post['input_shipping_country_id']);
			$order_data['shipping_zone'] = $shipping_zone['name'];
			$order_data['shipping_zone_id'] = $this->request->post['input_shipping_zone_id'];
			$this->load->model('localisation/country');
			$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_shipping_country_id']);
			$order_data['shipping_country'] = $payment_country['name'];
			$order_data['shipping_country_id'] = $this->request->post['input_shipping_country_id'];
		}else{
			$order_data['shipping_address_id'] = '';
			if(isset($this->request->post['input_shipping_firstname'])){
				$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			}else{
				$order_data['shipping_firstname'] = '';
			}
			if(isset($this->request->post['input_shipping_firstname'])){
				$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			}else{
				$order_data['shipping_firstname'] = '';
			}
			if(isset($this->request->post['input_shipping_lastname'])){
				$order_data['shipping_lastname'] = $this->request->post['input_shipping_lastname'];
			}else{
				$order_data['shipping_lastname'] = '';
			}
			if(isset($this->request->post['input_shipping_company'])){
				$order_data['shipping_company'] = $this->request->post['input_shipping_company'];
			}else{
				$order_data['shipping_company'] = '';
			}
			if(isset($this->request->post['input_shipping_address_1'])){
				$order_data['shipping_address_1'] = $this->request->post['input_shipping_address_1'];
			}else{
				$order_data['shipping_address_1'] = '';
			}
			if(isset($this->request->post['input_shipping_address_2'])){
				$order_data['shipping_address_2'] = $this->request->post['input_shipping_address_2'];
			}else{
				$order_data['shipping_address_2'] = '';
			}
			if(isset($this->request->post['input_shipping_city'])){
				$order_data['shipping_city'] = $this->request->post['input_shipping_city'];
			}else{
				$order_data['shipping_city'] = '';
			}
			if(isset($this->request->post['input_shipping_postcode'])){
				$order_data['shipping_postcode'] = $this->request->post['input_shipping_postcode'];
			}else{
				$order_data['shipping_postcode'] = '';
			}
			if(array_key_exists("input_shipping_zone_id",$this->request->post)){
				$order_data['shipping_zone_id'] = $this->request->post['input_shipping_zone_id'];
				$this->load->model('localisation/zone');
				$shipping_zone = $this->model_localisation_zone->getZone($this->request->post['input_shipping_zone_id']);
				$order_data['shipping_zone'] = $shipping_zone['name'];
			}else{
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_zone'] = '';
			}
			if($this->request->post['input_shipping_country_id'] != ''){
				$order_data['shipping_country_id'] = $this->request->post['input_shipping_country_id'];
				$this->load->model('localisation/country');
				$shipping_country = $this->model_localisation_country->getCountry($this->request->post['input_shipping_country_id']);
				$order_data['shipping_country'] = $shipping_country['name'];
			}else{
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_country'] = '';
			}
			if(isset($this->request->post['shipping_custom_field'])){
				$order_data['shipping_custom_field'] = $this->request->post['shipping_custom_field'];
			}else{
				$order_data['shipping_custom_field'] = '';
			}
		}
		if (isset($this->request->post['shipping_method'])) {
			$order_data['shipping_method'] = $this->request->post['shipping_method'];
		} else {	
			$order_data['shipping_method'] = '';
		}
		$order_data['order_status_id'] = $this->request->post['order_status_id'];
		$order_data['file_final_sketch'] = $this->request->post['file_final_sketch'];
		$order_data['total'] = str_replace(',','',$this->request->post['total']);
		if (isset($this->request->post['payment_method'])) {
			$order_data['payment_method'] = $this->request->post['payment_method'];
		} else {	
			$order_data['payment_method'] = '';
		}
		$order_data['products'] = array();
		$this->load->model('codevoc/b2bmanager_order');
		foreach ($this->request->post['order_products'] as $product) {
			$option_data = array();
			
			$product_info = $this->model_codevoc_b2bmanager_order->getProductOptions($product['product_id']);
			
			foreach ($product_info as $option) {
				if(array_key_exists($option['product_option_id'],$product['option'])){	
					foreach($option['product_option_value'] as $product_option_value){
						if(array_key_exists($option['product_option_id'],$product['option']) && array_key_exists("product_option_value_id",$product_option_value) && $product['option'][$option['product_option_id']] == $product_option_value['product_option_value_id'] ){
							$product_option_value_id = $product_option_value['product_option_value_id'];
							$option_value_id = $product_option_value['option_value_id'];
						}else{
							$product_option_value_id = $product['option'][$option['product_option_id']];
							$option_value_id = $product_option_value['option_value_id'];
						}
					}
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $product_option_value_id,
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option_value_id,
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}
			}
			

			if($product['name']){
				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					// 'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					// 'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'discount'=> $product['price_discount_percentage'],//B2B Manager
					// 'b2b_product_tax_class_id'=> $product['b2b_product_tax_class_id'],//B2B Manager
					'b2b_product_sort_order'=> $product['sort_order'],//B2B Manager
					'total'      => $product['total'],
					'tax'        => $product['total_tax_to_prod'],
					'tax_class_id'=> $product['tax_class_id']
					// 'reward'     => $product['reward']
				);
			}
		}

				//attachment
		// Order Totals
		$order_data['totals']['sub_total'] = array(
			'code'  => 'sub_total',
			'title' => 'Sub-Total',
			'value' => str_replace(",","",$this->request->post['sub_total']),
			'sort_order' => 1
		); 

		$order_data['totals']['shipping'] = array(
			'code'  => 'shipping_cost',
			'title' => 'Shipping',
			'value' =>  str_replace(",","",$this->request->post['shipping_cost']),
			'sort_order' => 3
		);

		$order_data['totals']['tax'] = array(
			'code'  => 'tax',
			'title' => 'Tax',
			'value' => str_replace(",","",$this->request->post['tax']),
			'sort_order' => 5
		); 

		$order_data['totals']['total'] = array(
			'code'  => 'total',
			'title' => 'Total',
			'value' => str_replace(",","",$this->request->post['total']),
			'sort_order' => 9
		); 
		$json['order_id'] = $this->model_codevoc_b2bmanager_order->editOrder($order_id, $order_data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getCarrierName($code){

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://app.shipmondo.com/api/public/v3/carriers?sender_country_code=SE&receiver_country_code=SE',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic NmY2Y2VkOTktYTFmOC00YmQ3LWJiNzEtYTAwMjhjYjQyNzdhOmQ5Nzg2OGFlLWRkMjctNDQ1My04YjU5LTAxMTgyN2E2MDgyYQ=='
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$carriers = json_decode($response);
		foreach($carriers as $car){
			if($car->code == $code){
				return $car->name;
			}else{
				return '';
			}
		}

	}
	public function getShipmondoProduct($code){
		$url = 'https://app.shipmondo.com/api/public/v3/products?sender_country_code=SE&receiver_country_code=SE&product_code='.$code;
	
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic NmY2Y2VkOTktYTFmOC00YmQ3LWJiNzEtYTAwMjhjYjQyNzdhOmQ5Nzg2OGFlLWRkMjctNDQ1My04YjU5LTAxMTgyN2E2MDgyYQ=='
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode($response);

	}

	public function getPackageType($productType){
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://app.shipmondo.com/api/public/v3/package_types?product_code='.$productType,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic NmY2Y2VkOTktYTFmOC00YmQ3LWJiNzEtYTAwMjhjYjQyNzdhOmQ5Nzg2OGFlLWRkMjctNDQ1My04YjU5LTAxMTgyN2E2MDgyYQ=='
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode($response);

	}
	public function getShipment(){
		$order_id = $this->request->get['order_id'];
		$this->load->model('codevoc/b2bmanager_shipmondo');
		$shipmentDetails = $this->model_codevoc_b2bmanager_shipmondo->getShipmentDetails($order_id);
		if(count($shipmentDetails) > 0){
			foreach($shipmentDetails as $shipment){
				$product = $this->getShipmondoProduct($shipment['product_code']);
				$services = $product[0]->available_services;
				$addons_db = explode(",",$shipment['service_codes']);
				$packages = $this->getPackageType($shipment['product_code']);
				foreach($packages as $pack){
					if($pack->code == $shipment['package_type']){
						$package = $pack->description;
					}
				}
				$addons = array();
				foreach($services as $service){
					if(in_array($service->code, $addons_db)){
						$addons[] = $service->name; 
					}
				}
				$data['shipment_details'][] = array(
					'id'			 => $shipment['id'],
					'shipment_id'    => $shipment['shipment_id'],
					'carrier'        => $this->getCarrierName($shipment['carrier_code']),
					'weight'         => $shipment['weight'],
					'quantity'       => $shipment['quantity'],
					'date_created'   => date('Y-m-d',strtotime($shipment['created_at'])),
					'product_type'   => $product[0]->name,
					'service_addons' => $addons,
					'package_type'   => $package
				);
			}
			
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
	public function sendmail_delivery() {
		#send delivery mail to client
		$order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : null;
		$email = isset($this->request->get['email']) ? $this->request->get['email'] : null;
		if($email == null){
			$this->load->model('codevoc/b2bmanager_order');
			$order_info = $this->model_codevoc_b2bmanager_order->getOrder($order_id);
			$email = $order_info['email'];
		}
		if($order_id) {
				$data['order_id'] = $order_id;
				$mail = new SendgridapiMail('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');
				$mail->setTo($email);
				$mail->setFrom('hej@profilewear.se');
				$mail->setReplyTo('hej@profilewear.se');
				$mail->setBcc('profilewear@biz.reco.se');
				$mail->setSender(html_entity_decode('Profilewear.se', ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode('Profilewear.se - Din order är på väg #'.$order_id, ENT_QUOTES, 'UTF-8'));
				$mail->setHtml($this->load->view('codevoc/b2bmanager_mail_delivery', $data));
				$mail->send();

		}
	}
	public function sendUpdateMail(){
		$order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : null;

		$this->load->model('codevoc/b2bmanager_order');
		$order_info = $this->model_codevoc_b2bmanager_order->getOrder($order_id);
		$email = $order_info['email'];
		$data = $order_info;
		$data['date_added'] = date('Y-m-d', strtotime($order_info['date_added']));
		$products = $this->model_codevoc_b2bmanager_order->getOrderProducts($order_id);
		foreach($products as $product){
			$option = $this->model_codevoc_b2bmanager_order->getOrderOptions($order_id,$product['order_product_id']);
			$data['order_products'][] = array(
				'product_id'   => $product['product_id'],
				'name'         => $product['name'],
				'model'        => $product['model'],
				'quantity'     => $product['quantity'],
				'option'       => $option,
				'price'        => number_format($product['price'],2),
				'total'        => number_format($product['total'],2)
			);
		}

		$order_totals = $this->model_codevoc_b2bmanager_order->getOrderTotals($order_id);
		foreach ($order_totals as $total) {
			if($total['title'] == 'Sub-Total'){
				$data['subtotal'] = number_format($total['value'],2);
			}elseif($total['title'] == 'Tax'){
				$data['tax'] = number_format($total['value'],2);
			}elseif($total['title'] == 'Shipping'){
				$data['shipping'] = number_format($total['value'],2);
			}elseif($total['title'] == 'Total'){
				$data['Total'] = number_format($total['value'],2);
			}
		}

		if($order_id) {
            $data['order_id'] = $order_id;
            $mail = new SendgridapiMail('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');

            $mail->setTo($email);
            $mail->setFrom('hej@profilewear.se');
            $mail->setReplyTo('hej@profilewear.se');
            $mail->setBcc('profilewear@biz.reco.se');
            $mail->setSender(html_entity_decode('Profilewear.se', ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode('Profilewear.se - Orderuppdatering #'.$order_id, ENT_QUOTES, 'UTF-8'));
            $mail->setHtml($this->load->view('codevoc/b2bmanager_order_update_mail', $data));
            $mail->send();
		}
	}
	public function import(){
		$this->load->model('codevoc/b2bmanager_order');
		$this->model_codevoc_b2bmanager_order->import();
	}
}
