<?php
namespace Opencart\Admin\Controller\Codevoc;
class B2bmanagerProduction extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function index() {

		$this->load->language('codevoc/b2bmanager_production');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_production');

		$this->getList();
	}

	public function add() {
		$this->load->language('codevoc/b2bmanager_production');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_production');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_codevoc_b2bmanager_production->addProduction($this->request->post);

			// send slack message
			// $this->load->library('slack');
			$message = "<<<SLACKMESSAGE
			:zap:Ny produktion skapades \n
			:arrow_right: {$this->request->post['name']} :arrow_forward: Ordernr {$this->request->post['order_id']} \n
			Skickas från oss senast :calendar: {$this->request->post['delivery_date']} \n
			SLACKMESSAGE";
			// $this->slack->sendMessage($message, 'production');

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			//addon point 5
			if(!empty($this->request->post['type']))
			{
				$this->response->redirect($this->url->link('codevoc/b2bmanager_order.edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->post['order_id'], true));
			}
			else
			{
				$this->response->redirect($this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
			//addon point 5
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('codevoc/b2bmanager_production');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_production');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_codevoc_b2bmanager_production->editProduction($this->request->get['production_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('codevoc/b2bmanager_production');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_production');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $production_id) {
				$this->model_codevoc_b2bmanager_production->deleteProduction($production_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = 'all';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		$data['catalog'] = HTTP_CATALOG;
		$data['add'] = $this->url->link('codevoc/b2bmanager_production.add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('codevoc/b2bmanager_production.delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['productions'] = array();

		$filter_data = array(
			'filter_status' => $filter_status,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * (int)$this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		);

		$production_total = $this->model_codevoc_b2bmanager_production->getTotalProductions($filter_data);

		$results = $this->model_codevoc_b2bmanager_production->getProductions($filter_data);

		foreach ($results as $result) {
			$data['productions'][] = array(
				'production_id' => $result['production_id'],
				'name'        => $result['name'],
				'order_date'=>date($this->language->get('date_formatshort'), strtotime($result['order_date'])),
				'order_id'=>$result['order_id'],
				'delivery_date'=>date($this->language->get('date_formatshort'), strtotime($result['delivery_date'])),
				// 'supplier_name'=>$this->model_codevoc_b2bmanager_production->getProductionSupplierName($result['supplier_id']),
				// 'method_name'=>$this->model_codevoc_b2bmanager_production->getProductionMethodName($result['method_id']),
				'supplier_name' => $result['suppliers'],
				'method_name' => $result['methods'],
				'item_arrival'  => $result['item_arrival'],
				'print_arrival'  => $result['print_arrival'],
				'status'  => $result['status'],
				'edit'        => $this->url->link('codevoc/b2bmanager_production.edit', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $result['production_id'] . $url, true),
				'delete'      => $this->url->link('codevoc/b2bmanager_production.delete', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $result['production_id'] . $url, true)
			);
		}

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

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_order_id'] = $this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . '&sort=order_id' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

        $data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $production_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . $url .'&filter_status='. $filter_status.'&page={page}', true)
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($production_total) ? (($page - 1) * (int)$this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * (int)$this->config->get('config_pagination_admin')) > ($production_total - (int)$this->config->get('config_pagination_admin'))) ? $production_total : ((($page - 1) * (int)$this->config->get('config_pagination_admin')) + (int)$this->config->get('config_pagination_admin')), $production_total, ceil($production_total / (int)$this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['filter_status'] = $filter_status;
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_productionlist', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['production_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		if(isset($this->request->get['production_id'])){
			$data['production_id'] = $this->request->get['production_id'] ? $this->request->get['production_id'] : null;
		}
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['order_date'])) {
			$data['error_order_date'] = $this->error['order_date'];
		} else {
			$data['error_order_date'] = '';
		}

		if (isset($this->error['order_id'])) {
			$data['error_order_id'] = $this->error['order_id'];
		} else {
			$data['error_order_id'] = '';
		}

		if (isset($this->error['delivery_date'])) {
			$data['error_delivery_date'] = $this->error['delivery_date'];
		} else {
			$data['error_delivery_date'] = '';
		}

		if (isset($this->error['production_date'])) {
			$data['error_production_date'] = $this->error['production_date'];
		} else {
			$data['error_production_date'] = '';
		}

		if (isset($this->error['suppliers'])) {
			$data['error_suppliers'] = $this->error['suppliers'];
		} else {
			$data['error_suppliers'] = '';
		}

		if (isset($this->error['methods'])) {
			$data['error_methods'] = $this->error['methods'];
		} else {
			$data['error_methods'] = '';
		}

		/*
		if (isset($this->error['item_arrival'])) {
			$data['error_item_arrival'] = $this->error['item_arrival'];
		} else {
			$data['error_item_arrival'] = '';
		}

		if (isset($this->error['print_arrival'])) {
			$data['error_print_arrival'] = $this->error['print_arrival'];
		} else {
			$data['error_print_arrival'] = '';
		}
		*/

		if (isset($this->error['status'])) {
			$data['error_status'] = $this->error['status'];
		} else {
			$data['error_status'] = '';
		}

		if (isset($this->error['duration'])) {
			$data['error_duration'] = $this->error['duration'];
		} else {
			$data['error_duration'] = '';
		}
		/*
		if (isset($this->error['attention'])) {
			$data['error_attention'] = $this->error['attention'];
		} else {
			$data['error_attention'] = '';
		}
		*/
		/*
		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = '';
		}
		*/

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['production_id'])) {
			$data['action'] = $this->url->link('codevoc/b2bmanager_production.add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('codevoc/b2bmanager_production.edit', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $this->request->get['production_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('codevoc/b2bmanager_production', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['production_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$production_info = $this->model_codevoc_b2bmanager_production->getProduction($this->request->get['production_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];


		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($production_info)) {
			$data['name'] = $production_info['name'];
		} else {
			//addon point 5
			if(isset($this->request->get['name']))
			{
				if(!empty($this->request->get['companyname'])){
					$data['name'] = html_entity_decode($this->request->get['name']. ' (' .$this->request->get['companyname'].')', ENT_QUOTES, 'UTF-8');
				}else{
					$data['name'] = html_entity_decode($this->request->get['name'], ENT_QUOTES, 'UTF-8');
				}
			}
			else
			{
				$data['name'] = '';
			}
			//addon point 5
		}
		//addon point 5
		if(isset($this->request->post['type']))
		{
			$data['type'] = $this->request->post['type'];
		}
		else if(isset($this->request->get['type']))
		{
			$data['type'] = $this->request->get['type'];
		}
		else
		{
			$data['type'] = '';
		}
		//addon point 5
		if (isset($this->request->post['order_date'])) {
			$data['order_date'] = $this->request->post['order_date'];
		} elseif (!empty($production_info)) {
			$data['order_date'] =date($this->language->get('date_formatshort'), strtotime($production_info['order_date']));
		} else {
			//addon point 5
			if(isset($this->request->get['order_date']))
			{
				$data['order_date'] =date($this->language->get('date_formatshort'), strtotime($this->request->get['order_date']));
			}
			else
			{
				$data['order_date'] = '';
			}
			//addon point 5
		}

		if (isset($this->request->post['order_id'])) {
			$data['order_id'] = $this->request->post['order_id'];
		} elseif (!empty($production_info)) {
			$data['order_id'] = $production_info['order_id'];
		} else {
			//addon point 5
			if(isset($this->request->get['order_id']))
			{
				$data['order_id'] = $this->request->get['order_id'];
			}
			else
			{
				$data['order_id'] = '';
			}
			//addon point 5
		}

		if (isset($this->request->post['delivery_date'])) {
			$data['delivery_date'] = $this->request->post['delivery_date'];
		} elseif (!empty($production_info)) {
			$data['delivery_date'] =date($this->language->get('date_formatshort'), strtotime($production_info['delivery_date']));
		} else {
			$data['delivery_date'] = '';
		}

		if (isset($this->request->post['production_date'])) {
			$data['production_date'] = $this->request->post['production_date'];
		} elseif (!empty($production_info)) {
			$data['production_date'] =date($this->language->get('date_formatshort'), strtotime($production_info['production_date']));
		} else {
			$data['production_date'] = '';
		}

		if (isset($this->request->post['suppliers'])) {
			$data['production_suppliers'] = $this->request->post['suppliers'];
		} elseif (!empty($production_info)) {
			$data['production_suppliers'] = $production_info['suppliers'];
		} else {
			$data['production_suppliers'] = '';
		}

		if (isset($this->request->post['methods'])) {
			$data['production_methods'] = $this->request->post['methods'];
		} elseif (!empty($production_info)) {
			$data['production_methods'] = $production_info['methods'];
		} else {
			$data['production_methods'] = '';
		}

		if (isset($this->request->post['item_arrival'])) {
			$data['item_arrival'] = $this->request->post['item_arrival'];
		} elseif (!empty($production_info)) {
			$data['item_arrival'] = $production_info['item_arrival'];
		} else {
			$data['item_arrival'] = '';
		}

		if (isset($this->request->post['print_arrival'])) {
			$data['print_arrival'] = $this->request->post['print_arrival'];
		} elseif (!empty($production_info)) {
			$data['print_arrival'] = $production_info['print_arrival'];
		} else {
			$data['print_arrival'] = '';
		}

		if (isset($this->request->post['duration'])) {
			$data['duration'] = $this->request->post['duration'];
		} elseif (!empty($production_info)) {
			$data['duration'] = $production_info['duration'];
		} else {
			$data['duration'] = '';
		}

		if (isset($this->request->post['file'])) {
			$data['file'] = $this->request->post['file'];
		} elseif (!empty($production_info)) {
			$data['file'] = $production_info['file'];
		} else {
			if(isset($this->request->get['file']))
			{
				$data['file'] = $this->request->get['file'];
			}
			else
			{
				$data['file'] = '';
			}			
		}
		
		if (isset($this->request->post['attention'])) {
			$data['attention'] = $this->request->post['attention'];
		} elseif (!empty($production_info)) {
			$data['attention'] = $production_info['attention'];
		} else {
			$data['attention'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($production_info)) {
			$data['status'] = $production_info['status'];
		} else {
			$data['status'] = 6;
		}

		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($production_info)) {
			$data['description'] = $production_info['description'];
		} else {
			$data['description'] = '';
		}

		$data['products'] = array();
		if($data['order_id']) {
			$this->load->model('sale/order');

			$products = $this->model_sale_order->getProducts($data['order_id']);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_sale_order->getOptions($data['order_id'], $product['order_product_id']);

				// Sort options based on name
				$name = array_column($options, 'name');
				array_multisort($name, SORT_ASC, $options);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $upload_info['name'],
								'type'  => $option['type'],
								'href'  => $this->url->link('tool/upload/download', 'user_token=' . $this->session->data['user_token'] . '&code=' . $upload_info['code'], true)
							);
						}
					}
				}

				$data['products'][] = array(
					'order_product_id' => $product['order_product_id'],
					'product_id'       => $product['product_id'],
					'name'    	 	   => $product['name'],
					'model'    		   => $product['model'],
					'option'   		   => $option_data,
					'quantity'		   => $product['quantity'],
					'price'    		   => $product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0),
					'total'    		   => $product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0),
					'href'     		   => $this->url->link('catalog/product.edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id'], true)
				);
			}
		}
		$data['suppliers'] = $this->model_codevoc_b2bmanager_production->getProductionSuppliers();
		$data['methods'] = $this->model_codevoc_b2bmanager_production->getProductionMethods();

		$data['allstatus'] =array('New','Progress','Completed','Cancelled','Priority','Rest');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_productionform', $data));
	}

	protected function validateForm() {
		// var_dump($this->request->post);exit;
		if (!$this->user->hasPermission('modify', 'codevoc/b2bmanager_production')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((strlen($this->request->post['name']) < 1)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((strlen($this->request->post['order_date']) < 1)) {
			$this->error['order_date'] = $this->language->get('error_order_date');
		}

		if ((strlen($this->request->post['delivery_date']) < 1)) {
			$this->error['delivery_date'] = $this->language->get('error_delivery_date');
		}

		if ((strlen($this->request->post['production_date']) < 1)) {
			$this->error['production_date'] = $this->language->get('error_production_date');
		}

		//if ((utf8_strlen($this->request->post['suppliers']) < 1)) {
		if(!isset($this->request->post['suppliers'])) {
			$this->error['suppliers'] = $this->language->get('error_supplier_id');
		}

		if(!isset($this->request->post['methods'])) {
			$this->error['methods'] = $this->language->get('error_method_id');
		}

		/*
		if ((utf8_strlen($this->request->post['item_arrival']) < 1)) {
			$this->error['item_arrival'] = $this->language->get('error_item_arrival');
		}

		if ((utf8_strlen($this->request->post['print_arrival']) < 1)) {
			$this->error['print_arrival'] = $this->language->get('error_print_arrival');
		}
		*/

		if ((strlen($this->request->post['status']) < 1)) {
			$this->error['status'] = $this->language->get('error_status');
		}

		if ((strlen($this->request->post['duration']) < 1)) {
			$this->error['duration'] = $this->language->get('error_duration');
		}

		/*
		if ((utf8_strlen($this->request->post['attention']) < 1)) {
			$this->error['attention'] = $this->language->get('error_attention');
		}
		*/
		
		/*
		if ((utf8_strlen($this->request->post['description']) < 1)) {
			$this->error['description'] = $this->language->get('error_description');
		}
		*/

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'codevoc/b2bmanager_production')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
    public function import(){
		$this->load->model('codevoc/b2bmanager_production');
		$this->model_codevoc_b2bmanager_production->import();
	}



}
