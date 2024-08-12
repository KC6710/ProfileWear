<?php
namespace Opencart\Catalog\Controller\Api\Sale;
class Quotation extends \Opencart\System\Engine\Controller {
	public function add() {
		$this->load->language('api/sale/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) 
		{
			$json['error'] = $this->language->get('error_permission');
		} 
		else 
		{
				
			// Customer
			if (!isset($this->session->data['customer'])) {
				$json['error'] = $this->language->get('error_customer');
			}
			// Cart
			if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
				$json['error'] = $this->language->get('error_stock');
			}
			// Validate minimum quantity requirements.
			$products = $this->cart->getProducts();

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
			if (!$json) 
			{
					$json['success'] = $this->language->get('text_success');
					$order_data = array();
					
					//custom data
					if(isset($this->request->post['assignee'])){$order_data['assignee']=$this->request->post['assignee'];}else{$order_data['assignee']='';}
					if(isset($this->request->post['vatnr'])){$order_data['vatnr']=$this->request->post['vatnr'];}else{$order_data['vatnr']='';}
					if(isset($this->request->post['payment_company'])){$order_data['payment_company']=$this->request->post['payment_company'];}else{$order_data['payment_company']='';}
					if(isset($this->request->post['shipping_company'])){$order_data['shipping_company']=$this->request->post['shipping_company'];}else{$order_data['shipping_company']='';}
					if(isset($this->request->post['create_date'])){$order_data['create_date']=$this->request->post['create_date'];}else{$order_data['create_date']='';}
					if(isset($this->request->post['delivery_date'])){$order_data['delivery_date']=$this->request->post['delivery_date'];}else{$order_data['delivery_date']='';}
					if(isset($this->request->post['expiration_date'])){$order_data['expiration_date']=$this->request->post['expiration_date'];}else{$order_data['expiration_date']='';}
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
						$order_data['customer_id'] = $this->session->data['customer']['customer_id'];
						$order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
						$order_data['firstname'] = $this->session->data['customer']['firstname'];
						$order_data['lastname'] = $this->session->data['customer']['lastname'];
						$order_data['email'] = $this->session->data['customer']['email'];
						$order_data['telephone'] = $this->session->data['customer']['telephone'];
						$order_data['custom_field'] = $this->session->data['customer']['custom_field'];


					
					// Payment Details
					$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
					$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
					$order_data['payment_company'] = $this->session->data['payment_address']['company'];
					$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
					$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
					$order_data['payment_city'] = $this->session->data['payment_address']['city'];
					$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
					$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
					$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
					$order_data['payment_country'] = $this->session->data['payment_address']['country'];
					$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
					$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
					$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

					if (isset($this->session->data['payment_method']['title'])) {
						$order_data['payment_method'] = $this->session->data['payment_method']['title'];
					} else {
						$payment_method=$this->session->data['payment_methods'][$this->request->post['payment_method']];	
						$order_data['payment_method'] = $payment_method['title'];
					}

					if (isset($this->session->data['payment_method']['code'])) {
						$order_data['payment_code'] = $this->session->data['payment_method']['code'];
					} else {
						
						$payment_method=$this->session->data['payment_methods'][$this->request->post['payment_method']];	
						$order_data['payment_code'] = $payment_method['code'];
					}

					// Shipping Details
					if ($this->cart->hasShipping()) {
						$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
						$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
						$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
						$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
						$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
						$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
						$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
						$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
						$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
						$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
						$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
						$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
						$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

						if (isset($this->session->data['shipping_method']['title'])) {
							$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
						} else {
							$shipping = explode('.', $this->request->post['shipping_method']);
							$shipping_method = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
							$order_data['shipping_method'] = $shipping_method['title'];
							
						}

						if (isset($this->session->data['shipping_method']['code'])) {
							$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
						} else {
							$shipping = explode('.', $this->request->post['shipping_method']);
							$shipping_method = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
							$order_data['shipping_code'] = $shipping_method['code'];
						}
					} else {
						$order_data['shipping_firstname'] = '';
						$order_data['shipping_lastname'] = '';
						$order_data['shipping_company'] = '';
						$order_data['shipping_address_1'] = '';
						$order_data['shipping_address_2'] = '';
						$order_data['shipping_city'] = '';
						$order_data['shipping_postcode'] = '';
						$order_data['shipping_zone'] = '';
						$order_data['shipping_zone_id'] = '';
						$order_data['shipping_country'] = '';
						$order_data['shipping_country_id'] = '';
						$order_data['shipping_address_format'] = '';
						$order_data['shipping_custom_field'] = array();
						$order_data['shipping_method'] = '';
						$order_data['shipping_code'] = '';
					}

					
					// Products
					$order_data['products'] = array();

					foreach ($this->cart->getProducts() as $product) {
						$option_data = array();

						foreach ($product['option'] as $option) {
							$option_data[] = array(
								'product_option_id'       => $option['product_option_id'],
								'product_option_value_id' => $option['product_option_value_id'],
								'option_id'               => $option['option_id'],
								'option_value_id'         => $option['option_value_id'],
								'name'                    => $option['name'],
								'value'                   => $option['value'],
								'type'                    => $option['type']
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
							'b2b_product_discount'=> $product['b2b_product_discount'],//B2B Manager
							'b2b_product_tax_class_id'=> $product['b2b_product_tax_class_id'],//B2B Manager
							'b2b_product_sort_order'=> $product['b2b_product_sort_order'],//B2B Manager
							'total'      => $product['total'],
							'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
							'reward'     => $product['reward']
						);
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
					
					// controller manage file
					if (isset($this->request->post['files_data'])) {
						$files_data = $this->request->post['files_data'];
						if ($files_data != "") {
							$file_name_array = explode('__|__', $files_data);
							$file_array = array();
							foreach ($file_name_array as $file) {
								$file_name_array[] = $file;
								$path = str_replace("/catalog", "", DIR_APPLICATION) . "uploads/temp/" . $file;
								$file_url = HTTP_SERVER . "uploads/temp/" . rawurlencode($file);
								//$url = $this->url->link('codevoc/b2bmanager_quotation/downloadfile', 'user_token=' . $this->session->data['user_token'] . "&name=" . $file . '&url=' . $file_url, true);
								$file_array[] = array(
									'name' => $file,
									'original_name' => $file,
									'size' => @filesize($path),
									'type' => @mime_content_type($path),
									'url' => '', // download url
									'fileurl' => $file_url,
									'deleteUrl' => '',
									'deleteType' => ''
								);
							}
							$file_data = implode('__|__', $file_name_array);
							$order_data['files_data'] = $file_data;
							$order_data['files'] = json_encode($file_array);

						}
					} else {
						$order_data['files_data'] = '';
						$order_data['files'] = json_encode(array());
					}
				//attachment

				
					// Order Totals
					$this->load->model('setting/extension');

					$totals = array();
					$taxes = $this->cart->getTaxes();
					$total = 0;

					// Because __call can not keep var references so we put them into an array.
					$total_data = array(
						'totals' => &$totals,
						'taxes'  => &$taxes,
						'total'  => &$total
					);
				
					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							$this->load->model('extension/total/' . $result['code']);
							
							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($total_data['totals'] as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $total_data['totals']);

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
				
				if (isset($this->request->post['comment'])) {
				$order_data['comment'] = $this->request->post['comment'];
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
				}
				
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('checkout/quotation');

			if (isset($this->request->get['quotation_id'])) {
				$quotation_id = $this->request->get['quotation_id'];
			} else {
				$quotation_id = 0;
			}

			$quotation_info = $this->model_checkout_quotation->getQuotation($quotation_id);


			if ($quotation_info) {
				// Customer
				if (!isset($this->session->data['customer'])) {
					$json['error'] = $this->language->get('error_customer');
				}

				// Cart
				if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
					$json['error'] = $this->language->get('error_stock');
				}

				// Validate minimum quantity requirements.
				$products = $this->cart->getProducts();

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

				if (!$json) {
					
					$json['success'] = $this->language->get('text_success');
					
					$order_data = array();

					// Store Details
					
							//custom data
							if(isset($this->request->post['assignee'])){$order_data['assignee']=$this->request->post['assignee'];}else{$order_data['assignee']='';}
							if(isset($this->request->post['vatnr'])){$order_data['vatnr']=$this->request->post['vatnr'];}else{$order_data['vatnr']='';}
							if(isset($this->request->post['payment_company'])){$order_data['payment_company']=$this->request->post['payment_company'];}else{$order_data['payment_company']='';}
							if(isset($this->request->post['shipping_company'])){$order_data['shipping_company']=$this->request->post['shipping_company'];}else{$order_data['shipping_company']='';}
							if(isset($this->request->post['create_date'])){$order_data['create_date']=$this->request->post['create_date'];}else{$order_data['create_date']='';}
							if(isset($this->request->post['delivery_date'])){$order_data['delivery_date']=$this->request->post['delivery_date'];}else{$order_data['delivery_date']='';}
							if(isset($this->request->post['expiration_date'])){$order_data['expiration_date']=$this->request->post['expiration_date'];}else{$order_data['expiration_date']='';}
							if(isset($this->request->post['shippment_terms'])){$order_data['shippment_terms']=$this->request->post['shippment_terms'];}else{$order_data['shippment_terms']='';}
							if(isset($this->request->post['rate_delay'])){$order_data['rate_delay']=$this->request->post['rate_delay'];}else{$order_data['rate_delay']='';}
							if(isset($this->request->post['custom_ordernr'])){$order_data['custom_ordernr']=$this->request->post['custom_ordernr'];}else{$order_data['custom_ordernr']='';}
							//custom data
							
							
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
							$order_data['customer_id'] = $this->session->data['customer']['customer_id'];
							$order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
							$order_data['firstname'] = $this->session->data['customer']['firstname'];
							$order_data['lastname'] = $this->session->data['customer']['lastname'];
							$order_data['email'] = $this->session->data['customer']['email'];
							$order_data['telephone'] = $this->session->data['customer']['telephone'];
					 		$order_data['custom_field'] = $this->session->data['customer']['custom_field'];
							
							
							// Payment Details
							$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
							$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
							$order_data['payment_company'] = $this->session->data['payment_address']['company'];
							$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
							$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
							$order_data['payment_city'] = $this->session->data['payment_address']['city'];
							$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
							$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
							$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
							$order_data['payment_country'] = $this->session->data['payment_address']['country'];
							$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
							$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
							$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

							if (isset($this->session->data['payment_method']['title'])) {
								$order_data['payment_method'] = $this->session->data['payment_method']['title'];
							} else {
								$payment_method=$this->session->data['payment_methods'][$this->request->post['payment_method']];	
								$order_data['payment_method'] = $payment_method['title'];
							}

							if (isset($this->session->data['payment_method']['code'])) {
								$order_data['payment_code'] = $this->session->data['payment_method']['code'];
							} else {
								
								$payment_method=$this->session->data['payment_methods'][$this->request->post['payment_method']];	
								$order_data['payment_code'] = $payment_method['code'];
							}

							// Shipping Details
							if ($this->cart->hasShipping()) {
								$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
								$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
								$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
								$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
								$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
								$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
								$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
								$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
								$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
								$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
								$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
								$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
								$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

								if (isset($this->session->data['shipping_method']['title'])) {
									$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
								} else {
									$shipping = explode('.', $this->request->post['shipping_method']);
									$shipping_method = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
									$order_data['shipping_method'] = $shipping_method['title'];
									
								}

								if (isset($this->session->data['shipping_method']['code'])) {
									$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
								} else {
									$shipping = explode('.', $this->request->post['shipping_method']);
									$shipping_method = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
									$order_data['shipping_code'] = $shipping_method['code'];
								}
							} else {
								$order_data['shipping_firstname'] = '';
								$order_data['shipping_lastname'] = '';
								$order_data['shipping_company'] = '';
								$order_data['shipping_address_1'] = '';
								$order_data['shipping_address_2'] = '';
								$order_data['shipping_city'] = '';
								$order_data['shipping_postcode'] = '';
								$order_data['shipping_zone'] = '';
								$order_data['shipping_zone_id'] = '';
								$order_data['shipping_country'] = '';
								$order_data['shipping_country_id'] = '';
								$order_data['shipping_address_format'] = '';
								$order_data['shipping_custom_field'] = array();
								$order_data['shipping_method'] = '';
								$order_data['shipping_code'] = '';
							}
							
							// Products
							$order_data['products'] = array();

							foreach ($this->cart->getProducts() as $product) {
								$option_data = array();

								foreach ($product['option'] as $option) {
									$option_data[] = array(
										'product_option_id'       => $option['product_option_id'],
										'product_option_value_id' => $option['product_option_value_id'],
										'option_id'               => $option['option_id'],
										'option_value_id'         => $option['option_value_id'],
										'name'                    => $option['name'],
										'value'                   => $option['value'],
										'type'                    => $option['type']
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
									'b2b_product_discount'=> $product['b2b_product_discount'],//B2B Manager
									'b2b_product_tax_class_id'=> $product['b2b_product_tax_class_id'],//B2B Manager
									'b2b_product_sort_order'=> $product['b2b_product_sort_order'],//B2B Manager
									'total'      => $product['total'],
									'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
									'reward'     => $product['reward']
								);
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
							
							
				// controller manage file
				if (isset($this->request->post['files_data'])) {
					$files_data = $this->request->post['files_data'];
					if ($files_data != "") {
						$file_name_array = explode('__|__', $files_data);
						$file_array = array();
						foreach ($file_name_array as $file) {
							$file_name_array[] = $file;
							$path = str_replace("/catalog", "", DIR_APPLICATION) . "uploads/temp/" . $file;
							
							$file_url = HTTP_SERVER . "uploads/temp/" . rawurlencode($file);
							//$url = $this->url->link('codevoc/b2bmanager_quotation/downloadfile', 'user_token=' . $this->session->data['user_token'] . "&name=" . $file . '&url=' . $file_url, true);
							$file_array[] = array(
								'name' => $file,
								'original_name' => $file,
								'size' => @filesize($path),
								'type' => @mime_content_type($path),
								'url' => '', // download url
								'fileurl' => $file_url,
								'deleteUrl' => '',
								'deleteType' => ''
							);
						}
						$file_data = implode('__|__', $file_name_array);
						$order_data['files_data'] = $file_data;
						$order_data['files'] = json_encode($file_array);

					}
				} else {
					$order_data['files_data'] = '';
					$order_data['files'] = json_encode(array());
				}
			//attachment


							// Order Totals
							$this->load->model('setting/extension');

							$totals = array();
							$taxes = $this->cart->getTaxes();
							$total = 0;

							// Because __call can not keep var references so we put them into an array.
							$total_data = array(
								'totals' => &$totals,
								'taxes'  => &$taxes,
								'total'  => &$total
							);
						
							$sort_order = array();

							$results = $this->model_setting_extension->getExtensions('total');

							foreach ($results as $key => $value) {
								$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
							}

							array_multisort($sort_order, SORT_ASC, $results);

							foreach ($results as $result) {
								if ($this->config->get('total_' . $result['code'] . '_status')) {
									$this->load->model('extension/total/' . $result['code']);
									
									// We have to put the totals in an array so that they pass by reference.
									$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
								}
							}

							$sort_order = array();

							foreach ($total_data['totals'] as $key => $value) {
								$sort_order[$key] = $value['sort_order'];
							}

							array_multisort($sort_order, SORT_ASC, $total_data['totals']);

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
						
						if (isset($this->request->post['comment'])) {
						$order_data['comment'] = $this->request->post['comment'];
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
					
					
					$this->model_checkout_quotation->editQuotation($quotation_id, $order_data);

					
					
				}
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	//Quotation shipping & payment methods fixing 
	public function getShippingPaymentMethods()
	{
			$this->load->language('api/order');

			$json = array();
		
			if (!isset($this->session->data['api_id'])) {
					$json['error'] = $this->language->get('error_permission');
			} else {
						unset($this->session->data['customer']);
						unset($this->session->data['payment_address']);
						unset($this->session->data['payment_methods']);
						unset($this->session->data['payment_method']);
						
						unset($this->session->data['shipping_address']);
						unset($this->session->data['shipping_methods']);
						unset($this->session->data['shipping_method']);
						
						//start to load method
						$this->load->model('checkout/quotation');
			
						if (isset($this->request->get['quotation_id'])) {
							$quotation_id = $this->request->get['quotation_id'];
						} else {
							$quotation_id = 0;
						}
			
						$quotation_info = $this->model_checkout_quotation->getQuotation($quotation_id);
						if ($quotation_info) 
						{	
								//set customer detail
								$this->session->data['customer'] = array(
									'customer_id'       => $quotation_info['customer_id'],
									'customer_group_id' => $quotation_info['customer_group_id'],
									'firstname'         => $quotation_info['firstname'],
									'lastname'          => $quotation_info['lastname'],
									'email'             => $quotation_info['email'],
									'telephone'         => $quotation_info['telephone'],
									'custom_field'      => isset($quotation_info['custom_field']) ? $quotation_info['custom_field'] : array()
								);
								//set customer detail
								
								//cart item add
									$this->cart->clear();
									if(isset($this->request->post['order_products']))
									{
										foreach ($this->request->post['order_products'] as $product) {
											
											if (isset($product['option'])) {
												$option = $product['option'];
											} else {
												$option = array();
											}
												//custom fields passed
												$b2b_product_price=0;$b2b_product_name='';$b2b_product_tax_class_id=0;$b2b_product_sort_order='';
												if(array_key_exists('price',$product)){ $b2b_product_price=$product['price'].'_'.$product['price_discount_percentage']; }
												if(array_key_exists('name',$product)){ $b2b_product_name=$product['name']; }
												if(array_key_exists('tax_class_id',$product)){ $b2b_product_tax_class_id=$product['tax_class_id']; }
												if(array_key_exists('sort_order',$product)){ $b2b_product_sort_order=$product['sort_order']; }						
												//custom fields passed
												
												$this->cart->B2bCartAdd($product['product_id'], $product['quantity'], $option,'',$b2b_product_price,$b2b_product_name,$b2b_product_tax_class_id,$b2b_product_sort_order);
										}
									}
								//cart item add
								
								
								//shipping address
									$this->load->model('localisation/country');
									$country_info = $this->model_localisation_country->getCountry($quotation_info['shipping_country_id']);
				
									if ($country_info) {
										$country = $country_info['name'];
										$iso_code_2 = $country_info['iso_code_2'];
										$iso_code_3 = $country_info['iso_code_3'];
										$address_format = $country_info['address_format'];
									} else {
										$country = '';
										$iso_code_2 = '';
										$iso_code_3 = '';
										$address_format = '';
									}
				
									$this->load->model('localisation/zone');
									$zone_info = $this->model_localisation_zone->getZone($quotation_info['shipping_zone_id']);
				
									if ($zone_info) {
										$zone = $zone_info['name'];
										$zone_code = $zone_info['code'];
									} else {
										$zone = '';
										$zone_code = '';
									}
				
									$this->session->data['shipping_address'] = array(
										'firstname'      => $quotation_info['shipping_firstname'],
										'lastname'       => $quotation_info['shipping_lastname'],
										'company'        => $quotation_info['shipping_company'],
										'address_1'      => $quotation_info['shipping_address_1'],
										'address_2'      => $quotation_info['shipping_address_2'],
										'postcode'       => $quotation_info['shipping_postcode'],
										'city'           => $quotation_info['shipping_city'],
										'zone_id'        => $quotation_info['shipping_zone_id'],
										'zone'           => $zone,
										'zone_code'      => $zone_code,
										'country_id'     => $quotation_info['shipping_country_id'],
										'country'        => $country,
										'iso_code_2'     => $iso_code_2,
										'iso_code_3'     => $iso_code_3,
										'address_format' => $address_format,
										'custom_field'   => isset($quotation_info['shipping_custom_field']) ? $quotation_info['shipping_custom_field'] : array()
									);
									
									$json['shipping_methods'] = array();
									
									$this->load->model('setting/extension');
									$results = $this->model_setting_extension->getExtensions('shipping');
					
									foreach ($results as $result) {
										if ($this->config->get('shipping_' . $result['code'] . '_status')) {
											$this->load->model('extension/shipping/' . $result['code']);
					
											$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);
											$quote['quote'][$result['code']]['cost_v']=$this->currency->format($quote['quote'][$result['code']]['cost'], $this->session->data['currency']);
											if ($quote) {
												$json['shipping_methods'][$result['code']] = array(
													'title'      => $quote['title'],
													'quote'      => $quote['quote'],
													'sort_order' => $quote['sort_order'],
													'error'      => $quote['error']
												);
											}
										}
									}
					
									$sort_order = array();
					
									foreach ($json['shipping_methods'] as $key => $value) {
										$sort_order[$key] = $value['sort_order'];
									}
					
									array_multisort($sort_order, SORT_ASC, $json['shipping_methods']);
									$this->session->data['shipping_methods'] = $json['shipping_methods'];
									
									$shipping=array();
									if($quotation_info['shipping_code']!='')
									{
										$shipping=explode('.',$quotation_info['shipping_code']);
										
									}
									$this->session->data['shipping_method'] =$json['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];	
									
									
									
									
									
								//set payment address
								$this->load->model('localisation/country');

								$country_info = $this->model_localisation_country->getCountry($quotation_info['payment_country_id']);
				
								if ($country_info) {
									$country = $country_info['name'];
									$iso_code_2 = $country_info['iso_code_2'];
									$iso_code_3 = $country_info['iso_code_3'];
									$address_format = $country_info['address_format'];
								} else {
									$country = '';
									$iso_code_2 = '';
									$iso_code_3 = '';
									$address_format = '';
								}
				
								$this->load->model('localisation/zone');
				
								$zone_info = $this->model_localisation_zone->getZone($quotation_info['payment_zone_id']);
				
								if ($zone_info) {
									$zone = $zone_info['name'];
									$zone_code = $zone_info['code'];
								} else {
									$zone = '';
									$zone_code = '';
								}
				
								$this->session->data['payment_address'] = array(
									'firstname'      => $quotation_info['payment_firstname'],
									'lastname'       => $quotation_info['payment_lastname'],
									'company'       => $quotation_info['payment_company'],
									'address_1'      => $quotation_info['payment_address_1'],
									'address_2'      => $quotation_info['payment_address_2'],
									'postcode'       => $quotation_info['payment_postcode'],
									'city'           => $quotation_info['payment_city'],
									'zone_id'        => $quotation_info['payment_zone_id'],
									'zone'           => $zone,
									'zone_code'      => $zone_code,
									'country_id'     => $quotation_info['payment_country_id'],
									'country'        => $country,
									'iso_code_2'     => $iso_code_2,
									'iso_code_3'     => $iso_code_3,
									'address_format' => $address_format,
									'custom_field'   => isset($quotation_info['payment_custom_field']) ? $quotation_info['payment_custom_field'] : array()
								);
								//set payment address
								
								//set payment method
								// Totals
									$totals = array();
									$taxes = $this->cart->getTaxes();
									$total = 0;
					
									// Because __call can not keep var references so we put them into an array. 
									$total_data = array(
										'totals' => &$totals,
										'taxes'  => &$taxes,
										'total'  => &$total
									);
					
									$this->load->model('setting/extension');
					
									$sort_order = array();
					
									$results = $this->model_setting_extension->getExtensions('total');
					
									foreach ($results as $key => $value) {
										$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
									}
					
									array_multisort($sort_order, SORT_ASC, $results);
					
									foreach ($results as $result) {
										if ($this->config->get('total_' . $result['code'] . '_status')) {
											$this->load->model('extension/total/' . $result['code']);
											
											// We have to put the totals in an array so that they pass by reference.
											$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
										}
									}
					
									// Payment Methods
									$json['payment_methods'] = array();
					
									$this->load->model('setting/extension');
					
									$results = $this->model_setting_extension->getExtensions('payment');
					
									$recurring = $this->cart->hasRecurringProducts();
					
									foreach ($results as $result) {
										if ($this->config->get('payment_' . $result['code'] . '_status')) {
											$this->load->model('extension/payment/' . $result['code']);
					
											$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['payment_address'], $total);
					
											if ($method) {
												if ($recurring) {
													if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
														$json['payment_methods'][$result['code']] = $method;
													}
												} else {
													$json['payment_methods'][$result['code']] = $method;
												}
											}
										}
									}
					
									$sort_order = array();
					
									foreach ($json['payment_methods'] as $key => $value) {
										$sort_order[$key] = $value['sort_order'];
									}
					
								array_multisort($sort_order, SORT_ASC, $json['payment_methods']);
								$this->session->data['payment_methods'] = $json['payment_methods'];
								$this->session->data['payment_method'] =$json['payment_methods'][$quotation_info['payment_code']];	
								//set payment method
						}
						//start to load method
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	//Quotation shipping & payment methods fixing 
}