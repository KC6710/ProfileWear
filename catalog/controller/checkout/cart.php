<?php
namespace Opencart\Catalog\Controller\Checkout;
class Cart extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('checkout/cart');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
		];

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$data['list'] = $this->load->controller('checkout/cart.getList');

			$data['modules'] = [];

			$this->load->model('setting/extension');

			$extensions = $this->model_setting_extension->getExtensionsByType('total');

			foreach ($extensions as $extension) {
				 $result = $this->load->controller('extension/' . $extension['extension'] . '/total/' . $extension['code']);

				if (!$result instanceof \Exception) {
					$data['modules'][] = $result;
				}
			}
			# PW-Front > Count totals of cart
        	# PW-Front > Count total items
			if($this->cart->hasProducts()) {
				$group_product = array('00-prt-d-s','00-prt-d-25','00-prt-d-100','00-prt-d-200','00-prt-d-300','00-prt-scr-s','00-prt-scr-1','00-prt-scr-2','00-prt-scr-3','00-prt-scr-4');
				$cartProducts = $this->cart->getCartProducts();
				$data['count_cart_items'] = 0;
				foreach($cartProducts as $product){
					if(!in_array($product['model'],$group_product)){
						$data['count_cart_items'] = $data['count_cart_items'] + (int)$product['quantity'];
					}
				}
			} else {
				$data['count_cart_items'] = 0;
			}
            # PW-Front > Count total items

			$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));
			$data['checkout'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'));
			$data['quotation'] = $this->url->link('checkout/quotation', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$this->response->setOutput($this->load->view('checkout/cart', $data));
		} 
		else {
			$data['text_error'] = $this->language->get('text_no_results');

			$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/no_product', $data));
		}
	}

	public function list(): void {
		$this->load->language('checkout/cart');

		$this->response->setOutput($this->getList());
	}

	public function getList(): string {
		$data['list'] = $this->url->link(' ', 'language=' . $this->config->get('config_language'));
		$data['product_edit'] = $this->url->link('checkout/cart.edit', 'language=' . $this->config->get('config_language'));
		$data['product_remove'] = $this->url->link('checkout/cart.remove', 'language=' . $this->config->get('config_language'));
		$data['voucher_remove'] = $this->url->link('checkout/voucher.remove', 'language=' . $this->config->get('config_language'));

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$data['products'] = [];

		$this->load->model('checkout/cart');

		$products = $this->model_checkout_cart->getProducts();

		$customized = 0;

		$this->load->model('catalog/product');

		foreach ($products as $product) {
			if (!$product['minimum']) {
				$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$description = '';

			if ($product['subscription']) {
				if ($product['subscription']['trial_status']) {
					$trial_price = $this->currency->format($this->tax->calculate($product['subscription']['trial_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_cycle = $product['subscription']['trial_cycle'];
					$trial_frequency = $this->language->get('text_' . $product['subscription']['trial_frequency']);
					$trial_duration = $product['subscription']['trial_duration'];

					$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['subscription']['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				}

				$cycle = $product['subscription']['cycle'];
				$frequency = $this->language->get('text_' . $product['subscription']['frequency']);
				$duration = $product['subscription']['duration'];

				if ($duration) {
					$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
				} else {
					$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
				}
			}

			foreach($product['option'] as $product_option){
				if($product_option['name'] == 'Färg'){
					$option_array['product_option_value_id'] =  $product_option['product_option_value_id'];
				}
			}
			$options = $this->model_catalog_product->getOptions($product['product_id']);
			foreach($options as $option){
				if($option['name'] == 'Färg'){
					foreach($option['product_option_value'] as $product_option_value){
						if($product_option_value['product_option_value_id'] == $option_array['product_option_value_id']){
							$image = $product_option_value['image'];
							if (is_file(DIR_IMAGE . html_entity_decode($product_option_value['image'], ENT_QUOTES, 'UTF-8'))) {
								$image = $this->model_tool_image->resize(html_entity_decode($product_option_value['image'], ENT_QUOTES, 'UTF-8'), 47,47);
							}
						}
					}
				}
			}
			if(count($product['group_products']) > 0 ) {
				$customized = 1;
			}
			$data['products'][] = [
				'product_id'      => $product['product_id'],
				'cart_id'      => $product['cart_id'],
				'thumb'        => $image,
				'name'         => $product['name'],
				'model'        => $product['model'],
				'option'       => $product['option'],
				'subscription' => $description,
				'quantity'     => $product['quantity'],
				'stock'        => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
				'minimum'      => $product['minimum'],
				'reward'       => $product['reward'],
				'price'        => $price,
				'total'        => $total,
				'href'         => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id']),
				'group_products'=> $product['group_products']
			];
		}

		
		$data['customized']  = $customized;
		$products = $data['products'];
		// Create an associative array with product_id as key and product array as value
		$productMap = [];
		foreach ($products as $product) {
			$productMap[$product['cart_id']] = $product;
		}
		$productsToRemove = [];

		
		foreach ($products as $key => &$product) {
			if (isset($product['group_products']) && is_array($product['group_products'])) {
				$groupProductsArray = [];
				foreach ($product['group_products'] as $groupProductId) {
					$groupProductId = (int)$groupProductId;
					if (isset($productMap[$groupProductId])) {
						$groupProductsArray[] = $productMap[$groupProductId];
						// Add the product ID to the removal list
						$productsToRemove[] = $groupProductId;
					}
				}
				
				// Sort the group products by 'name'
				usort($groupProductsArray, function($a, $b) {
					return strcmp($a['name'], $b['name']);
				});

				$product['group_products'] = $groupProductsArray;
			}
		}
		unset($product); // Unset reference to avoid unexpected behavior
		$screen_color = 0;
		// Remove the products from the main products array
		foreach ($productsToRemove as $productIdToRemove) {
			foreach ($products as $key => $product) {
				if(strpos($product['name'], '1-Color') !== false && $screen_color < 1  ) {
					$screen_color = 1;
				} else if(strpos($product['name'], '2-Color') !== false && $screen_color < 2) {
					$screen_color = 2;
				} else if(strpos($product['name'], '3-Color') !== false && $screen_color < 3) {
					$screen_color = 3;
				} else if(strpos($product['name'], '4-Color') !== false && $screen_color < 4) {
					$screen_color = 4;
				}
				if ($product['cart_id'] == $productIdToRemove) {
					unset($products[$key]);
					break;
				}
			}
		}
		$data['screen_color'] = $screen_color;
		// Re-index the array to fix any gaps in the keys
		$products = array_values($products);
		$data['products'] = $products;
		// Gift Voucher
		$data['vouchers'] = [];

		$vouchers = $this->model_checkout_cart->getVouchers();

		foreach ($vouchers as $key => $voucher) {
			$data['vouchers'][] = [
				'key'         => $key,
				'description' => $voucher['description'],
				'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
			];
		}

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
		foreach($data['totals'] as $total){
			if($total['title'] == 'Totalt'){
				$data['carttotalvalue'] =  $total['text'];
			 }
		}
		$data['carttotalvalue'] = str_replace(',','',$data['carttotalvalue']);
		$data['carttotalvalue'] = str_replace('kr','',$data['carttotalvalue']);
		$data['notSurePrint'] = $this->getnotsurevalue();
		$data['quotation'] = $this->url->link('checkout/quotation', 'language=' . $this->config->get('config_language'));
		$data['checkout'] = $this->url->link('extension/svea/module/svea/checkout', 'language=' . $this->config->get('config_language'));
		return $this->load->view('checkout/cart_list', $data);
	}

	public function array_multidimensional_unique($input){
		$output = array_map("unserialize",
		array_unique(array_map("serialize", $input)));
	  return $output;
   }
    public function productsCount(){
		if($this->cart->hasProducts()) {
			$group_product = array('00-prt-d-s','00-prt-d-25','00-prt-d-100','00-prt-d-200','00-prt-d-300','00-prt-scr-s','00-prt-scr-1','00-prt-scr-2','00-prt-scr-3','00-prt-scr-4');
			$cartProducts = $this->cart->getCartProducts();
			$json['productsCount'] = 0;
			foreach($cartProducts as $product){
				if(!in_array($product['model'],$group_product)){
					$json['productsCount'] = $json['productsCount'] + (int)$product['quantity'];
				}
			}
		} else {
			$json['productsCount'] = 0;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function add(): void {
		$this->load->language('checkout/cart');
		$json = [];

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['not_sure'])) {
			$notSurePrint = (int)$this->request->post['not_sure'];
		} else {
			$notSurePrint = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = (int)$this->request->post['quantity'];
		} else {
			$quantity = 0;
		}

		if (isset($this->request->post['option'])) {
			$option = array_filter($this->request->post['option']);
		} else {
			$option = [];
		}

		if (isset($this->request->post['subscription_plan_id'])) {
			$subscription_plan_id = (int)$this->request->post['subscription_plan_id'];
		} else {
			$subscription_plan_id = 0;
		}

		if (isset($this->request->post['product_group_id'])) {
			$product_group_id = $this->request->post['product_group_id'];
		} else {
			$product_group_id = null;
		}

		if (isset($this->request->post['groupoption'])) {
			$group_options = $this->request->post['groupoption'];
		} else {
			$group_options = [];
		}

		if (isset($this->request->post['startup'])) {
			$startup_product = $this->request->post['startup'];
		} else {
			$startup_product = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);
		$product_options = $this->model_catalog_product->getOptions($product_id);

		if ($product_info) {
			// If variant get master product
			if ($product_info['master_id']) {
				$product_id = $product_info['master_id'];
			}

			// Only use values in the override
			if (isset($product_info['override']['variant'])) {
				$override = $product_info['override']['variant'];
			} else {
				$override = [];
			}

			// Merge variant code with options
			foreach ($product_info['variant'] as $key => $value) {
				if (in_array($key, $override)) {
					$option[$key] = $value;
				}
			}

			// Validate options
			$product_options = $this->model_catalog_product->getOptions($product_id);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option_' . $product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}

			// Validate Quantity
			if($quantity < 1) {
				$json['error']['quantity'] = "Kvantitet kan inte vara 0";
			}

			// Validate subscription products
			$subscriptions = $this->model_catalog_product->getSubscriptions($product_id);

			if ($subscriptions) {
				$subscription_plan_ids = [];

				foreach ($subscriptions as $subscription) {
					$subscription_plan_ids[] = $subscription['subscription_plan_id'];
				}

				if (!in_array($subscription_plan_id, $subscription_plan_ids)) {
					$json['error']['subscription'] = $this->language->get('error_subscription');
				}
			}
		} else {
			$json['error']['warning'] = $this->language->get('error_product');
		}

		if($product_group_id) {
			// For Grouped product
			$product_info = $this->model_catalog_product->getProduct($product_group_id);

			if ($product_info) {
				// Only use values in the override
				if (isset($product_info['override']['variant'])) {
					$override = $product_info['override']['variant'];
				} else {
					$override = [];
				}

				// Merge variant code with group_options
				foreach ($product_info['variant'] as $key => $value) {
					if (in_array($key, $override)) {
						$group_options[$key] = $value;
					}
				}

				// Validate group_options
				$product_options = $this->model_catalog_product->getOptions($product_id);

				foreach ($product_options as $product_option) {
					if ($product_option['required'] && empty($group_options[$product_option['product_option_id']])) {
						$json['error']['option_' . $product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
					}
				}
			}
		}

		if ((array_key_exists('group_products',$this->request->post))) {
			$group_products_flag = $this->request->post['group_products'];
		} else {
			$group_products_flag = 0;
		}
		
		if (!$json) {
			$this->cart->add($product_id,$notSurePrint, $quantity, $option, $subscription_plan_id, $product_group_id, $group_options, $startup_product, $group_products_flag);
			// if($product_options){
			// 	foreach($product_options as $product_option){
			// 		if($product_option['name'] == 'Size'){
			// 			foreach($product_option['product_option_value'] as $option_value){
			// 				if($this->request->post['quantity_value_'.$option_value['product_option_value_id']] > 0){
			// 					$option[$product_option['product_option_id']] = $option_value['product_option_value_id'];
			// 					$this->cart->add($product_id, $this->request->post['quantity_value_'.$option_value['product_option_value_id']], $option, $subscription_plan_id, $product_group_id, $group_options, $startup_product, $group_products_flag);
			// 				}
			// 			}
			// 		}
			// 	}
			// }else{
			// 	$this->cart->add($product_id, $quantity, $option, $subscription_plan_id, $product_group_id, $group_options, $startup_product, $group_products_flag);
			// }


			$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id), $product_info['name'], $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language')));

			// Unset all shipping and payment methods
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		} else {
			$json['redirect'] = $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id, true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function edit(): void {
		$this->load->language('checkout/cart');

		$json = [];
		if (isset($this->request->post['key'])) {
			$key = (int)$this->request->post['key'];
		} else {
			$key = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = (int)$this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		if (isset($this->request->post['option_id'])) {
			$option_id = (int)$this->request->post['option_id'];
		}

		if (isset($this->request->post['option_value_id'])) {
			$option_value_id = (int)$this->request->post['option_value_id'];
		}

		// Handles single item update
		$this->cart->update($key, $quantity, $option_id, $option_value_id);

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			$json['success'] = $this->language->get('text_edit');
		} else {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['reward']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function remove(): void {
		$this->load->language('checkout/cart');

		$json = [];

		if (isset($this->request->post['key'])) {
			$keys = $this->request->post['key'];
		} else {
			$keys = [];
		}

		$keys = array_unique($keys,true);

		// Remove
		foreach($keys as $key){
			$this->cart->remove($key);
		}

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			$json['success'] = $this->language->get('text_remove');
		} else {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['reward']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function B2bCart(){
		$this->load->language('checkout/cart');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/B2bCart', 'language=' . $this->config->get('config_language'))
		];

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$data['list'] = $this->load->controller('checkout/cart.getB2bList');

			$data['modules'] = [];

			$this->load->model('setting/extension');

			$extensions = $this->model_setting_extension->getExtensionsByType('total');

			foreach ($extensions as $extension) {
				 $result = $this->load->controller('extension/' . $extension['extension'] . '/total/' . $extension['code']);

				if (!$result instanceof \Exception) {
					$data['modules'][] = $result;
				}
			}

			$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));
			$data['checkout'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'));
			$data['quotation'] = $this->url->link('checkout/quotation', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('checkout/B2bCart', $data));
		} else {
			$data['text_error'] = $this->language->get('text_no_results');

			$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	public function getB2bList(){
		$data['list'] = $this->url->link(' ', 'language=' . $this->config->get('config_language'));
		$data['product_edit'] = $this->url->link('checkout/cart.edit', 'language=' . $this->config->get('config_language'));
		$data['product_remove'] = $this->url->link('checkout/cart.remove', 'language=' . $this->config->get('config_language'));
		$data['voucher_remove'] = $this->url->link('checkout/voucher.remove', 'language=' . $this->config->get('config_language'));

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$data['products'] = [];

		$this->load->model('checkout/cart');

		$products = $this->model_checkout_cart->getB2bProducts();
		
		$customized = 0;
		foreach ($products as $product) {
			if (!$product['minimum']) {
				$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$description = '';

			if ($product['subscription']) {
				if ($product['subscription']['trial_status']) {
					$trial_price = $this->currency->format($this->tax->calculate($product['subscription']['trial_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_cycle = $product['subscription']['trial_cycle'];
					$trial_frequency = $this->language->get('text_' . $product['subscription']['trial_frequency']);
					$trial_duration = $product['subscription']['trial_duration'];

					$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['subscription']['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				}

				$cycle = $product['subscription']['cycle'];
				$frequency = $this->language->get('text_' . $product['subscription']['frequency']);
				$duration = $product['subscription']['duration'];

				if ($duration) {
					$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
				} else {
					$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
				}
			}
			if(count($product['group_products']) > 0 ) {
				$customized = 1;
			}
			$data['products'][] = [
				'product_id'      => $product['product_id'],
				'cart_id'      => $product['cart_id'],
				'thumb'        => $product['image'],
				'name'         => $product['name'],
				'model'        => $product['model'],
				'option'       => $product['option'],
				'subscription' => $description,
				'quantity'     => $product['quantity'],
				'stock'        => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
				'minimum'      => $product['minimum'],
				'reward'       => $product['reward'],
				'price'        => $price,
				'total'        => $total,
				'href'         => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id']),
				'group_products'=> $product['group_products']
			];
		}

		
		$data['customized']  = $customized;
		$products = $data['products'];
		// Create an associative array with product_id as key and product array as value
		$productMap = [];
		foreach ($products as $product) {
			$productMap[$product['cart_id']] = $product;
		}
		$productsToRemove = [];

		
		foreach ($products as $key => &$product) {
			if (isset($product['group_products']) && is_array($product['group_products'])) {
				$groupProductsArray = [];
				foreach ($product['group_products'] as $groupProductId) {
					$groupProductId = (int)$groupProductId;
					if (isset($productMap[$groupProductId])) {
						$groupProductsArray[] = $productMap[$groupProductId];
						// Add the product ID to the removal list
						$productsToRemove[] = $groupProductId;
					}
				}
				
				// Sort the group products by 'name'
				usort($groupProductsArray, function($a, $b) {
					return strcmp($a['name'], $b['name']);
				});

				$product['group_products'] = $groupProductsArray;
			}
		}
		unset($product); // Unset reference to avoid unexpected behavior
		$screen_color = 0;
		// Remove the products from the main products array
		foreach ($productsToRemove as $productIdToRemove) {
			foreach ($products as $key => $product) {
				if(strpos($product['name'], '1-Color') !== false && $screen_color < 1  ) {
					$screen_color = 1;
				} else if(strpos($product['name'], '2-Color') !== false && $screen_color < 2) {
					$screen_color = 2;
				} else if(strpos($product['name'], '3-Color') !== false && $screen_color < 3) {
					$screen_color = 3;
				} else if(strpos($product['name'], '4-Color') !== false && $screen_color < 4) {
					$screen_color = 4;
				}
				if ($product['cart_id'] == $productIdToRemove) {
					unset($products[$key]);
					break;
				}
			}
		}
		$data['screen_color'] = $screen_color;
		// Re-index the array to fix any gaps in the keys
		$products = array_values($products);
		$data['products'] = $products;
		// Gift Voucher
		$data['vouchers'] = [];

		$vouchers = $this->model_checkout_cart->getVouchers();

		foreach ($vouchers as $key => $voucher) {
			$data['vouchers'][] = [
				'key'         => $key,
				'description' => $voucher['description'],
				'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
			];
		}

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
		$data['quotation'] = $this->url->link('checkout/quotation', 'language=' . $this->config->get('config_language'));
		$data['checkout'] = $this->url->link('extension/svea/module/svea/checkout', 'language=' . $this->config->get('config_language'));
		return $this->load->view('checkout/B2bCart_list', $data);
	}
	public function getnotsurevalue(){
		$cart_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cart` WHERE `api_id` = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' AND `session_id` = '" . $this->db->escape($this->session->getId()) . "'");
		foreach($cart_query->rows as $cart){
			if($cart['not_sure'] == 1){
				return 1;
			}
		}
	}
}
