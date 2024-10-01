<?php
namespace Opencart\Catalog\Controller\Checkout;
include_once DIR_SYSTEM . 'library/sendgridapimail/sendgridapimail.php';
use \Opencart\System\Library\SendGridApiMail\SendgridapiMail;
class Quotation extends \Opencart\System\Engine\Controller {
	public function index(): void {
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart', 'language=' . $this->config->get('config_language')));
		}

		if ($this->customer->isLogged()) {
			$data['first_name'] = $this->customer->getFirstName();
			$data['last_name'] = $this->customer->getLastName();
			$data['email'] = $this->customer->getEmail();
			$data['telephone'] = $this->customer->getTelephone();
			$data['company'] = $this->customer->getCompany();
		}

		$this->load->model('checkout/cart');

		$data['totals'] = [];

		$totals = [];
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			($this->model_checkout_cart->getTotals)($totals, $taxes, $total);

			foreach ($totals as $result) {
				$data['totals'][] = [
					'title' => $result['title'],
					'text'  => $this->currency->format($result['value'], $this->session->data['currency'])
				];
			}
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			if (!$product['minimum']) {
				$this->response->redirect($this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true));

				break;
			}
		}

		$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'))
		];

		if (!$this->customer->isLogged()) {
			$data['register'] = $this->load->controller('checkout/register');
		} else {
			$data['register'] = '';
		}

		if ($this->customer->isLogged() && $this->config->get('config_checkout_payment_address')) {
			$data['payment_address'] = $this->load->controller('checkout/payment_address');
		} else {
			$data['payment_address'] = '';
		}

		if ($this->customer->isLogged() && $this->cart->hasShipping()) {
			$data['shipping_address'] = $this->load->controller('checkout/shipping_address');
		}  else {
			$data['shipping_address'] = '';
		}

		if ($this->cart->hasShipping()) {
			$data['shipping_method'] = $this->load->controller('checkout/shipping_method');
		}  else {
			$data['shipping_method'] = '';
		}

		$data['payment_method'] = $this->load->controller('checkout/payment_method');
		$data['quotation_confirm'] = $this->url->link('checkout/quotation.confirm', 'language=' . $this->config->get('config_language'));

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('checkout/quotation', $data));
	}

	public function confirm(): void {

		$json = array();

		$this->load->model('account/customer');
    
		$email = $this->request->post['email'];
		$firstname = $this->request->post['first_name'];
		$lastname = $this->request->post['last_name'];
		$telephone = $this->request->post['telephone'];
		$password = $this->request->post['first_name'].$this->request->post['last_name']; // Generate this securely
		
		// First, let's check if the customer already exists
		$existingCustomer = $this->model_account_customer->getCustomerByEmail($email);

		$new_customer_id = 0;
		$customer_data = [];
		if (!$existingCustomer) {
			$customer_data = array(
				'email'      => $email,
				'firstname'  => $firstname,
				'lastname'   => $lastname,
				'telephone'  => $telephone,
				'password'   => $password,
			);
			
			$new_customer_id = $this->model_account_customer->addCustomer($customer_data);
		}else{
			$customer_data = $existingCustomer; 
		}
		$this->load->language('checkout/quotation');
		// Customer
		// if (!isset($this->session->data['customer']) && !$new_customer_id) {
		// 	$json['error'] = sprintf('Email already in use.', $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
		// }
		// Cart
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['error'] = $this->language->get('error_stock');
		}
		// Validate minimum quantity requirements.
		$products = $this->cart->getCartProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['error'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

				break;
			}
		}
		if (!$json){
			$json['success'] = $this->language->get('text_success');
			$order_data = array();
				
			//custom data
			if(isset($this->request->post['assignee'])){$order_data['assignee']=$this->request->post['assignee'];}else{$order_data['assignee']='';}
			if(isset($this->request->post['vatnr'])){$order_data['vatnr']=$this->request->post['vatnr'];}else{$order_data['vatnr']='';}
			if(isset($this->request->post['payment_company'])){$order_data['payment_company']=$this->request->post['payment_company'];}else{$order_data['payment_company']='';}
			if(isset($this->request->post['shipping_company'])){$order_data['shipping_company']=$this->request->post['shipping_company'];}else{$order_data['shipping_company']='';}
			// Get today's date in the format you require, e.g., YYYY-MM-DD
			$today_date = date("Y-m-d");

			if(isset($this->request->post['create_date'])) {
				$order_data['create_date'] = $this->request->post['create_date'];
			} else {
				$order_data['create_date'] = $today_date;
			}

			if(isset($this->request->post['delivery_date'])) {
				$order_data['delivery_date'] = $this->request->post['delivery_date'];
			} else {
				$order_data['delivery_date'] = $today_date;
			}

			if(isset($this->request->post['expiration_date'])) {
				$order_data['expiration_date'] = $this->request->post['expiration_date'];
			} else {
				$order_data['expiration_date'] = $today_date;
			}
			if(isset($this->request->post['shippment_terms'])){$order_data['shippment_terms']=$this->request->post['shippment_terms'];}else{$order_data['shippment_terms']='';}
			if(isset($this->request->post['rate_delay'])){$order_data['rate_delay']=$this->request->post['rate_delay'];}else{$order_data['rate_delay']='';}
			if(isset($this->request->post['custom_ordernr'])){$order_data['custom_ordernr']=$this->request->post['custom_ordernr'];}else{$order_data['custom_ordernr']='';}
			//custom data
				
				
			// Store Details
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				if ($this->request->server['HTTPS']) {
					$order_data['store_url'] = HTTP_SERVER;
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
			}

			// Customer Details
			if($existingCustomer) {
				$order_data['customer_id'] = $existingCustomer['customer_id'];
				$order_data['customer_group_id'] = $existingCustomer['customer_group_id'];
				$order_data['firstname'] = $existingCustomer['firstname'];
				$order_data['lastname'] = $existingCustomer['lastname'];
				$order_data['email'] = $existingCustomer['email'];
				$order_data['telephone'] = $existingCustomer['telephone'];
				$order_data['custom_field'] = $existingCustomer['custom_field'];
			} else {
				$order_data['customer_id'] = $new_customer_id;
				$order_data['customer_group_id'] = '';
				$order_data['firstname'] = $customer_data['firstname'];
				$order_data['lastname'] = $customer_data['lastname'];
				$order_data['email'] = $customer_data['email'];
				$order_data['telephone'] = $customer_data['telephone'];
				$order_data['custom_field'] = $this->request->post['vatnr'];
			}
			



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
				$this->load->model('localisation/zone');
				$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
				$order_data['payment_zone'] = $payment_zone['name'];
				$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
				$this->load->model('localisation/country');
				$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
				$order_data['payment_country'] = $payment_country['name'];
				$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
			} else{
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
				if(isset($this->request->post['input_payment_zone_id']) && $this->request->post['input_payment_zone_id'] != 0){
					$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
					$this->load->model('localisation/zone');
					$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
					$order_data['payment_zone'] = $payment_zone['name'];
				}else{
					$order_data['payment_zone_id'] = '';
					$order_data['payment_zone'] = '';
				}
				if(isset($this->request->post['input_payment_country_id']) && $this->request->post['input_payment_country_id'] != ''){
					$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
					$this->load->model('localisation/country');
					$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
					$order_data['payment_country'] = $payment_country['name'];
				}else{
					$order_data['payment_country_id'] = '';
					$order_data['payment_country'] = '';
				}
			}
			if(array_key_exists('shipping_address',$this->request->post) && $this->request->post['shipping_address']!= 0){
				$order_data['shipping_address_id'] = $this->request->post['shipping_address'];
				$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
				$order_data['shipping_lastname'] = $this->request->post['input_shipping_lastname'];
				if(isset($this->request->post['input_shipping_company'])) {
					$order_data['shipping_company'] = $this->request->post['input_shipping_company'];
				} else {
					$company = json_decode($this->session->data['customer']['custom_field'],true);
					if(isset($company[3])) {
						$order_data['shipping_company'] = $company[3];
					} else {
						$order_data['shipping_company'] = '';
					}
				}
				
				$order_data['shipping_address_1'] = $this->request->post['input_shipping_address_1'];
				$order_data['shipping_address_2'] = $this->request->post['input_shipping_address_2'];
				$order_data['shipping_city'] = $this->request->post['input_shipping_city'];
				$order_data['shipping_postcode'] = $this->request->post['input_shipping_postcode'];
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
				if(isset($this->request->post['input_shipping_zone_id']) && $this->request->post['input_shipping_zone_id'] != 0){
					$order_data['shipping_zone_id'] = $this->request->post['input_shipping_zone_id'];
					$this->load->model('localisation/zone');
					$shipping_zone = $this->model_localisation_zone->getZone($this->request->post['input_shipping_zone_id']);
					$order_data['shipping_zone'] = $shipping_zone['name'];
				}else{
					$order_data['shipping_zone_id'] = '';
					$order_data['shipping_zone'] = '';
				}
				if(isset($this->request->post['input_shipping_country_id']) && $this->request->post['input_shipping_country_id'] != ''){
					$order_data['shipping_country_id'] = $this->request->post['input_shipping_country_id'];
					$this->load->model('localisation/country');
					$shipping_country = $this->model_localisation_country->getCountry($this->request->post['input_shipping_country_id']);
					$order_data['shipping_country'] = $shipping_country['name'];
				}else{
					$order_data['shipping_country_id'] = '';
					$order_data['shipping_country'] = '';
				}
			}
			if (isset($this->request->post['shipping_method'])) {
				$order_data['shipping_method'] = $this->request->post['shipping_method'];
			} else {	
				$order_data['shipping_method'] = '';
			}

			if (isset($this->request->post['payment_method'])) {
				$order_data['payment_method'] = $this->request->post['payment_method'];
			} else {	
				$order_data['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$order_data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {	
				$order_data['payment_code'] = '';
			}

			if (isset($this->session->data['shipping_method']['code'])) {
				$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
			} else {
				$order_data['shipping_code'] = '';
			}
			// $order_data['quotation_status_id'] = 1;

				
			// Products
			$order_data['products'] = array();

			foreach ($this->cart->getCartProducts() as $product) {
				if(count($product['option']) > 0){
					$option_data = array();

					foreach ($product['option'] as $option) {
						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $option['value'],
							'type'                    => $option['type'],
							'quantity'				  => $option['quantity']
						);
					}
	
					$other_options = (array_filter($option_data,function($option){
						return $option['name'] != 'Storlek'; 
					})); 
					foreach($option_data as $option){
						if($option['name']=='Storlek' && ($option['quantity'] != '' || $option['quantity'] > 0)){
							$product_options = [];
							array_push($product_options, $option);
							foreach($other_options as $other_option){
								array_push($product_options, $other_option);
							}
							$order_data['products'][] = array(
								'product_id' => $product['product_id'],
								'name'       => $product['name'],
								'model'      => $product['model'],
								'option'     => $product_options,
								'download'   => $product['download'],
								'quantity'   => $option['quantity'],
								'subtract'   => $product['subtract'],
								'price'      => $product['price'],
								// 'b2b_product_discount'=> $product['b2b_product_discount'],//B2B Manager
								// 'b2b_product_tax_class_id'=> $product['b2b_product_tax_class_id'],//B2B Manager
								// 'b2b_product_sort_order'=> $product['b2b_product_sort_order'],//B2B Manager
								'b2b_product_discount'=> '',//B2B Manager
								'b2b_product_tax_class_id'=> '',//B2B Manager
								'b2b_product_sort_order'=> '',//B2B Manager
								'total'      => $product['total'],
								'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
								'reward'     => $product['reward']
							);
						}
					}
				}else{
					$option_data = array();

					foreach ($product['option'] as $option) {
						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $option['value'],
							'type'                    => $option['type'],
							'quantity'				  => $option['quantity']
						);
					}

					$order_data['products'][] = array(
						'product_id' => $product['product_id'],
						'name'       => $product['name'],
						'model'      => $product['model'],
						'option'     => $option_data,
						'download'   => $product['download'],
						'quantity'   => $product['quantity'],
						'subtract'   => $product['subtract'],
						'price'      => $product['price'],
						// 'b2b_product_discount'=> $product['b2b_product_discount'],//B2B Manager
						// 'b2b_product_tax_class_id'=> $product['b2b_product_tax_class_id'],//B2B Manager
						// 'b2b_product_sort_order'=> $product['b2b_product_sort_order'],//B2B Manager
						'b2b_product_discount'=> '',//B2B Manager
						'b2b_product_tax_class_id'=> '',//B2B Manager
						'b2b_product_sort_order'=> '',//B2B Manager
						'total'      => $product['total'],
						'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
						'reward'     => $product['reward']
					);
				}
			}
			// Gift Voucher
			$order_data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$order_data['vouchers'][] = array(
						'description'      => $voucher['description'],
						'code'             => oc_token(10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}
				// echo "<pre>"; print_r($this->request->post['hidden_images_name']); die;
			// Manage files
			if (isset($this->request->post['files_data'])) {
				$files_data = $this->request->post['files_data'];
				$file_names = []; // To store file names
				$original_file_names = [];
				if (!empty($files_data) && is_array($files_data)) {
					$upload_dir = DIR_OPENCART . "uploads/temp/";
					// Create the directory if it does not exist
					if (!is_dir($upload_dir)) {
						mkdir($upload_dir, 0755, true);
					}
					// To store original file names
					$original_file_names = $this->request->post['hidden_images_name'] ?? [];
					foreach ($files_data as $index => $file) {
						// Decoding the base64 image data
						list($type, $file_data) = explode(';', $file);
						list(, $file_data) = explode(',', $file_data);
						list(, $mime_extension) = explode('/', $type);
						$file_data = base64_decode($file_data);

						// Creating a unique file name
						$file_extension = '.' . $mime_extension;
						$file_name = uniqid().$file_extension;
						
						// Use original file name if available, else use a unique ID
    					$original_file_name = isset($original_file_names[$index]) ? $original_file_names[$index] : uniqid();
						
						// Saving the file
						$file_path = $upload_dir . $file_name;
						if (file_put_contents($file_path, $file_data)) {
							$file_names[] = $file_name; // Store file name
							$original_file_names[] = $original_file_name . $file_extension;
						}
					}
					$order_data['files_data'] = json_encode($file_names); // Store file names as JSON
					$order_data['original_file_name'] = json_encode($original_file_names); // Store file names as JSON
				}
			}
			
			// Order Totals
			$totals = [];
			$taxes = $this->cart->getTaxes();
			$total = 0;

			$this->load->model('checkout/cart');

			($this->model_checkout_cart->getTotals)($totals, $taxes, $total);

	
			$total_data = [
				'totals' => $totals,
				'taxes'  => $taxes,
				'total'  => $total
			];
			if($taxes){
				$total_data['totals'][] = array(
                    'extension' => 'opencart',
                    'code' => 'shipping_cost',
                    'title' => 'Shipping',
                    'value' => '',
                    'sort_order' => 3
                );
			}else{
				$total_data['totals'][] = array(
                    'extension' => 'opencart',
                    'code' => 'shipping_cost',
                    'title' => 'Shipping',
                    'value' => '',
                    'sort_order' => 3
                );
				$total_data['totals'][] = array(
                    'extension' => 'opencart',
                    'code' => 'tax',
                    'title' => 'Tax',
                    'value' => '',
                    'sort_order' => 5
                );
			}


			$order_data = array_merge($order_data, $total_data);
			
			if (isset($this->request->post['affiliate_id'])) {
			$subtotal = $this->cart->getSubTotal();

			// Affiliate
			$this->load->model('account/customer');

			$affiliate_info = $this->model_account_customer->getAffiliate($this->request->post['affiliate_id']);

			if ($affiliate_info) {
				$order_data['affiliate_id'] = $affiliate_info['customer_id'];
				$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
			}

			// Marketing
			$order_data['marketing_id'] = 0;
			$order_data['tracking'] = '';
		} else {
			$order_data['affiliate_id'] = 0;
			$order_data['commission'] = 0;
			$order_data['marketing_id'] = 0;
			$order_data['tracking'] = '';
		}
			
		if (isset($this->request->post['message'])) {
			$comment = $this->request->post['message'];
			
			if (isset($this->request->post['color_selects'])) {
				$decodedString = html_entity_decode($this->request->post['color_selects']);
				$colors = json_decode($decodedString, true);
				
				if (is_array($colors)) {
					$colorText = implode(", ", $colors); // Join array into comma-separated string
					$comment .= "\nColors: " . $colorText; // Append to message
				}
			}
			
			$order_data['comment'] = $comment;
		} else {
			$order_data['comment'] = '';
		}

		if (isset($this->request->post['file_final_sketch'])) {
			$order_data['file_final_sketch'] = $this->request->post['file_final_sketch'];
			} else {
				$order_data['file_final_sketch'] = '';
		}						

			
		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
		$order_data['currency_code'] = $this->session->data['currency'];
		$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];
		$order_data['total'] = $total_data['total'];
			
		if(isset($this->request->post['quotation_status_id']))
		{
			$order_data['quotation_status_id'] = $this->request->post['quotation_status_id'];
		}
		else
		{
			$order_data['quotation_status_id'] ='1';
		}
			
			
		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		} else {
			$order_data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		} else {
			$order_data['accept_language'] = '';
		}
		
		$this->load->model('checkout/quotation');

		$json['quotation_id'] = $this->model_checkout_quotation->addQuotation($order_data);
			$this->cart->clear();
			$json['redirect'] = $this->url->link('checkout/quotation.thankyou', 'language=' . $this->config->get('config_language'), true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function thankyou(): void {
		$this->document->setTitle('Tack för din offertförfrågan');
		if ($this->customer->isLogged()) {
			$data['first_name'] = $this->customer->getFirstName();
			$data['last_name'] = $this->customer->getLastName();
			$data['email'] = $this->customer->getEmail();
			$data['telephone'] = $this->customer->getTelephone();
			$data['company'] = $this->customer->getCompany();
		}

		$data['account'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language'));
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/thankyou', $data));
	}
	public function sendQuotationMail(){
		$quotation_id = $this->request->get['quotation_id'];
		$this->load->model('checkout/quotation');
		$result = $this->model_checkout_quotation->getQuotation($quotation_id);
		$this->load->model('catalog/product');
		$products = $this->model_checkout_quotation->getQuotationProducts($result['quotation_id']);
		$result['order_products'] = array();
		$product_options = array();
		foreach($products as $product){
			$product_options = $this->model_checkout_quotation->getQuotationOrderOptions($result['quotation_id'],$product['quotation_product_id']);
			foreach ($product_options as $order_option) {
				$ooption[$order_option['product_option_id']] = $order_option['product_option_value_id'];
			}
			$product_options = $this->model_checkout_quotation->getProductOptionsSel($product['product_id'], $ooption);
			$result['order_products'][] = array(
				'product_id' 		   => $product['product_id'],
				'name' 				   => $product['name'],
				'model' 			   => $product['model'],
				'quantity'     		   => $product['quantity'],
				'price'                => $product['price'],
				'total' 			   => $product['total'],
				'discount' 			   => $product['discount'],
				'option'   			   => $product_options
			);
		}
		$quotation_totals = $this->model_checkout_quotation->getQuotationOrderTotals($result['quotation_id']);
		foreach ($quotation_totals as $total) {
			if($total['title'] == 'Sub-Total'){
				$result['subtotal'] = number_format($total['value'],2);
			}elseif($total['title'] == 'Tax'){
				$result['tax'] = number_format($total['value'],2);
			}elseif($total['title'] == 'Shipping'){
				$result['shipping'] = number_format($total['value'],2);
			}elseif($total['title'] == 'Total'){
				$result['Total'] = number_format($total['value'],2);
			}
	 }


		$mail = new SendgridapiMail('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');
		$mail->setTo($result['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject($this->language->get('text_subject'));
		$mail->setHtml($this->load->view('mail/quatation_email', $result));
		$mail->send();
	}
	public function getCustomerByEmail(){
		$this->response->addHeader('Content-Type: application/json');
		$customer = [];
		$this->load->model('account/customer');
		if(isset($this->request->post['email']) && !empty($this->request->post['email'])) {
			$email = $this->request->post['email'];
			$customer = $this->model_account_customer->getCustomerByEmail($email);

		}
		if($customer) {
			$companyname='';
			$custom_field=json_decode($customer['custom_field'], true);
			if($custom_field){
				if(array_key_exists('1',$custom_field))
				{
					$companyname=$custom_field[1];
				}
			}
			$customer = array(
				'customer_id' => $customer['customer_id'],
				'firstname' => $customer['firstname'],
				'lastname' => $customer['lastname'],
				'company' => $companyname,
				'email' => $customer['email'],
				'telephone' => $customer['telephone'],
			);
		}else {
			$customer = null;
		}

		$this->response->setOutput(json_encode($customer));
	}
}
