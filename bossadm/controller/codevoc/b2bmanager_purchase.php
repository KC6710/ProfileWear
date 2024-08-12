<?php
namespace Opencart\Admin\Controller\Codevoc;
class B2bmanagerPurchase extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function index() {

		$this->document->setTitle('Purchased orders');

		$this->load->model('codevoc/b2bmanager_purchase');

		$this->getList();
	}

	public function add() {

		$this->document->setTitle('Purchase');

		$this->load->model('codevoc/b2bmanager_purchase');

		$this->getForm();
	}

	public function edit() {

		$this->document->setTitle('Purchase');

		$this->load->model('codevoc/b2bmanager_purchase');

		$this->getForm();
	}

	public function printPurchasedList() {

		$this->document->setTitle('Purchase list print');

		$this->load->model('codevoc/b2bmanager_purchase');

		if (isset($this->request->get['purchase_id'])) {
			$purchase_info = $this->model_codevoc_b2bmanager_purchase->getPurchase($this->request->get['purchase_id']);
		}

		if(!empty($purchase_info)) {
			$data['purchase_info'] = $purchase_info;
		} else {
			$data['purchase_info'] = null;
		}

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_purchaseprintlist', $data));
	}

	public function printPurchasedOrderList() {

		$this->document->setTitle('Purchase list print');

		$this->load->model('codevoc/b2bmanager_purchase');

		$data['empty'] = '';

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_purchaseprintorderlist', $data));
	}


	protected function getList() {
		$url = '';

		$data['btn_edit'] = $this->url->link('codevoc/b2bmanager_purchase.edit', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['btn_print_purchased_list'] = $this->url->link('codevoc/b2bmanager_purchase.printPurchasedList', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['btn_print_purchased_order_list'] = $this->url->link('codevoc/b2bmanager_order.purchaseorder', 'user_token=' . $this->session->data['user_token'] . $url, true);


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => 'Home',
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Purchase',
			'href' => $this->url->link('codevoc/b2bmanager_purchase', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('codevoc/b2bmanager_purchase.add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('codevoc/b2bmanager_purchase.delete', 'user_token=' . $this->session->data['user_token'] . $url, true);


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

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['purchases'] = array();

		$filter_data = array(
			'start'                  => ($page - 1) * 10,
			'limit'                  => 10
		);

		$purchase_total = $this->model_codevoc_b2bmanager_purchase->getTotalPurchases($filter_data);
		$results = $this->model_codevoc_b2bmanager_purchase->getPurchases($filter_data);

		foreach($results as $result) {
			$data['purchases'][] = array(
				'purchase_id' => $result['purchase_id'],
				'name' => $result['name'],
				'total_orders' => $result['total_orders'],
				'date' => date('Y-m-d', strtotime($result['created_at'])),
				'purchased_by' => $result['purchased_by'],
				'purchased_by_name' => $result['purchased_by_name'],
				'purchase_receiver_name' => $result['purchase_receiver_name'],
				'purchase_complete' => $this->isPurchaseComplete($result['purchase_id']),
				'orders' => $this->model_codevoc_b2bmanager_purchase->getPurchaseOrderForListing($result['purchase_id']),
				'suppliers' => $this->model_codevoc_b2bmanager_purchase->getPurchaseSuppliersName($result['purchase_id']),
				'edit' => $this->url->link('codevoc/b2bmanager_purchase.edit', 'user_token=' . $this->session->data['user_token'] . '&purchase_id=' . $result['purchase_id'] . $url, true),
				'print_purchase_list' => $this->url->link('codevoc/b2bmanager_purchase.printPurchasedList', 'user_token=' . $this->session->data['user_token'] . '&purchase_id=' . $result['purchase_id'] . $url, true)
			);
		}

        $data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $purchase_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('codevoc/b2bmanager_purchase', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true)
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($purchase_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($purchase_total - 10)) ? $purchase_total : ((($page - 1) * 10) + 10), $purchase_total, ceil($purchase_total / 10));
		$data['token'] = $this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_purchaselist', $data));
	}

	protected function getForm() {
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$url = '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => 'Home',
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Purchase',
			'href' => $this->url->link('codevoc/b2bmanager_purchase', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['suppliers'] = $this->model_codevoc_b2bmanager_purchase->getSuppliers();
		$data['all_suppliers'] = $this->model_codevoc_b2bmanager_purchase->getAllSuppliers();
		$data['receivers'] = $this->model_codevoc_b2bmanager_purchase->getReceivers();
		if (isset($this->request->get['purchase_id'])) {
			$purchase_info = $this->model_codevoc_b2bmanager_purchase->getPurchase($this->request->get['purchase_id']);
		}

		if(!empty($purchase_info)) {
			$data['purchase_info'] = $purchase_info;
			$data['purchase_info_json'] = $this->escapeJsonString(json_encode($purchase_info));
		} else {
			$data['purchase_info'] = null;
		}
		// echo "<pre>"; print_r($data['purchase_info']); die;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_purchaseform', $data));
	}

	/* Function to escape new line from the comment */
	private function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
			$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c", "'");
			$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", "\'");
			$result = str_replace($escapers, $replacements, $value);
			return $result;
	}

	public function generatePurchaseOrder() {
		$orders = $this->request->post['orders'];
		$json = [];
		if(count($orders) <= 0) {
			$json['error'] = "No orders provided for purchase order.";
		}

		if($orders) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$user_id = $this->user->getId();
			$purchase_id = $this->model_codevoc_b2bmanager_purchase->generatePurhcase($user_id, $orders);
			$json['success'] = "Purchase order generated.";
			$json['purchase_url'] = $this->url->link('codevoc/b2bmanager_purchase.edit', 'user_token=' . $this->session->data['user_token']."&purchase_id=". $purchase_id, true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function savePurchaseDetail() {
		$json = [];
		$purchase_id = $this->request->post['purchase_id'];
		$name = $this->request->post['name'];
		$purchase_receiver_id = $this->request->post['purchase_receiver_id'];

		if(empty($purchase_id) || empty($name) || empty($purchase_receiver_id)) {
			$json['error'] = "Invalid data provided.";
		}

		if($purchase_id && $name && $purchase_receiver_id) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$this->model_codevoc_b2bmanager_purchase->savePurchaseDetail($purchase_id, [
				'name' => $name,
				'purchase_receiver_id' => $purchase_receiver_id,
			]);
			$json['success'] = "Inköp är sparad";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function saveOrderComment() {
		$json = [];
		$purchase_order_id = $this->request->post['purchase_order_id'];
		$comment = $this->request->post['comment'];

		if(empty($purchase_order_id) || empty($comment)) {
			$json['error'] = "Invalid data provided.";
		}

		if($purchase_order_id && $comment) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$this->model_codevoc_b2bmanager_purchase->saveOrderComment($purchase_order_id, [
				'comment' => $comment,
			]);
			$json['success'] = "Inköpskommentar för denna order är sparad";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function savePurchaseCost() {
		$json = [];
		$order_id = $this->request->post['order_id'];
		$cost = $this->request->post['cost'];
		$type = $this->request->post['type'];
		$supplier = $this->request->post['supplier'];
		if(array_key_exists('label',$this->request->post)){
			$label = $this->request->post['label'];
		}else{
			$label = '';
		}

		if(empty($order_id) || empty($cost) || empty($type) || empty($supplier)) {
			$json['error'] = "Invalid data provided.";
		}

		if($order_id && $cost && $type && $supplier) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$item = $this->model_codevoc_b2bmanager_purchase->savePurchaseCost($order_id, [
				'cost' => $cost,
				'type' => $type,
				'supplier' => $supplier,
				'label' => $label,
			]);
			$json['success'] = "Inköpskostnaden för denna order sparas";
			$json['item'] = $item;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeCost() {
		$json = [];
		$cost_id = $this->request->post['cost_id'];

		if(empty($cost_id)) {
			$json['error'] = "Invalid data provided.";
		}

		if($cost_id) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$item = $this->model_codevoc_b2bmanager_purchase->removeCost($cost_id);
			$json['success'] = "Order cost removed.";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeOrder() {
		$json = [];
		$purchase_order_id = $this->request->post['purchase_order_id'];

		if(empty($purchase_order_id)) {
			$json['error'] = "Invalid data provided.";
		}

		if($purchase_order_id) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$this->model_codevoc_b2bmanager_purchase->removeOrder($purchase_order_id);
			$json['success'] = "Purchase order removed.";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeOrderAndChangeStatus() {
		$json = [];
		$purchase_order_id = $this->request->post['purchase_order_id'];

		if(empty($purchase_order_id)) {
			$json['error'] = "Invalid data provided.";
		}

		if($purchase_order_id) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$this->model_codevoc_b2bmanager_purchase->removeOrderAndChangeStatus($purchase_order_id);
			$json['success'] = "Purchase order removed.";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function saveSupplier() {
		$json = [];
		$purchase_order_id = $this->request->post['purchase_order_id'];
		$products = $this->request->post['products'];
		$supplier_id = $this->request->post['supplier_id'] ? $this->request->post['supplier_id'] : 0;

		if(empty($purchase_order_id) || empty($products)) {
			$json['error'] = "Invalid data provided.";
		}

		if(!isset($json['error'])) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$this->model_codevoc_b2bmanager_purchase->saveSupplier($purchase_order_id, [
				'products' => $products,
				'supplier_id' => $supplier_id
			]);
			$json['products'] = $this->model_codevoc_b2bmanager_purchase->getPurchaseOrderProducts($purchase_order_id);
			$json['success'] = "Leveranstör är sparad";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function finishPurchaseAndChangeStatus() {
		$json = [];
		$purchase_id = $this->request->post['purchase_id'];

		if(empty($purchase_id)) {
			$json['error'] = "Invalid data provided.";
		}

		if($purchase_id) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$user_id = $this->user->getId();
			$this->model_codevoc_b2bmanager_purchase->finishPurchaseAndChangeStatus($user_id, $purchase_id);
			$json['success'] = "Orders status changed to processing.";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getAllPurchaseOrder() {
		$json = [];
		$purchase_id = $this->request->get['purchase_id'];

		if(empty($purchase_id)) {
			$json['error'] = "Invalid data provided.";
		}

		if($purchase_id) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$orders = $this->model_codevoc_b2bmanager_purchase->getPurchaseOrder($purchase_id);
			$json['orders'] = $orders;
			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function isPurchaseComplete($purchase_id) {
		$orders = $this->model_codevoc_b2bmanager_purchase->getPurchaseOrderForListing($purchase_id);
		$total_orders = count($orders);
		$total_finished_orders = 0;
		foreach($orders as $order) {
			$products = $order['products'];
			$total_products = count($order['products']);
			$total_finished_products = 0;
			foreach($products as $product) {
				if(
					($product['purchased'] && $product['purchase_supplier_id'] != "0")
					|| $product['pending']
				) {
					$total_finished_products += 1;
				}
			}

			if($total_products === $total_finished_products)
				$total_finished_orders += 1;
		}

		return $total_finished_orders === $total_orders;
	}

	public function deletePurchase() {
		$json = [];
		$purchase_id = $this->request->post['purchase_id'];

		if(empty($purchase_id)) {
			$json['error'] = "Invalid data provided.";
		}

		if($purchase_id) {
			$this->load->model('codevoc/b2bmanager_purchase');
			$this->model_codevoc_b2bmanager_purchase->deletePurchase($purchase_id);
			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addOrderstoPurchase() {
		$json = [];
		$purchase_id = $this->request->post['purchase_id'];
		$orders = $this->request->post['orders'];

		if(empty($purchase_id) || empty($orders)) {
			$json['error'][] = "Invalid data provided.";
		}

		if($purchase_id) {
			$orders = [$orders];
			$this->load->model('codevoc/b2bmanager_purchase');

			$orderExist = $this->model_codevoc_b2bmanager_purchase->checkOrderExistInPurchase($purchase_id, $orders[0]);

			if($orderExist)
				$json['error'][] = "Order already added to purchase.";

			// Check order exist in system
			$finalOrders = [];
			foreach($orders as $order) {
				$orderInfo = $this->model_codevoc_b2bmanager_purchase->getSaleOrder($order);
				if($orderInfo)
					$finalOrders[] = $order;
			}

			if(empty($finalOrders)) {
				$json['error'][] = "No order found with provided order number.";
			}

			if(!isset($json['error'])) {
				// add orders to purchase
				$this->model_codevoc_b2bmanager_purchase->addOrdersToPurchase($purchase_id, $finalOrders);
				$json['success'] = true;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    public function import(){
		$this->load->model('codevoc/b2bmanager_purchase');
		$this->model_codevoc_b2bmanager_purchase->import();
	}

}
