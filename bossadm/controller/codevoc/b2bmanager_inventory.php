<?php
namespace Opencart\Admin\Controller\Codevoc;

class B2bmanagerInventory extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function  index(): void  {

		$this->load->language('codevoc/b2bmanager_inventory');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('codevoc/b2bmanager_inventory');
		$this->getList();
	}


	protected function getList(){

		$data = array();

		$this->document->setTitle($this->language->get('heading_title'));

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

		// Token
		$data['token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_inventorylist', $data));
	}

	public function api_get_list(){
		$this->load->model('codevoc/b2bmanager_inventory');
		// retrive all items
		$items = $this->model_codevoc_b2bmanager_inventory->api_get_items();
		
		$add_to_list_items = array();
		if(isset($this->session->data['add_to_list_items'])){
			for ($i = 0; $i < count($this->session->data['add_to_list_items']);$i++) {
				$this->session->data['add_to_list_items'][$i]['item_info'] = $this->model_codevoc_b2bmanager_inventory->api_get_item($this->session->data['add_to_list_items'][$i]['item_id']);
			}
			$add_to_list_items = $this->session->data['add_to_list_items'];
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$result = array(
			'items'=>$items,
			'add_to_list_items' => $add_to_list_items
		);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}

	public function api_delete_item(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$id = (isset($_POST['id']) && $_POST['id'] != "") ? intval($_POST['id']) : "";
			if($id){
				$this->load->model('codevoc/b2bmanager_inventory');
				$this->model_codevoc_b2bmanager_inventory->api_delete_item($id);
				$result['success'] = "Item deleted";
			}else{
				$result['error'] = 'Invalid inventory product id.';
			}
		}else{
			$result['error'] = 'Invalid method';
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}

	public function api_copy_item(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$id = (isset($_POST['id']) && $_POST['id'] != "") ? intval($_POST['id']) : "";
			if($id){
				$this->load->model('codevoc/b2bmanager_inventory');
				$this->model_codevoc_b2bmanager_inventory->api_copy_item($id);
				$result['success'] = "Item Copied";
			}else{
				$result['error'] = 'Invalid inventory product id.';
			}
		}else{
			$result['error'] = 'Invalid method';
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}

	public function api_update_article(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$id = (isset($_POST['id']) && $_POST['id'] != "") ? intval($_POST['id']) : "";
			if($id){
				$error = array();
				$this->load->model('codevoc/b2bmanager_inventory');
				$article_nr = (isset($_POST['article_nr']) && $_POST['article_nr'] != "") ? ($_POST['article_nr']) : "";
				$name = (isset($_POST['name']) && $_POST['name'] != "") ? ($_POST['name']) : "";
				$price_1 = (isset($_POST['price_1']) && $_POST['price_1'] != "") ? round(floatval($_POST['price_1']),2) : "";
				$price_2 = (isset($_POST['price_2']) && $_POST['price_2'] != "") ? round(floatval($_POST['price_2']),2) : "";

				if($article_nr == ""){
					$error[] = "Article Nr. is required.";
				}
				if($name == ""){
					$error[] = "Name is required.";
				}
				if(!empty($error)){
					$result['error'] = $error;
				}else{
					$this->model_codevoc_b2bmanager_inventory->api_update_article($id,$this->request->post);
					$result['success'] = array("Article updated");
				}
			}else{
				$result['error'] = array('Invalid inventory product id.');
			}
		}else{
			$result['error'] = array('Invalid method');
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}

	public function api_add_to_list(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$item_id = (isset($_POST['item_id']) && $_POST['item_id'] != "") ? intval($_POST['item_id']) : "";
			if($item_id){
				$error = array();
				$article_nr = (isset($_POST['article_nr']) && $_POST['article_nr'] != "") ? ($_POST['article_nr']) : "";
				$quantity = (isset($_POST['quantity']) && $_POST['quantity'] != "") ? intval($_POST['quantity']) : 0;

				if($article_nr == ""){
					$error[] = "Article nr. is required.";
				}
				if($quantity == "" || $quantity == 0){
					$error[] = "Quantity can not be zero";
				}
				if(!empty($error)){
					$result['error'] = $error;
				}else{
					$id = md5(uniqid());
					$item = array(
						'id' => $id,
						'item_id' => $item_id,
						'article_nr' => $article_nr,
						'quantity' => $quantity
					);

					$this->session->data['add_to_list_items'][] = $item;

					$result['success'] = array("Item added.");
				}
			}else{
				$result['error'] = array('Invalid inventory product id.');
			}
		}else{
			$result['error'] = array('Invalid method');
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}


	public function api_delete_from_list(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$id = (isset($_POST['id']) && $_POST['id'] != "") ? ($_POST['id']) : "";
			if($id){

				for ($i=0; $i < count($this->session->data['add_to_list_items']); $i++) {
					if($this->session->data['add_to_list_items'][$i]['id'] == $id){
						unset($this->session->data['add_to_list_items'][$i]);
						$this->session->data['add_to_list_items'] = array_values($this->session->data['add_to_list_items']);
						break;
					}
				}

				$result['success'] = array("Item deleted.");
			}else{
				$result['error'] = array('Invalid item.');
			}
		}else{
			$result['error'] = array('Invalid method');
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}

	public function api_delete_all_from_list(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$this->session->data['add_to_list_items'] = array();
			$result['success'] = array("List cleared.");

		}else{
			$result['error'] = array('Invalid method');
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}

	public function api_get_add_to_list(){
		$this->load->model('codevoc/b2bmanager_inventory');

		// retrive all items
		$add_to_list_items = array();
		if(isset($this->session->data['add_to_list_items'])){
			for ($i = 0; $i < count($this->session->data['add_to_list_items']);$i++) {
				$this->session->data['add_to_list_items'][$i]['item_info'] = $this->model_codevoc_b2bmanager_inventory->api_get_item($this->session->data['add_to_list_items'][$i]['item_id']);
			}
			$add_to_list_items = $this->session->data['add_to_list_items'];
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$result = array(
			'add_to_list_items' => $add_to_list_items
		);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}

	public function print_view(){
		$this->load->language('codevoc/b2bmanager_inventory');

		$data = array();

		$data['heading_title'] = $this->language->get('heading_title');

		// Text
		$data['text_add'] = $this->language->get('text_add');
		$data['text_table_column_article_nr'] = $this->language->get('text_table_column_article_nr');
		$data['text_table_column_name'] = $this->language->get("text_table_column_name");
		$data['text_table_column_color'] = $this->language->get("text_table_column_color");
		$data['text_table_column_size'] = $this->language->get("text_table_column_size");
		$data['text_table_column_brand'] = $this->language->get("text_table_column_brand");
		$data['text_table_column_quantity'] = $this->language->get("text_table_column_quantity");
		$data['text_table_column_location'] = $this->language->get("text_table_column_location");
		$data['text_table_column_price_1'] = $this->language->get("text_table_column_price_1");
		$data['text_table_column_price_2'] = $this->language->get("text_table_column_price_2");
		$data['text_table_column_comments'] = $this->language->get("text_table_column_comments");
		$data['text_table_column_date_added'] = $this->language->get("text_table_column_date_added");
		$data['text_table_column_date_updated'] = $this->language->get("text_table_column_date_updated");

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

    	// Token
    	$data['token'] = $this->session->data['user_token'];

		$this->response->setOutput($this->load->view('codevoc/b2bmanager_inventory_print', $data));
	}

	public function api_add_article(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$items = isset($_POST['items']) ? $_POST['items'] : array();

			if(!empty($items)){
				$i = 0;
				$error = false;
				foreach ($items as $item) {

					// Check if error in input in item

					// Consider item only if article nr is provided
					if($item['article_nr'] != ""){
						if(empty($item['article_nr'])){
							$result['error']['items'][$i]['article_nr'] = "Article Nr. is required";
							$error = true;
						}

						if(empty($item['name'])){
							$result['error']['items'][$i]['name'] = "Name. is required";
							$error = true;
						}

						if(empty($item['quantity'])){
							$result['error']['items'][$i]['quantity'] = "Quantity is required";
							$error = true;
						}else if(!is_numeric($item['quantity'])){
							$result['error']['items'][$i]['quantity'] = "Quantity should be number";
							$error = true;
						}

						if($item['price_1'] == ""){
							$result['error']['items'][$i]['price_1'] = "Price 1 is required";
							$error = true;
						}else if(!is_numeric($item['price_1'])){
							$result['error']['items'][$i]['price_1'] = "Price 1 should be number";
							$error = true;
						}
						if($item['price_2'] == ""){
							$result['error']['items'][$i]['price_2'] = "Price 2 is required";
							$error = true;
						}else if(!is_numeric($item['price_2'])){
							$result['error']['items'][$i]['price_2'] = "Price 2 should be number";
							$error = true;
						}
					}
					$i++;
				}

				$success = false;
				if($error == false){
					foreach ($items as $item) {
						if($item['article_nr'] != ""){
							$this->load->model('codevoc/b2bmanager_inventory');
							$this->load->model('user/user');

							// retrive logged in user name
							$user_info = $this->model_user_user->getUser($this->user->getId());
							$firstname = "";
							$lastname = "";
							if ($user_info) {
								$firstname = $user_info['firstname'];
								$lastname = $user_info['lastname'];
							}

							$last_update_by = trim($firstname . " " . $lastname);

							$data = array(
								'article_nr' => $item['article_nr'],
								'name' => $item['name'],
								'color' => $item['color'],
								'size' => $item['size'],
								'brand' => $item['brand'],
								'quantity' => intval($item['quantity']),
								'location' => $item['location'],
								'price_1' => floatval($item['price_1']),
								'price_2' => floatval($item['price_2']),
								'comments' => $item['comments'],
								'last_update_by' => $last_update_by,
							);
							$this->model_codevoc_b2bmanager_inventory->api_create_item($data);
							$success = true;
						}
					}
				}

				if($success)
					$result['success'] = array('Item(s) are inserted');
			}else{
				$result['error'] = array('No items found to insert.');
			}

		}else{
			$result['error'] = array('Invalid method');
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}

	public function api_collect_and_clear(){
		$result = array();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			$this->load->model('codevoc/b2bmanager_inventory');

			if(isset($this->session->data['add_to_list_items']) && !empty($this->session->data['add_to_list_items'])){
				// deduct quantity from stock
				foreach ($this->session->data['add_to_list_items'] as $item) {
					$id = $item['item_id'];
					$quantity = intval($item['quantity']);
					if($quantity < 0)
						$quantity = 0;
					$this->model_codevoc_b2bmanager_inventory->api_deduct_article_quantity($id,$quantity);
				}
			}

			// clear item
			$this->session->data['add_to_list_items'] = array();

			$result['success'] = array("List collected and cleared.");

		}else{
			$result['error'] = array('Invalid method');
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('result'=>$result)));
	}

	public function spcountautocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('codevoc/b2bmanager_inventory');
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

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

			$results = $this->model_codevoc_b2bmanager_inventory->getFindproducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}


					$this->load->model('catalog/manufacturer');
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);

			if ($manufacturer_info) {
				$manufacturer = $manufacturer_info['name'];
			} else {
				$manufacturer = '';
			}
				$productstr='';
				if($manufacturer!='')
				{
			$productstr='['.$result['model'].'] > '.' ['.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).'] - ['.$manufacturer.']'; 			}
			else
			{
			$productstr='['.$result['model'].'] > '.' ['.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).']';
			}




				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => $productstr,
					//'name'       =>strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price'],
					'product_name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'manufacturer' => $manufacturer
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}