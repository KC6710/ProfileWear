<?php
namespace Opencart\Catalog\Model\Checkout;
class Cart extends \Opencart\System\Engine\Model {
	public function getProducts(): array {
		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		// Products
		$product_data = [];

		$products = $this->cart->getCartProducts();

		foreach ($products as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			}

			$option_data = [];

			foreach ($product['option'] as $option) {
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

				$option_data[] = [
					'name'  => $option['name'],
					'value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value),
					'type'  => $option['type']
				];
			}

			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$minimum = false;
			} else {
				$minimum = true;
			}

			$option_data = [];
			foreach ($product['option'] as $option) {
				$option_data[] = [
					'cart_id'                 => $product['cart_id'],
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type'],
					'quantity'                => $option['quantity']
				];
			}

			$product_data[] = [
				'cart_id'      => $product['cart_id'],
				'product_id'   => $product['product_id'],
				'master_id'    => $product['master_id'],
				'image'        => $image,
				'name'         => $product['name'],
				'model'        => $product['model'],
				'option'       => $option_data,
				'subscription' => $product['subscription'],
				'download'     => $product['download'],
				'quantity'     => $product['quantity'],
				'stock'        => $product['stock'],
				'minimum'      => $minimum,
				'shipping'     => $product['shipping'],
				'subtract'     => $product['subtract'],
				'reward'       => $product['reward'],
				'tax_class_id' => $product['tax_class_id'],
				'price'        => $product['price'],
				'total'        => $product['price'] * $product['quantity'],
				'group_products' => $product['group_products']
			];
		}

		return $product_data;
	}

	public function getProduct(int $product_id): array {
		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		// Products
		$product_data = [];

		$products = $this->getCartProduct($product_id);

		foreach ($products as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			}

			$option_data = [];

			foreach ($product['option'] as $option) {
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

				$option_data[] = [
					'name'  => $option['name'],
					'value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value),
					'type'  => $option['type']
				];
			}

			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$minimum = false;
			} else {
				$minimum = true;
			}

			$option_data = [];

			foreach ($product['option'] as $option) {
				$option_data[] = [
					'cart_id'				  => $product['cart_id'],
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type'],
					'quantity' 				  => $option['quantity']
				];
			}

			$product_data[] = [
				'cart_id'      => $product['cart_id'],
				'product_id'   => $product['product_id'],
				'master_id'    => $product['master_id'],
				'image'        => $image,
				'name'         => $product['name'],
				'model'        => $product['model'],
				'option'       => $option_data,
				'subscription' => $product['subscription'],
				'download'     => $product['download'],
				'quantity'     => $product['quantity'],
				'stock'        => $product['stock'],
				'minimum'      => $minimum,
				'shipping'     => $product['shipping'],
				'subtract'     => $product['subtract'],
				'reward'       => $product['reward'],
				'tax_class_id' => $product['tax_class_id'],
				'price'        => $product['price'],
				'total'        => $product['price'] * $product['quantity'],
				'group_products' => $product['group_products']
			];
		}

		return $product_data;
	}

	public function getVouchers(): array {
		$voucher_data = [];

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$voucher_data[] = [
					'code'             => $voucher['code'],
					'description'      => $voucher['description'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']
				];
			}
		}

		return $voucher_data;
	}

	public function getTotals(array &$totals, array &$taxes, int &$total): void {
		$sort_order = [];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensionsByType('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/' . $result['extension'] . '/total/' . $result['code']);
				 
				// __call magic method cannot pass-by-reference so we get PHP to call it as an anonymous function.
				($this->{'model_extension_' . $result['extension'] . '_total_' . $result['code']}->getTotal)($totals, $taxes, $total);
			}
		}

		$sort_order = [];

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);
		// echo "<pre>"; print_r($totals); die;
	}

	public function getCartProduct(int $product_id): array{
			$cart_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cart` WHERE `product_id` = '".$product_id."' AND `api_id` = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' AND `session_id` = '" . $this->db->escape($this->session->getId()) . "'");

			foreach ($cart_query->rows as $cart) {

				if($cart['group_products']) {
					$group_product_ids = json_decode($cart['group_products']);
				} else {
					$group_product_ids = [];
				}

				$stock = true;

				$product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_store` `p2s` LEFT JOIN `" . DB_PREFIX . "product` p ON (p2s.`product_id` = p.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) WHERE p2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND p2s.`product_id` = '" . (int)$cart['product_id'] . "' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND p.`date_available` <= NOW() AND p.`status` = '1'");

				if ($product_query->num_rows && ($cart['quantity'] > 0)) {
					$option_price = 0;
					$option_points = 0;
					$option_weight = 0;

					$option_data = [];

					$product_options = (array)json_decode($cart['option'], true);

					// Merge variant code with options
					$variant = json_decode($product_query->row['variant'], true);

					if ($variant) {
						foreach ($variant as $key => $value) {
							$product_options[$key] = $value;
						}
					}

					foreach ($product_options as $product_option_id => $value) {
						if (!$product_query->row['master_id']) {
							$product_id = $cart['product_id'];
						} else {
							$product_id = $product_query->row['master_id'];
						}

						$option_query = $this->db->query("SELECT po.`product_option_id`, po.`option_id`, od.`name`, o.`type` FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.`option_id` = o.`option_id`) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.`option_id` = od.`option_id`) WHERE po.`product_option_id` = '" . (int)$product_option_id . "' AND po.`product_id` = '" . (int)$product_id . "' AND od.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

						if ($option_query->num_rows) {
							if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio') {
								$option_value_query = $this->db->query("SELECT pov.`option_value_id`, ovd.`name`, pov.`quantity`, pov.`subtract`, pov.`price`, pov.`price_prefix`, pov.`points`, pov.`points_prefix`, pov.`weight`, pov.`weight_prefix` FROM `" . DB_PREFIX . "product_option_value` pov LEFT JOIN `" . DB_PREFIX . "option_value` ov ON (pov.`option_value_id` = ov.`option_value_id`) LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.`option_value_id` = ovd.`option_value_id`) WHERE pov.`product_option_value_id` = '" . (int)$value . "' AND pov.`product_option_id` = '" . (int)$product_option_id . "' AND ovd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}

									if ($option_value_query->row['points_prefix'] == '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] == '-') {
										$option_points -= $option_value_query->row['points'];
									}

									if ($option_value_query->row['weight_prefix'] == '+') {
										$option_weight += $option_value_query->row['weight'];
									} elseif ($option_value_query->row['weight_prefix'] == '-') {
										$option_weight -= $option_value_query->row['weight'];
									}

									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cart['quantity']))) {
										$stock = false;
									}

									$option_data[] = [
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $value,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'value'                   => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $cart['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix']
									];
								}
							} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
								foreach ($value as $product_option_value_id) {
									$option_value_query = $this->db->query("SELECT pov.`option_value_id`, pov.`quantity`, pov.`subtract`, pov.`price`, pov.`price_prefix`, pov.`points`, pov.`points_prefix`, pov.`weight`, pov.`weight_prefix`, ovd.`name` FROM `" . DB_PREFIX . "product_option_value` `pov` LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (pov.`option_value_id` = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

									if ($option_value_query->num_rows) {
										if ($option_value_query->row['price_prefix'] == '+') {
											$option_price += $option_value_query->row['price'];
										} elseif ($option_value_query->row['price_prefix'] == '-') {
											$option_price -= $option_value_query->row['price'];
										}

										if ($option_value_query->row['points_prefix'] == '+') {
											$option_points += $option_value_query->row['points'];
										} elseif ($option_value_query->row['points_prefix'] == '-') {
											$option_points -= $option_value_query->row['points'];
										}

										if ($option_value_query->row['weight_prefix'] == '+') {
											$option_weight += $option_value_query->row['weight'];
										} elseif ($option_value_query->row['weight_prefix'] == '-') {
											$option_weight -= $option_value_query->row['weight'];
										}

										if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cart['quantity']))) {
											$stock = false;
										}

										$option_data[] = [
											'product_option_id'       => $product_option_id,
											'product_option_value_id' => $product_option_value_id,
											'option_id'               => $option_query->row['option_id'],
											'option_value_id'         => $option_value_query->row['option_value_id'],
											'name'                    => $option_query->row['name'],
											'value'                   => $option_value_query->row['name'],
											'type'                    => $option_query->row['type'],
											'quantity'                => $option_value_query->row['quantity'],
											'subtract'                => $option_value_query->row['subtract'],
											'price'                   => $option_value_query->row['price'],
											'price_prefix'            => $option_value_query->row['price_prefix'],
											'points'                  => $option_value_query->row['points'],
											'points_prefix'           => $option_value_query->row['points_prefix'],
											'weight'                  => $option_value_query->row['weight'],
											'weight_prefix'           => $option_value_query->row['weight_prefix']
										];
									}
								}
							} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
								$option_data[] = [
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => '',
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => '',
									'name'                    => $option_query->row['name'],
									'value'                   => $value,
									'type'                    => $option_query->row['type'],
									'quantity'                => '',
									'subtract'                => '',
									'price'                   => '',
									'price_prefix'            => '',
									'points'                  => '',
									'points_prefix'           => '',
									'weight'                  => '',
									'weight_prefix'           => ''
								];
							}
						}
					}

					$price = $product_query->row['price'];

					// Product Discounts
					$discount_quantity = 0;

					foreach ($cart_query->rows as $cart_2) {
						if ($cart_2['product_id'] == $cart['product_id']) {
							$discount_quantity += $cart_2['quantity'];
						}
					}

					$product_discount_query = $this->db->query("SELECT `price` FROM `" . DB_PREFIX . "product_discount` WHERE `product_id` = '" . (int)$cart['product_id'] . "' AND `customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "' AND `quantity` <= '" . (int)$discount_quantity . "' AND ((`date_start` = '0000-00-00' OR `date_start` < NOW()) AND (`date_end` = '0000-00-00' OR `date_end` > NOW())) ORDER BY `quantity` DESC, `priority` ASC, `price` ASC LIMIT 1");

					if ($product_discount_query->num_rows) {
						$price = $product_discount_query->row['price'];
					}

					// Product Specials
					$product_special_query = $this->db->query("SELECT `price` FROM `" . DB_PREFIX . "product_special` WHERE `product_id` = '" . (int)$cart['product_id'] . "' AND `customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((`date_start` = '0000-00-00' OR `date_start` < NOW()) AND (`date_end` = '0000-00-00' OR `date_end` > NOW())) ORDER BY `priority` ASC, `price` ASC LIMIT 1");

					if ($product_special_query->num_rows) {
						$price = $product_special_query->row['price'];
					}

					$product_total = 0;

					foreach ($cart_query->rows as $cart_2) {
						if ($cart_2['product_id'] == $cart['product_id']) {
							$product_total += $cart_2['quantity'];
						}
					}

					if ($product_query->row['minimum'] > $product_total) {
						$minimum = false;
					} else {
						$minimum = true;
					}

					// Reward Points
					$product_reward_query = $this->db->query("SELECT `points` FROM `" . DB_PREFIX . "product_reward` WHERE `product_id` = '" . (int)$cart['product_id'] . "' AND `customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "'");

					if ($product_reward_query->num_rows) {
						$reward = $product_reward_query->row['points'];
					} else {
						$reward = 0;
					}

					// Downloads
					$download_data = [];

					$download_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_download` p2d LEFT JOIN `" . DB_PREFIX . "download` d ON (p2d.`download_id` = d.`download_id`) LEFT JOIN `" . DB_PREFIX . "download_description` dd ON (d.`download_id` = dd.`download_id`) WHERE p2d.`product_id` = '" . (int)$cart['product_id'] . "' AND dd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

					foreach ($download_query->rows as $download) {
						$download_data[] = [
							'download_id' => $download['download_id'],
							'name'        => $download['name'],
							'filename'    => $download['filename'],
							'mask'        => $download['mask']
						];
					}

					// Stock
					if (!$product_query->row['quantity'] || ($product_query->row['quantity'] < $cart['quantity'])) {
						$stock = false;
					}

					$subscription_data = [];

					$subscription_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_subscription` ps LEFT JOIN `" . DB_PREFIX . "subscription_plan` sp ON (ps.`subscription_plan_id` = sp.`subscription_plan_id`) LEFT JOIN `" . DB_PREFIX . "subscription_plan_description` spd ON (sp.`subscription_plan_id` = spd.`subscription_plan_id`) WHERE ps.`product_id` = '" . (int)$cart['product_id'] . "' AND ps.`subscription_plan_id` = '" . (int)$cart['subscription_plan_id'] . "' AND ps.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "' AND spd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND sp.`status` = '1'");

					if ($subscription_query->num_rows) {
						$price = $subscription_query->row['price'];

						if ($subscription_query->row['trial_status']) {
							$price = $subscription_query->row['trial_price'];
						}

                        $subscription_data = [
                            'subscription_plan_id' => $subscription_query->row['subscription_plan_id'],
                            'name'                 => $subscription_query->row['name'],
                            'trial_price'          => $subscription_query->row['trial_price'],
                            'trial_frequency'      => $subscription_query->row['trial_frequency'],
                            'trial_cycle'          => $subscription_query->row['trial_cycle'],
                            'trial_duration'       => $subscription_query->row['trial_duration'],
                            'trial_remaining'      => $subscription_query->row['trial_duration'],
                            'trial_status'         => $subscription_query->row['trial_status'],
                            'price'                => $subscription_query->row['price'],
                            'frequency'            => $subscription_query->row['frequency'],
                            'cycle'                => $subscription_query->row['cycle'],
                            'duration'             => $subscription_query->row['duration'],
                            'remaining'            => $subscription_query->row['duration']
                        ];
					}

					$product_data[] = [
						'cart_id'         => $cart['cart_id'],
						'product_id'      => $product_query->row['product_id'],
						'master_id'       => $product_query->row['master_id'],
						'name'            => $product_query->row['name'],
						'model'           => $product_query->row['model'],
						'shipping'        => $product_query->row['shipping'],
						'image'           => $product_query->row['image'],
						'option'          => $option_data,
						'subscription'    => $subscription_data,
						'download'        => $download_data,
						'quantity'        => $cart['quantity'],
						'minimum'         => $minimum,
						'subtract'        => $product_query->row['subtract'],
						'stock'           => $stock,
						'price'           => ($price + $option_price),
						'total'           => ($price + $option_price) * $cart['quantity'],
						'reward'          => $reward * $cart['quantity'],
						'points'          => ($product_query->row['points'] ? ($product_query->row['points'] + $option_points) * $cart['quantity'] : 0),
						'tax_class_id'    => $product_query->row['tax_class_id'],
						'weight'          => ($product_query->row['weight'] + $option_weight) * $cart['quantity'],
						'weight_class_id' => $product_query->row['weight_class_id'],
						'length'          => $product_query->row['length'],
						'width'           => $product_query->row['width'],
						'height'          => $product_query->row['height'],
						'length_class_id' => $product_query->row['length_class_id'],
						'group_products'  => $group_product_ids
					];
				} else {
				}
			}
		return $product_data;
	}

	public function getB2bProducts(): array {
		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		// Products
		$product_data = [];

		$products = $this->cart->getCartProducts();

		foreach ($products as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			}

			$option_data = [];

			foreach ($product['option'] as $option) {
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

				$option_data[] = [
					'name'  => $option['name'],
					'value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value),
					'type'  => $option['type']
				];
			}

			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$minimum = false;
			} else {
				$minimum = true;
			}

			$option_data = [];
			foreach ($product['option'] as $option) {
				$option_data[] = [
					'cart_id'                 => $product['cart_id'],
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type'],
					'quantity'                => $option['quantity']
				];
			}

			$product_data[] = [
				'cart_id'      => $product['cart_id'],
				'product_id'   => $product['product_id'],
				'master_id'    => $product['master_id'],
				'image'        => $image,
				'name'         => $product['name'],
				'model'        => $product['model'],
				'option'       => $option_data,
				'subscription' => $product['subscription'],
				'download'     => $product['download'],
				'quantity'     => $product['quantity'],
				'stock'        => $product['stock'],
				'minimum'      => $minimum,
				'shipping'     => $product['shipping'],
				'subtract'     => $product['subtract'],
				'reward'       => $product['reward'],
				'tax_class_id' => $product['tax_class_id'],
				'price'        => $product['price'],
				'total'        => $product['price'] * $product['quantity'],
				'group_products' => $product['group_products']
			];
		}

		return $product_data;
	}

	public function getnotSure() {
		$products = [];
			$cart_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cart` WHERE `api_id` = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' AND `session_id` = '" . $this->db->escape($this->session->getId()) . "'");
			foreach ($cart_query->rows as $cart) {
				if($cart['not_sure'] == 1){

				}
			}
	}
}
