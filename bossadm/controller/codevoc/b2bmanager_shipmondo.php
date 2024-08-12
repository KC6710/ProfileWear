<?php
namespace Opencart\Admin\Controller\Codevoc;
require_once(DIR_SYSTEM . 'library/dompdf/autoload.inc.php');

use Dompdf\Dompdf;
class B2bmanagerShipmondo extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function index() {

	}
    public function shipmondoCarriers(){

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
		echo $response;

	}	
	public function shipmondoProducts(){
		if(array_key_exists('carrierCode',$this->request->post)){
			$url = 'https://app.shipmondo.com/api/public/v3/products?sender_country_code=SE&receiver_country_code=SE&carrier_code='.$this->request->post['carrierCode'];
		}else if(array_key_exists('productType',$this->request->post)){
			$url = 'https://app.shipmondo.com/api/public/v3/products?sender_country_code=SE&receiver_country_code=SE&product_code='.$this->request->post['productType'];
		}
		
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
		echo $response;

	}

	public function shipmondoPackageType(){
		$productType = $this->request->post['productType'];
		
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
		echo $response;

	}

	public function getPackageType(){
		$productType = $this->request->post['productType'];
		
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
		echo $response;

	}
	public function orderAddress(){
		$this->load->model('codevoc/b2bmanager_order');
		$order_details = $this->model_codevoc_b2bmanager_order->getOrder($this->request->post['order_id']);
		$json['address'][] = array(
			'customer_id' 		  => 0,
			'type'        => 'custom',
			'firstname'   => 'Swedish Profile',
			'lastname'    => 'Wear AB',
			'email'       => 'hej@profilewear.se',
			'telephone'   => '+46762014816',
			'company'     => '',
			'address_1'   => 'Kajpromenaden 21',
			'address_2'   => '',
			'postcode'    => '25657',
			'city'        => 'Helsingborg',
			'zone'        => '',
			'zone_id'	  => '',
			'country_id'  => '',
			'country'     => '',
			'iso_code_2'  => 'SE',
			'iso_code_3'  => ''
		);

		$this->load->model('customer/customer');
		$payment_address = array();
		if(array_key_exists('payment_address_id', $order_details) && $order_details['payment_address_id'] != 0){
			$payment_address = $this->model_customer_customer->getAddress($order_details['payment_address_id']);
			$json['address'][] = array(
				'customer_id' => $order_details['customer_id'],
				'type'        => 'payment',
				'firstname'   => $payment_address['firstname'],
				'lastname'    => $payment_address['lastname'],
				'email'       => $order_details['email'],
				'telephone'   => $order_details['telephone'],
				'company'     => $payment_address['company'],
				'address_1'   => $payment_address['address_1'],
				'address_2'   => $payment_address['address_2'],
				'postcode'    => $payment_address['postcode'],
				'city'        => $payment_address['city'],
				'zone'        => $payment_address['zone'],
				'zone_id'	  => $payment_address['zone_id'],
				'country_id'  => $payment_address['country_id'],
				'country'     => $payment_address['country'],
				'iso_code_2'  => $payment_address['iso_code_2'],
				'iso_code_3'  => $payment_address['iso_code_3']
			);
		}else{
			$this->load->model('localisation/country');
			if($order_details['payment_country_id'] != 0){
				$payment_country_data = $this->model_localisation_country->getCountry($order_details['payment_country_id']);
			}else{
				$payment_country_data['iso_code_2'] = '';
				$payment_country_data['iso_code_3'] = '';
			}
			// if($order_details['payment_firstname'] || $order_details['payment_lastname'] && $order_details['email'] && $order_details['telephone'])
			$json['address'][] = array(
				'customer_id' => $order_details['customer_id'],
				'type'        => 'payment',
				'firstname'   => $order_details['payment_firstname'],
				'lastname'    => $order_details['payment_lastname'],
				'email'       => $order_details['email'],
				'telephone'   => $order_details['telephone'],
				'company'     => $order_details['payment_company'],
				'address_1'   => $order_details['payment_address_1'],
				'address_2'   => $order_details['payment_address_2'],
				'postcode'    => $order_details['payment_postcode'],
				'city'        => $order_details['payment_city'],
				'zone'        => $order_details['payment_zone'],
				'zone_id'	  => $order_details['payment_zone_id'],
				'country_id'  => $order_details['payment_country_id'],
				'country'     => $order_details['payment_country'],
				'iso_code_2'  => $payment_country_data['iso_code_2'] ? $payment_country_data['iso_code_2'] : '',
				'iso_code_3'  => $payment_country_data['iso_code_3'] ? $payment_country_data['iso_code_3'] :''
			);
		}
		if(array_key_exists('shipping_address_id', $order_details) && $order_details['shipping_address_id'] != 0){
			$shipping_address = $this->model_customer_customer->getAddress($order_details['shipping_address_id']);
			$json['address'][] = array(
				'customer_id' => $order_details['customer_id'],
				'type'        => 'shipping',
				'firstname'   => $shipping_address['firstname'],
				'lastname'    => $shipping_address['lastname'],
				'email'       => $order_details['email'],
				'telephone'   => $order_details['telephone'],
				'company'     => $shipping_address['company'],
				'address_1'   => $shipping_address['address_1'],
				'address_2'   => $shipping_address['address_2'],
				'postcode'    => $shipping_address['postcode'],
				'city'        => $shipping_address['city'],
				'zone'        => $shipping_address['zone'],
				'zone_id'	  => $shipping_address['zone_id'],
				'country_id'  => $shipping_address['country_id'],
				'country'     => $shipping_address['country'],
				'iso_code_2'  => $shipping_address['iso_code_2'],
				'iso_code_3'  => $shipping_address['iso_code_3']
			);
		}else{
			$this->load->model('localisation/country');
			if($order_details['shipping_country_id'] != 0){
				$shipping_country_data = $this->model_localisation_country->getCountry($order_details['shipping_country_id']);
			}else{
				$shipping_country_data['iso_code_2'] = '';
				$shipping_country_data['iso_code_3'] = '';
			}
			$json['address'][] = array(
				'customer_id' => $order_details['customer_id'],
				'type'        => 'shipping',
				'firstname'   => $order_details['shipping_firstname'],
				'lastname'    => $order_details['shipping_lastname'],
				'email'       => $order_details['email'],
				'telephone'   => $order_details['telephone'],
				'company'     => $order_details['shipping_company'],
				'address_1'   => $order_details['shipping_address_1'],
				'address_2'   => $order_details['shipping_address_2'],
				'postcode'    => $order_details['shipping_postcode'],
				'city'        => $order_details['shipping_city'],
				'zone'        => $order_details['shipping_zone'],
				'zone_id'	   => $order_details['shipping_zone_id'],
				'country_id'  => $order_details['shipping_country_id'],
				'country'     => $order_details['shipping_country'],
				'iso_code_2'     => $shipping_country_data['iso_code_2'],
				'iso_code_3'     => $shipping_country_data['iso_code_3']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    public function createShipment(){
		$this->load->model('codevoc/b2bmanager_order');
		$order_details = $this->model_codevoc_b2bmanager_order->getOrder($this->request->post['order_id']);
		if($this->request->post['from_address'] == 'custom'){
			$shipment['from_address'] = array(
				'customer_id' 		  => 0,
				'type'        => 'custom',
				'firstname'   => 'Swedish Profile',
				'lastname'    => 'Wear AB',
				'email'       => 'hej@profilewear.se',
				'telephone'   => '+46762014816',
				'company'     => '',
				'address_1'   => 'Kajpromenaden 21',
				'address_2'   => '',
				'postcode'    => '25667',
				'city'        => 'Helsingborg',
				'zone'        => '',
				'zone_id'	  => '',
				'country_id'  => '',
				'country'     => '',
				'iso_code_2'  => 'SE',
				'iso_code_3'  => ''
			);
		}else if($this->request->post['from_address'] == 'payment'){
			$this->load->model('customer/customer');
			$payment_address = array();
			if(array_key_exists('payment_address_id', $order_details) && $order_details['payment_address_id'] != 0){
				$payment_address = $this->model_customer_customer->getAddress($order_details['payment_address_id']);
				$shipment['from_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'payment',
					'firstname'   => $payment_address['firstname'],
					'lastname'    => $payment_address['lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $payment_address['company'],
					'address_1'   => $payment_address['address_1'],
					'address_2'   => $payment_address['address_2'],
					'postcode'    => $payment_address['postcode'],
					'city'        => $payment_address['city'],
					'zone'        => $payment_address['zone'],
					'zone_id'	  => $payment_address['zone_id'],
					'country_id'  => $payment_address['country_id'],
					'country'     => $payment_address['country'],
					'iso_code_2'  => $payment_address['iso_code_2'],
					'iso_code_3'  => $payment_address['iso_code_3']
				);
			}else{
				$this->load->model('localisation/country');
				if($order_details['payment_country_id'] != 0){
					$payment_country_data = $this->model_localisation_country->getCountry($order_details['payment_country_id']);
				}else{
					$payment_country_data['iso_code_2'] = '';
					$payment_country_data['iso_code_3'] = '';
				}
				// if($order_details['payment_firstname'] || $order_details['payment_lastname'] && $order_details['email'] && $order_details['telephone'])
				$shipment['from_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'payment',
					'firstname'   => $order_details['payment_firstname'],
					'lastname'    => $order_details['payment_lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $order_details['payment_company'],
					'address_1'   => $order_details['payment_address_1'],
					'address_2'   => $order_details['payment_address_2'],
					'postcode'    => $order_details['payment_postcode'],
					'city'        => $order_details['payment_city'],
					'zone'        => $order_details['payment_zone'],
					'zone_id'	  => $order_details['payment_zone_id'],
					'country_id'  => $order_details['payment_country_id'],
					'country'     => $order_details['payment_country'],
					'iso_code_2'  => $payment_country_data['iso_code_2'] ? $payment_country_data['iso_code_2'] : '',
					'iso_code_3'  => $payment_country_data['iso_code_3'] ? $payment_country_data['iso_code_3'] :''
				);
			}
		}else if($this->request->post['from_address'] == 'shipping'){
			if(array_key_exists('shipping_address_id', $order_details) && $order_details['shipping_address_id'] != 0){
				$shipping_address = $this->model_customer_customer->getAddress($order_details['shipping_address_id']);
				$shipment['from_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'shipping',
					'firstname'   => $shipping_address['firstname'],
					'lastname'    => $shipping_address['lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $shipping_address['company'],
					'address_1'   => $shipping_address['address_1'],
					'address_2'   => $shipping_address['address_2'],
					'postcode'    => $shipping_address['postcode'],
					'city'        => $shipping_address['city'],
					'zone'        => $shipping_address['zone'],
					'zone_id'	  => $shipping_address['zone_id'],
					'country_id'  => $shipping_address['country_id'],
					'country'     => $shipping_address['country'],
					'iso_code_2'  => $shipping_address['iso_code_2'],
					'iso_code_3'  => $shipping_address['iso_code_3']
				);
			}else{
				$this->load->model('localisation/country');
				if($order_details['shipping_country_id'] != 0){
					$shipping_country_data = $this->model_localisation_country->getCountry($order_details['shipping_country_id']);
				}else{
					$shipping_country_data['iso_code_2'] = '';
					$shipping_country_data['iso_code_3'] = '';
				}
				$shipment['from_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'shipping',
					'firstname'   => $order_details['shipping_firstname'],
					'lastname'    => $order_details['shipping_lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $order_details['shipping_company'],
					'address_1'   => $order_details['shipping_address_1'],
					'address_2'   => $order_details['shipping_address_2'],
					'postcode'    => $order_details['shipping_postcode'],
					'city'        => $order_details['shipping_city'],
					'zone'        => $order_details['shipping_zone'],
					'zone_id'	   => $order_details['shipping_zone_id'],
					'country_id'  => $order_details['shipping_country_id'],
					'country'     => $order_details['shipping_country'],
					'iso_code_2'     => $shipping_country_data['iso_code_2'],
					'iso_code_3'     => $shipping_country_data['iso_code_3']
				);
			}
		}

		if($this->request->post['pickup_address'] == 'custom'){
			$shipment['pickup_address'] = array(
				'customer_id' 		  => 0,
				'type'        => 'custom',
				'firstname'   => 'Swedish Profile',
				'lastname'    => 'Wear AB',
				'email'       => 'hej@profilewear.se',
				'telephone'   => '+46762014816',
				'company'     => '',
				'address_1'   => 'Kajpromenaden 21',
				'address_2'   => '',
				'postcode'    => '25667',
				'city'        => 'Helsingborg',
				'zone'        => '',
				'zone_id'	  => '',
				'country_id'  => '',
				'country'     => '',
				'iso_code_2'  => 'SE',
				'iso_code_3'  => ''
			);
		}else if($this->request->post['pickup_address'] == 'payment'){
			$this->load->model('customer/customer');
			$payment_address = array();
			if(array_key_exists('payment_address_id', $order_details) && $order_details['payment_address_id'] != 0){
				$payment_address = $this->model_customer_customer->getAddress($order_details['payment_address_id']);
				$shipment['pickup_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'payment',
					'firstname'   => $payment_address['firstname'],
					'lastname'    => $payment_address['lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $payment_address['company'],
					'address_1'   => $payment_address['address_1'],
					'address_2'   => $payment_address['address_2'],
					'postcode'    => $payment_address['postcode'],
					'city'        => $payment_address['city'],
					'zone'        => $payment_address['zone'],
					'zone_id'	  => $payment_address['zone_id'],
					'country_id'  => $payment_address['country_id'],
					'country'     => $payment_address['country'],
					'iso_code_2'  => $payment_address['iso_code_2'],
					'iso_code_3'  => $payment_address['iso_code_3']
				);
			}else{
				$this->load->model('localisation/country');
				if($order_details['payment_country_id'] != 0){
					$payment_country_data = $this->model_localisation_country->getCountry($order_details['payment_country_id']);
				}else{
					$payment_country_data['iso_code_2'] = '';
					$payment_country_data['iso_code_3'] = '';
				}
				// if($order_details['payment_firstname'] || $order_details['payment_lastname'] && $order_details['email'] && $order_details['telephone'])
				$shipment['pickup_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'payment',
					'firstname'   => $order_details['payment_firstname'],
					'lastname'    => $order_details['payment_lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $order_details['payment_company'],
					'address_1'   => $order_details['payment_address_1'],
					'address_2'   => $order_details['payment_address_2'],
					'postcode'    => $order_details['payment_postcode'],
					'city'        => $order_details['payment_city'],
					'zone'        => $order_details['payment_zone'],
					'zone_id'	  => $order_details['payment_zone_id'],
					'country_id'  => $order_details['payment_country_id'],
					'country'     => $order_details['payment_country'],
					'iso_code_2'  => $payment_country_data['iso_code_2'] ? $payment_country_data['iso_code_2'] : '',
					'iso_code_3'  => $payment_country_data['iso_code_3'] ? $payment_country_data['iso_code_3'] :''
				);
			}
		}else if($this->request->post['pickup_address'] == 'shipping'){
			if(array_key_exists('shipping_address_id', $order_details) && $order_details['shipping_address_id'] != 0){
				$shipping_address = $this->model_customer_customer->getAddress($order_details['shipping_address_id']);
				$shipment['pickup_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'shipping',
					'firstname'   => $shipping_address['firstname'],
					'lastname'    => $shipping_address['lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $shipping_address['company'],
					'address_1'   => $shipping_address['address_1'],
					'address_2'   => $shipping_address['address_2'],
					'postcode'    => $shipping_address['postcode'],
					'city'        => $shipping_address['city'],
					'zone'        => $shipping_address['zone'],
					'zone_id'	  => $shipping_address['zone_id'],
					'country_id'  => $shipping_address['country_id'],
					'country'     => $shipping_address['country'],
					'iso_code_2'  => $shipping_address['iso_code_2'],
					'iso_code_3'  => $shipping_address['iso_code_3']
				);
			}else{
				$this->load->model('localisation/country');
				if($order_details['shipping_country_id'] != 0){
					$shipping_country_data = $this->model_localisation_country->getCountry($order_details['shipping_country_id']);
				}else{
					$shipping_country_data['iso_code_2'] = '';
					$shipping_country_data['iso_code_3'] = '';
				}
				$shipment['pickup_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'shipping',
					'firstname'   => $order_details['shipping_firstname'],
					'lastname'    => $order_details['shipping_lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $order_details['shipping_company'],
					'address_1'   => $order_details['shipping_address_1'],
					'address_2'   => $order_details['shipping_address_2'],
					'postcode'    => $order_details['shipping_postcode'],
					'city'        => $order_details['shipping_city'],
					'zone'        => $order_details['shipping_zone'],
					'zone_id'	   => $order_details['shipping_zone_id'],
					'country_id'  => $order_details['shipping_country_id'],
					'country'     => $order_details['shipping_country'],
					'iso_code_2'     => $shipping_country_data['iso_code_2'],
					'iso_code_3'     => $shipping_country_data['iso_code_3']
				);
			}
		}

		if($this->request->post['delivery_address'] == 'payment'){
			$this->load->model('customer/customer');
			$payment_address = array();
			if(array_key_exists('payment_address_id', $order_details) && $order_details['payment_address_id'] != 0){
				$payment_address = $this->model_customer_customer->getAddress($order_details['payment_address_id']);
				$shipment['delivery_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'payment',
					'firstname'   => $payment_address['firstname'],
					'lastname'    => $payment_address['lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $payment_address['company'],
					'address_1'   => $payment_address['address_1'],
					'address_2'   => $payment_address['address_2'],
					'postcode'    => $payment_address['postcode'],
					'city'        => $payment_address['city'],
					'zone'        => $payment_address['zone'],
					'zone_id'	  => $payment_address['zone_id'],
					'country_id'  => $payment_address['country_id'],
					'country'     => $payment_address['country'],
					'iso_code_2'  => $payment_address['iso_code_2'],
					'iso_code_3'  => $payment_address['iso_code_3']
				);
			}else{
				$this->load->model('localisation/country');
				if($order_details['payment_country_id'] != 0){
					$payment_country_data = $this->model_localisation_country->getCountry($order_details['payment_country_id']);
				}else{
					$payment_country_data['iso_code_2'] = '';
					$payment_country_data['iso_code_3'] = '';
				}
				// if($order_details['payment_firstname'] || $order_details['payment_lastname'] && $order_details['email'] && $order_details['telephone'])
				$shipment['delivery_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'payment',
					'firstname'   => $order_details['payment_firstname'],
					'lastname'    => $order_details['payment_lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $order_details['payment_company'],
					'address_1'   => $order_details['payment_address_1'],
					'address_2'   => $order_details['payment_address_2'],
					'postcode'    => $order_details['payment_postcode'],
					'city'        => $order_details['payment_city'],
					'zone'        => $order_details['payment_zone'],
					'zone_id'	  => $order_details['payment_zone_id'],
					'country_id'  => $order_details['payment_country_id'],
					'country'     => $order_details['payment_country'],
					'iso_code_2'  => $payment_country_data['iso_code_2'] ? $payment_country_data['iso_code_2'] : '',
					'iso_code_3'  => $payment_country_data['iso_code_3'] ? $payment_country_data['iso_code_3'] :''
				);
			}
		}else if($this->request->post['delivery_address'] == 'shipping'){
			if(array_key_exists('shipping_address_id', $order_details) && $order_details['shipping_address_id'] != 0){
				$shipping_address = $this->model_customer_customer->getAddress($order_details['shipping_address_id']);
				$shipment['delivery_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'shipping',
					'firstname'   => $shipping_address['firstname'],
					'lastname'    => $shipping_address['lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $shipping_address['company'],
					'address_1'   => $shipping_address['address_1'],
					'address_2'   => $shipping_address['address_2'],
					'postcode'    => $shipping_address['postcode'],
					'city'        => $shipping_address['city'],
					'zone'        => $shipping_address['zone'],
					'zone_id'	  => $shipping_address['zone_id'],
					'country_id'  => $shipping_address['country_id'],
					'country'     => $shipping_address['country'],
					'iso_code_2'  => $shipping_address['iso_code_2'],
					'iso_code_3'  => $shipping_address['iso_code_3']
				);
			}else{
				$this->load->model('localisation/country');
				if($order_details['shipping_country_id'] != 0){
					$shipping_country_data = $this->model_localisation_country->getCountry($order_details['shipping_country_id']);
				}else{
					$shipping_country_data['iso_code_2'] = '';
					$shipping_country_data['iso_code_3'] = '';
				}
				$shipment['delivery_address'] = array(
					'customer_id' => $order_details['customer_id'],
					'type'        => 'shipping',
					'firstname'   => $order_details['shipping_firstname'],
					'lastname'    => $order_details['shipping_lastname'],
					'email'       => $order_details['email'],
					'telephone'   => $order_details['telephone'],
					'company'     => $order_details['shipping_company'],
					'address_1'   => $order_details['shipping_address_1'],
					'address_2'   => $order_details['shipping_address_2'],
					'postcode'    => $order_details['shipping_postcode'],
					'city'        => $order_details['shipping_city'],
					'zone'        => $order_details['shipping_zone'],
					'zone_id'	   => $order_details['shipping_zone_id'],
					'country_id'  => $order_details['shipping_country_id'],
					'country'     => $order_details['shipping_country'],
					'iso_code_2'     => $shipping_country_data['iso_code_2'],
					'iso_code_3'     => $shipping_country_data['iso_code_3']
				);
			}
		}
		$addons = array();
		foreach($this->request->post['service_addons'] as $key=>$addon){
			$addons[] = $key;
		}

		$services = implode(",",$addons);

		$curl = curl_init();

		curl_setopt_array($curl, [
		CURLOPT_URL => "https://app.shipmondo.com/api/public/v3/shipments",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => json_encode([
			'test_mode' => true,
			'own_agreement' => false,
			'label_format' => 'a4_pdf',
			'product_code' => $this->request->post['product_type'],
			'service_codes' => $services,
			'reference' => $this->request->post['order_id'],
			'automatic_select_service_point' => null,
			'sender' => [
				'name' => $shipment['from_address']['firstname'].' '. $shipment['from_address']['lastname'],
				'attention' => $shipment['from_address']['company'],
				'address1' => $shipment['from_address']['address_1'],
				'address2' => $shipment['from_address']['address_2'],
				'zipcode' => $shipment['from_address']['postcode'],
				'city' => $shipment['from_address']['city'],
				'country_code' => $shipment['from_address']['iso_code_2'],
				'email' => $shipment['from_address']['email'],
				'mobile' => $shipment['from_address']['telephone']
			],
			'receiver' => [
				'name' => $shipment['delivery_address']['firstname'].' '. $shipment['delivery_address']['lastname'],
				'attention' => $shipment['delivery_address']['company'],
				'address1' => $shipment['delivery_address']['address_1'],
				'address2' => $shipment['delivery_address']['address_2'],
				'zipcode' => $shipment['delivery_address']['postcode'],
				'city' => $shipment['delivery_address']['city'],
				'country_code' => $shipment['delivery_address']['iso_code_2'],
				'email' => $shipment['delivery_address']['email'],
				'mobile' => $shipment['delivery_address']['telephone']
			],
			'parcels' => [
				[
						'weight' => $this->request->post['weight'],
						'height' => $this->request->post['height'],
						'width' => $this->request->post['width'],
						'length' => $this->request->post['Length'],
						'packaging' => $this->request->post['packing_type']
				]
			]
		]),
		CURLOPT_HTTPHEADER => [
			"Accept: application/json",
			"Authorization: Basic NmY2Y2VkOTktYTFmOC00YmQ3LWJiNzEtYTAwMjhjYjQyNzdhOmQ5Nzg2OGFlLWRkMjctNDQ1My04YjU5LTAxMTgyN2E2MDgyYQ==",
			"Content-Type: application/json"
		],
		]);
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$json['error'] = $err;
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
		$shipment_details = json_decode($response);
		}

		if($shipment_details){
			$label_data = array(
				'shipment_id' 	=> $shipment_details->id,
				'order_id'      => $this->request->post['order_id'],
				'weight'      	=> $shipment_details->parcels[0]->weight,
				'quantity'    	=> $shipment_details->parcels[0]->quantity,
				'carrier_code'  => $shipment_details->carrier_code,
				'product_code'	=> $shipment_details->product_code,
				'service_codes' => $shipment_details->service_codes,
				'pdf_data'      => $shipment_details->labels[0]->base64,
				'package_type'  => $shipment_details->parcels[0]->package_type,
				'created_at'    => $shipment_details->created_at,

			);

			$this->load->model('codevoc/b2bmanager_shipmondo');
			$shipment_id = $this->model_codevoc_b2bmanager_shipmondo->insertDetails($label_data);
			$json['success'] = 'Shipment created successfully';
			$json['shipment_id'] = $shipment_id;
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
	public function downloadShipmentPdf(){
		$id = $this->request->get['shipment_id'];
		$this->load->model('codevoc/b2bmanager_shipmondo'); 
		$shipment_details = $this->model_codevoc_b2bmanager_shipmondo->getPdfData($id);
		if(!$shipment_details['pdf_id']){
			$pdf_data = $shipment_details['pdf_data'];
			$pdf_decoded = base64_decode ($pdf_data);
			$pdf = fopen ('view/shipment/pdfs/'.$shipment_details['shipment_id'].'.pdf','w');
			fwrite($pdf,$pdf_decoded);
			//close output file
			fclose($pdf);
			$pdf_id = $this->model_codevoc_b2bmanager_shipmondo->insertPdfId($id,$shipment_details['shipment_id']);
			header('Content-Description: File Transfer');
			header('Set-Cookie: fileDownload=true; path=/');
			header("Content-type:application/pdf");
			header("Content-Disposition:attachment;filename=\"".$shipment_details['shipment_id'].".pdf\"");
			readfile('view/shipment/pdfs/'.$shipment_details['shipment_id'].'.pdf');
		}else{
			header('Content-Description: File Transfer');
			header('Set-Cookie: fileDownload=true; path=/');
			header("Content-type:application/pdf");
			header("Content-Disposition:attachment;filename=\"".$shipment_details['pdf_id'].".pdf\"");
			readfile('view/shipment/pdfs/'.$shipment_details['shipment_id'].'.pdf');
		}
	}

}