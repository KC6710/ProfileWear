<?php
namespace Opencart\Admin\Controller\Codevoc;
class B2bmanagerFortnox extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function index() {

	}

	public function createinvoice() {

		$json = array();
		//here we can run the API
		if (isset($this->request->get['order_id'])) {
			$this->load->model('sale/order');
			$this->load->model('customer/customer');
			$this->load->model('codevoc/b2bmanager_fortnox');

			$order_id = $this->request->get['order_id'];
			$order = $this->model_sale_order->getOrder($order_id);
			$customer_id = $order['customer_id'];
			$oc_customer = $this->model_customer_customer->getCustomer($customer_id);
			// print_r($order); die;
			if(array_key_exists('payment_address_id',$order)){
				$address_id = $oc_customer['payment_address_id'];
				$address = $this->model_customer_customer->getAddress($address_id);
			}else{
				$address = [
					// 'address_id'     => $order['address_id'],
					// 'customer_id'    => $order['customer_id'],
					'firstname'      => $order['payment_firstname'],
					'lastname'       => $order['payment_lastname'],
					'company'        => $order['payment_company'],
					'address_1'      => $order['payment_address_1'],
					'address_2'      => $order['payment_address_2'],
					'postcode'       => $order['payment_postcode'],
					'city'           => $order['payment_city'],
					'zone_id'        => $order['payment_zone_id'],
					'zone'           => $order['payment_zone'],
					'zone_code'      => $order['payment_zone_code'],
					'country_id'     => $order['payment_country_id'],
					'country'        => $order['payment_country'],
					'iso_code_2'     => $order['payment_iso_code_2'],
					'iso_code_3'     => $order['payment_iso_code_3'],
					'address_format' => $order['payment_address_format'],
					// 'custom_field'   => json_decode($order['payment_custom_field'], true),
					// 'default'        => $order['default']
				];
			}

			$order_products = $this->model_sale_order->getProducts($order_id);
			$order_totals = $this->model_sale_order->getTotals($order_id);

			/* Retrive customer */
			$customer = $this->getFortnoxCustomer($customer_id);

			/* if customer not exist then create new customer in fortnox */
			if(!$customer) {
				$this->createFortnoxCustomer($oc_customer, $address);
			}

			$data = [
				'order' => $order,
				'customer' => $oc_customer,
				'address' => $address,
				'products' => $order_products,
				'totals' => $order_totals,
			];
			$result = $this->generateFortnoxInvoice($data);
			/* If unable to create invoice for order */
			if(!$result['status']) {
				$json['error'] = $result['error'];
			} else {
				$this->model_codevoc_b2bmanager_fortnox->attachOrderToInvoice($order_id, $result['data']['Invoice']['DocumentNumber']);
				$json['fortnox_invoice_no'] = $result['data']['Invoice']['DocumentNumber'];
			}
		} else {
			$json['error'] = 'Order id not provided.';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	private function getFortnoxCustomer($customerNumber) {

		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://apps.fortnox.se/3/customers/'. $customerNumber,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_FAILONERROR => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			// 'Access-Token: c7118f61-4186-4a65-b044-749aa04982f2',
			// 'Client-Secret: paxYcGcFhy'
		),
		));

		$response = curl_exec($curl);

		$error_msg = false;
		if (curl_errno($curl)) {
			$error_msg = curl_error($curl);
		}

		if($error_msg) {
			curl_close($curl);
			return false;
		}

		curl_close($curl);
		return json_decode($response, true);

	}

	private function generateFortnoxInvoice($data) {
		$curl = curl_init();

		$order = $data['order'];
		$customer = $data['customer'];
		$address = $data['address'];
		$products = $data['products'];
		$totals = $data['totals'];
		# var_dump($this->generateOrderProductsString($products)); exit;
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.fortnox.se/3/invoices',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_FAILONERROR => true,
			CURLOPT_POSTFIELDS =>'{
			"Invoice": {
				"CustomerNumber": "'. $customer['customer_id'] .'",
				"Address1": "'. $address['address_1'] .'",
				"Address2": "'. $address['address_2'] .'",
				"City": "'. $address['city'] .'",
				"Country": "Sverige",
				"Currency": "SEK",
				"CustomerName": "'. $customer['firstname'] .' '. $customer['lastname'] .'",
				"EmailInformation": {
				"EmailAddressTo": "'. $customer['email'] .'"
				},
				"Freight": '. $this->getShippingCost($totals) .',
				"InvoiceDate": "'. date('Y-m-d') .'",
				"InvoiceRows": ['. $this->generateOrderProductsString($products) .'],				
				"Phone1": "'. $customer['telephone'] .'"
			}
			}',
			CURLOPT_HTTPHEADER => array(
				'Access-Token: c7118f61-4186-4a65-b044-749aa04982f2',
				'Client-Secret: paxYcGcFhy',
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);

		$error_msg = false;
		if (curl_errno($curl)) {
			$error_msg = curl_error($curl);
		}

		if($error_msg) {
			curl_close($curl);
			return [
				'status' => false,
				'error' => $error_msg
			];
		}

		curl_close($curl);
		return [
			'status' => true,
			'data' => json_decode($response, true)
		];
	}

	private function createFortnoxCustomer($customer, $address) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://apps.fortnox.se/3/customers',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_FAILONERROR => true,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>'{
		"Customer": {
			"Address1": "'. $address['address_1'] .'",
			"Address2": "'. $address['address_2'] .'",
			"City": "'. $address['city'] .'",
			"Currency": "SEK",
			"CountryCode": "SE",
			"Active": true,
			"CustomerNumber": "'. $customer['customer_id'] .'",
			"Email": "'. $customer['email'] .'",
			"Name": "'. $customer['firstname'] .' '. $customer['lastname'] .'",
			"Phone1": "'. $customer['telephone'] .'",
			"ZipCode": "'. $address['postcode'] .'"
		}
		}',
		CURLOPT_HTTPHEADER => array(
			'Access-Token: c7118f61-4186-4a65-b044-749aa04982f2',
			'Client-Secret: paxYcGcFhy',
			'Content-Type: application/json'
		),
		));

		$response = curl_exec($curl);
		$error_msg = false;
		if (curl_errno($curl)) {
			$error_msg = curl_error($curl);
		}

		if($error_msg) {
			curl_close($curl);
			return false;
		}

		curl_close($curl);
		return json_decode($response, true);
	}

	private function generateOrderProductsString($products) {
		$products_string = [];
		foreach($products as $product) {
			$products_string[] = '{
				"ArticleNumber": "1001",
				"DeliveredQuantity": "'. $product['quantity'] .'",
				"Description": "'. $product['name'] .'",
				"Price": '. $product['price'] .'
			}';
		}
		return implode(',', $products_string);
	}

	private function getShippingCost($totals) {
		$shipping = 0;
		foreach($totals as $total) {
			if($total['code'] == 'shipping') {
				$shipping = $total['value'];
				break;
			}
		}

		return $shipping;
	}

}