<?php
namespace Opencart\Admin\Model\Codevoc;
class B2bmanagerOrder extends \Opencart\System\Engine\Model {
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, bo.vatnr,bo.assignee FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "codevoc_b2b_order` bo ON  o.order_id = bo.order_id WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			$reward = 0;

			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}

			$this->load->model('customer/customer');

			$affiliate_info = $this->model_customer_customer->getCustomer($order_query->row['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			return array(
				'order_id'                => $order_query->row['order_id'],
				'assignee'				  => $order_query->row['assignee'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'email'                   => $order_query->row['email'],
				'telephone'               => $order_query->row['telephone'],
				'custom_field'            => json_decode($order_query->row['custom_field'], true),
				'payment_address_id'       => $order_query->row['payment_address_id'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_custom_field'    => json_decode($order_query->row['payment_custom_field'], true),
				'payment_method'          => $order_query->row['payment_method'],
				'payment_code'            => $order_query->row['payment_code'],
				'shipping_address_id'       => $order_query->row['shipping_address_id'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_custom_field'   => json_decode($order_query->row['shipping_custom_field'], true),
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_code'           => $order_query->row['shipping_code'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $reward,
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified'],
				'quotation_id'           => $order_query->row['quotation_id'],
				'order_type'           => $order_query->row['order_type']
			);
		} else {
			return;
		}
	}

	public function getOrders($data = array()) {

        //v7
		$sql = "SELECT DISTINCT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer,o.custom_field, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, o.shipping_code, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, o.quotation_id ,bo.assignee, o.order_type,  o.payment_method FROM `" . DB_PREFIX . "order` o  LEFT JOIN " . DB_PREFIX . "codevoc_b2b_order bo ON o.order_id = bo.order_id";
		//v7
		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}
        //v7
			if (isset($data['filter_search']) && $data['filter_search'] !== '')
			{
				$implode = array();
				$implode[]= " o.order_id= '".$this->db->escape($data['filter_search'])."'";
				$implode[]=" CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" custom_field LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}

		//v7
		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
		}

		if (!empty($data['filter_assignee'])) {
			$sql .= " AND bo.assignee = '" . (float)$data['filter_assignee'] . "'";
		}

		$sort_data = array(
			'o.order_id',
			'customer',
			'order_status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_b2b_order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
		return $query->rows;
	}

	public function changeOrderType($order_id, $order_type){
		if($order_type == 'printed'){
			$order_type = 'Quotation';
		}else{
			$order_type = '';
		}
		if($this->db->query("UPDATE `" . DB_PREFIX . "order` SET `order_type`='" . $this->db->escape($order_type) . "' WHERE `order_id` = " . (int)$order_id)){
			return 1;
		}else{
			return 0;
		}
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getTotalOrders($data = array()) {
		//$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";
		//v7
		$sql = "SELECT COUNT(*) AS total,order_id,firstname,lastname,custom_field FROM `" . DB_PREFIX . "order`";
		//v7

		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE order_status_id > '0'";
		}
        //v7
			if (isset($data['filter_search']) && $data['filter_search'] !== '')
			{
				$implode = array();
				$implode[]= " order_id= '".$this->db->escape($data['filter_search'])."'";
				$implode[]=" CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" custom_field LIKE '%" . $this->db->escape($data['filter_search']) . "%'";

				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}

		//v7
		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND total = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getOrderHistories($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalOrderHistories($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}
	//get codevoc_b2b_order table data
	public function getCodevocB2bOrder($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_b2b_order WHERE order_id = '" . (int)$order_id . "'");
		return $query->row;
	}
	//get codevoc_b2b_order table data

	//get codevoc_b2b_order_product table data
	public function getCodevocB2bOrderProduct($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_b2b_order_product WHERE order_id = '" . (int)$order_id . "'");
		return $query->row;
	}
	//get codevoc_b2b_order_product table data


	//get codevoc_b2b_order_product table order product data
	public function getCodevocB2bOrderProducts($order_id,$order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_b2b_order_product WHERE order_id = '" . (int)$order_id . "' and order_product_id='".$order_product_id."'");
		return $query->row;
	}
	//get codevoc_b2b_order_product table order product data


	//get products autocomplete
	public function getFindproducts($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND (pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%' or p.model LIKE '" . $this->db->escape($data['filter_name']) . "%')";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
            $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
        }

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
            $sql .= " AND p.quantity = '" . (int) $data['filter_quantity'] . "'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int) $data['filter_status'] . "'";
        }

        if (isset($data['filter_image']) && !is_null($data['filter_image'])) {
            if ($data['filter_image'] == 1) {
                $sql .= " AND (p.image IS NOT NULL AND p.image <> '' AND p.image <> 'no_image.png')";
            } else {
                $sql .= " AND (p.image IS NULL OR p.image = '' OR p.image = 'no_image.png')";
            }
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }
	//get products autocomplete

	public function getProductOptions($product_id){
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY po.product_option_id");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query("SELECT * ,ovd.name as option_name FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

            foreach ($product_option_value_query->rows as $product_option_value) {
                if (!$product_option_value['subtract'] || ($product_option_value['quantity'] > 0)) {
                    $product_option_value_data[] = array(
                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                        'option_value_id'         => $product_option_value['option_value_id'],
						'option_value'         	  => $product_option_value['option_name'],
                        'name'                    => $product_option_value['name'],
                        'quantity'                => $product_option_value['quantity'],
                        'subtract'                => $product_option_value['subtract'],
                        'price'                   => $product_option_value['price'],
                        'price_prefix'            => $product_option_value['price_prefix'],
                        'points'                  => $product_option_value['points'],
                        'points_prefix'           => $product_option_value['points_prefix'],
                        'weight'                  => $product_option_value['weight'],
                        'weight_prefix'           => $product_option_value['weight_prefix']
                    );
                }
            }

            $product_option_data[] = array(
                'product_option_id'    => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id'            => $product_option['option_id'],
                'name'                 => $product_option['name'],
                'type'                 => $product_option['type'],
                'value'                => $product_option['value'],
                'required'             => $product_option['required']
            );
        }

        return $product_option_data;
    }
	public function updateAssignee($order_id, $assignee_id) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "codevoc_b2b_order SET assignee = '". (int)$assignee_id ."' WHERE order_id = '". (int)$order_id ."'");
	}
	public function updateOrderStatus($order_id, $status) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '". (int)$status ."' WHERE order_id = '". (int)$order_id ."'");
	}

	public function deleteOrder($order_id){
		$query = $this->db->query("DELETE FROM " . DB_PREFIX . "order WHERE order_id = '". (int)$order_id ."'");
		$query_history = $this->db->query("SELECT COUNT(*) FROM"  . DB_PREFIX . "order_history WHERE order_id = '". (int)$order_id ."'");
		if($query_history->num_rows>0){
			$delete_history = $this->db->query("DELETE FROM " . DB_PREFIX . "order_history WHERE order_id = '". (int)$order_id ."'");
		}
		$query_product = $this->db->query("SELECT COUNT(*) FROM"  . DB_PREFIX . "order_product WHERE order_id = '". (int)$order_id ."'");
		if($query_product->num_rows>0){
			$delete_product = $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '". (int)$order_id ."'");
		}
		$query_option = $this->db->query("SELECT COUNT(*) FROM"  . DB_PREFIX . "order_option WHERE order_id = '". (int)$order_id ."'");
		if($query_option->num_rows>0){
			$delete_option = $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '". (int)$order_id ."'");
		}
		$query_total = $this->db->query("SELECT COUNT(*) FROM"  . DB_PREFIX . "order_total WHERE order_id = '". (int)$order_id ."'");
		if($query_total->num_rows>0){
			$delete_total = $this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '". (int)$order_id ."'");
		}
		$query_voucher = $this->db->query("SELECT COUNT(*) FROM"  . DB_PREFIX . "order_voucher WHERE order_id = '". (int)$order_id ."'");
		if($query_voucher->num_rows>0){
			$delete_voucher = $this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '". (int)$order_id ."'");
		}
	}
	public function getProductOptionsSel($product_id,$orderoption){
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY po.product_option_id");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query("SELECT *,ovd.name as option_name FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

            foreach ($product_option_value_query->rows as $product_option_value) {
                if (!$product_option_value['subtract'] || ($product_option_value['quantity'] > 0)) {
					//db select
					$selected='';
					if(isset($orderoption[$product_option['product_option_id']]) && $orderoption[$product_option['product_option_id']]!='')
					{
						if($orderoption[$product_option['product_option_id']]==$product_option_value['product_option_value_id'])
						{
							$selected='selected';
						}
					}
					//db select
                    $product_option_value_data[] = array(
                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                        'option_value_id'         => $product_option_value['option_value_id'],
						'option_value'            => $product_option_value['option_name'],
                        'name'                    => $product_option_value['name'],
						'selected'				  => $selected,
                        'quantity'                => $product_option_value['quantity'],
                        'subtract'                => $product_option_value['subtract'],
                        'price'                   => $product_option_value['price'],
                        'price_prefix'            => $product_option_value['price_prefix'],
                        'points'                  => $product_option_value['points'],
                        'points_prefix'           => $product_option_value['points_prefix'],
                        'weight'                  => $product_option_value['weight'],
                        'weight_prefix'           => $product_option_value['weight_prefix']
                    );
                }
            }

            $product_option_data[] = array(
                'product_option_id'    => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id'            => $product_option['option_id'],
                'name'                 => $product_option['name'],
                'type'                 => $product_option['type'],
                'value'                => $product_option['value'],
                'required'             => $product_option['required']
            );
        }

        return $product_option_data;
    }
	public function getOrderProductDiscount($order_id,$product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_b2b_order_product WHERE order_id = '" . (int)$order_id . "' and product_id='".$product_id."'");
		return $query->row;

	}
	public function getOrderCustomeFilds($order_id)
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
		return $query->row;

	}
	public function getB2bOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "codevoc_b2b_order_product bop ON (bop.order_product_id  = op.order_product_id) WHERE op.order_id = '" . (int)$order_id . "' order by cast(bop.sort as DECIMAL(10,5)) ASC");
		return $query->rows;
	}

	public function getOrderCustomfield($order_id) {
		$order_query = $this->db->query("SELECT custom_field FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			return array(
				'custom_field'            => json_decode($order_query->row['custom_field'], true)
			);
		} else {
			return;
		}
	}

	public function updateOrderType($order_id, $status) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "order SET order_type = '". $status ."' WHERE order_id = '". (int)$order_id ."'");
	}

	public function getCodevocB2bFortnoxInvoice($order_id) {
		$query = $this->db->query("SELECT fortnox_invoice_nr FROM " . DB_PREFIX . "codevoc_b2b_fortnox WHERE order_id = '" . (int)$order_id . "'");
		
		if($query->num_rows>0)
		{
			return $query->row['fortnox_invoice_nr'];
		}
		else
		{
		return '';
		}		
	}
	public function createFortnoxInvoicenr($invoicenr, $order_id) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_b2b_fortnox SET order_id = '" . (int)$order_id . "', fortnox_invoice_nr = '" . (int)$invoicenr . "'");
	}
	
	public function getCodevocB2bQuotationfile($quotation_id) {
		$query = $this->db->query("SELECT file_final_sketch FROM " . DB_PREFIX . "codevoc_quotation WHERE quotation_id = '" . (int)$quotation_id . "'");
		
		if($query->num_rows>0)
		{
			return $query->row['file_final_sketch'];
		}
		else
		{
		return '';
		}		
	}	
	public function editOrder($order_id, $data) {
		
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
		
		customer_id = '" . (int)$data['customer_id'] . "', 
		firstname = '" . $this->db->escape($data['firstname']) . "', 
		lastname = '" . $this->db->escape($data['lastname']) . "', 
		email = '" . $this->db->escape($data['email']) . "', 
		telephone = '" . $this->db->escape($data['telephone']) . "',
		custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "',  
		payment_address_id = '" . $this->db->escape($data['payment_address_id']) . "',
		shipping_address_id = '" . $this->db->escape($data['shipping_address_id']) . "',
		payment_method = '" . $this->db->escape($data['payment_method']) . "', 
		shipping_method = '" . $this->db->escape($data['shipping_method']) . "',
		payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', 
		payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', 
		payment_company = '" . $this->db->escape($data['payment_company']) . "',
		payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', 
		payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', 
		payment_city = '" . $this->db->escape($data['payment_city']) . "', 
		payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', 
		payment_country = '" . $this->db->escape($data['payment_country']) . "', 
		payment_country_id = '" . (int)$data['payment_country_id'] . "', 
		payment_zone = '" . $this->db->escape($data['payment_zone']) . "', 
		payment_zone_id = '" . (int)$data['payment_zone_id'] . "', 
		payment_custom_field = '" . $this->db->escape(isset($data['payment_custom_field']) ? json_encode($data['payment_custom_field']) : '') . "', 
		shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', 
		shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', 
		shipping_company = '" . $this->db->escape($data['shipping_company']) . "', 
		shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', 
		shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', 
		shipping_city = '" . $this->db->escape($data['shipping_city']) . "', 
		shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', 
		shipping_country = '" . $this->db->escape($data['shipping_country']) . "', 
		shipping_country_id = '" . (int)$data['shipping_country_id'] . "', 
		shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', 
		shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', 
		shipping_custom_field = '" . $this->db->escape(isset($data['shipping_custom_field']) ? json_encode($data['shipping_custom_field']) : '') . "',  
		order_status_id='".$this->db->escape($data['order_status_id'])."',
		total = '" .(float)$data['total']."',
		file_final_sketch = '" . $this->db->escape($data['file_final_sketch']) . "'
		WHERE order_id = '" . (int)$order_id . "'");

		//custom data
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_b2b_order WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_b2b_order` SET  
		order_id = '" . (int)$order_id . "', 
		assignee = '" . $this->db->escape($data['assignee']) . "', 
		vatnr = '" . $this->db->escape($data['vatnr']) . "', 
		payment_company = '" . $this->db->escape($data['payment_company']) . "', 
		shipping_company = '" . $this->db->escape($data['shipping_company']) . "'");
		//custom data
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_b2b_order_product WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
		
		
		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_b2b_order_product SET 
				order_id = '" . (int)$order_id . "', 
				product_id = '" . (int)$product['product_id'] . "', 
				name = '" . $this->db->escape($product['name']) . "', 
				model = '" . $this->db->escape($product['model']) . "', 
				quantity = '" . (int)$product['quantity'] . "', 
				price = '" . (float)$product['price'] . "', 
				total = '" . (float)$product['total'] . "',
				tax = '" . $this->db->escape($product['tax']) . "',
				tax_class_id = '" . $this->db->escape($product['tax_class_id']) . "',
				sort = '" . $this->db->escape($product['b2b_product_sort_order']) . "',
				discount = '" . (float)$product['discount'] . "'");

				$order_product_id = $this->db->getLastId();
				
				foreach ($product['option'] as $option) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
				}

				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET 
				order_id = '" . (int)$order_id . "', 
				product_id = '" . (int)$product['product_id'] . "', 
				name = '" . $this->db->escape($product['name']) . "', 
				model = '" . $this->db->escape($product['model']) . "', 
				quantity = '" . (int)$product['quantity'] . "', 
				price = '" . (float)$product['price'] . "', 
				total = '" . (float)$product['total'] . "',
				tax = '" . $this->db->escape($product['tax']) . "'");

			}
		}

		// Totals
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");

		if (isset($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}
		
		if (isset($data['files_data']) && $data['files_data'] != "") 
		{
				
						// Retrive all database files
						$db_files = $this->db->query("select *  from " . DB_PREFIX . "codevoc_quotation_files where quotation_id = '" . (int)$order_id . "'");
						$db_files = $db_files->rows;
						$db_files_array = array();
						foreach($db_files as $file_item){
							$db_files_array[] = $file_item['filename'];
						}

						// Retrive posted files
						$files = $data['files'];
						// $files = explode("__|__", $files);
						$files=array_unique($files);
            			// Unlink files from folder
						foreach($db_files_array as $db_files_item){
							if(!in_array($db_files_item,$files)){

								// remove from database
								$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_files WHERE filename = '" . $db_files_item . "'");

								$file_path = str_replace("/bossadm", "", DIR_APPLICATION) . "uploads/temp/" . $db_files_item;
								@unlink($file_path);
							}
						}

						$upload_dir = str_replace("/bossadm", "", DIR_APPLICATION) . "uploads/temp/";
						$destination_upload_dir = str_replace("/bossadm", "", DIR_APPLICATION) . "uploads/temp/";

						foreach ($files as $file) {
							if ($file != "") {
								$file_tmp_path = $upload_dir . $file;
								$file_name = md5(uniqid(). $file);
								$file_destination_path = $destination_upload_dir . $file_name;
								$file_original_name = $file;

								if(!in_array($file,$db_files_array)){
									// Move file to destination path
									@rename($file_tmp_path,$file_destination_path);
									$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_files SET language_id = '" . (int)$this->config->get('config_language_id') . "', quotation_id = '" . (int)$order_id . "', filename = '" . $file_name . "', original_filename = '" . $file_original_name . "'");
								}

							}
						}
		}		
	}
	public function import(){
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_pw3` ORDER BY `order_id` ASC LIMIT 10");
		foreach($query->rows as $row){
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET 
			`order_id` = '" . $row['order_id'] . "',
			`invoice_no` = '" . $row['invoice_no'] . "',
			`invoice_prefix` = '" . $row['invoice_prefix'] . "',
			`store_id` = '" . $row['store_id'] . "',
			`store_name` = '" . $row['store_name'] . "',
			`store_url` = '" . $row['store_url'] . "',
			`customer_id` = '" . $row['customer_id'] . "',
			`customer_group_id` = '" . $row['customer_group_id'] . "',
			`firstname` = '" . $row['firstname'] . "',
			`lastname` = '" . $row['lastname'] . "',
			`email` = '" . $row['email'] . "',
			`telephone` = '" . $row['telephone'] . "',
			`fax` = '" . $row['fax'] . "',
			`custom_field` = '" . $row['custom_field'] . "',
			`payment_firstname` = '" . $row['payment_firstname'] . "',
			`payment_lastname` = '" . $row['payment_lastname'] . "',
			`payment_company` = '" . $row['payment_company'] . "',
			`payment_address_1` = '" . $row['payment_address_1'] . "',
			`payment_address_2` = '" . $row['payment_address_2'] . "',
			`payment_city` = '" . $row['payment_city'] . "',
			`payment_postcode` = '" . $row['payment_postcode'] . "',
			`payment_country` = '" . $row['payment_country'] . "',
			`payment_country_id` = '" . $row['payment_country_id'] . "',
			`payment_zone` = '" . $row['payment_zone'] . "',
			`payment_zone_id` = '" . $row['payment_zone_id'] . "',
			`payment_custom_field` = '" . $row['payment_custom_field'] . "',
			`payment_method` = '" . $row['payment_method'] . "',
			`payment_code` = '" . $row['payment_code'] . "',
			`payment_address_format` = '" . $row['payment_address_format'] . "',
			`shipping_firstname` = '" . $row['shipping_firstname'] . "',
			`shipping_lastname` = '" . $row['shipping_lastname'] . "',
			`shipping_company` = '" . $row['shipping_company'] . "',
			`shipping_address_1` = '" . $row['shipping_address_1'] . "',
			`shipping_address_2` = '" . $row['shipping_address_2'] . "',
			`shipping_city` = '" . $row['shipping_city'] . "',
			`shipping_postcode` = '" . $row['shipping_postcode'] . "',
			`shipping_country` = '" . $row['shipping_country'] . "',
			`shipping_country_id` = '" . $row['shipping_country_id'] . "',
			`shipping_zone` = '" . $row['shipping_zone'] . "',
			`shipping_zone_id` = '" . $row['shipping_zone_id'] . "',
			`shipping_custom_field` = '" . $row['shipping_custom_field'] . "',
			`shipping_method` = '" . $row['shipping_method'] . "',
			`shipping_code` = '" . $row['shipping_code'] . "',
			`shipping_address_format` = '" . $row['shipping_address_format'] . "',
			`servicepoint_code` = '" . $row['servicepoint_code'] . "',
			`servicepoint_location` = '" . $row['servicepoint_location'] . "',
			`comment` = '" . $row['comment'] . "',
			`total` = '" . $row['total'] . "',
			`order_status_id` = '" . $row['order_status_id'] . "',
			`commission` = '" . $row['commission'] . "',
			`marketing_id` = '" . $row['marketing_id'] . "',
			`tracking` = '" . $row['tracking'] . "',
			`language_id` = '2',
			`currency_id` = '" . $row['currency_id'] . "',
			`currency_code` = '" . $row['currency_code'] . "',
			`currency_value` = '" . $row['currency_value'] . "',
			`affiliate_id` = '" . $row['affiliate_id'] . "',
			`ip` = '" . $row['ip'] . "',
			`forwarded_ip` = '" . $row['forwarded_ip'] . "',
			`user_agent` = '" . $row['user_agent'] . "',
			`accept_language` = '" . $row['accept_language'] . "',
			`date_added` = '" . $row['date_added'] . "',
			`date_modified` = '" . $row['date_modified'] . "',
			`quotation_id` = '" . $row['quotation_id'] . "',
			`order_type` = '" . $row['order_type'] . "',
			`delivery_date` = '" . $row['delivery_date'] . "'
			");

			$query_history = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_history_pw3` WHERE `order_id` = '" . $row['order_id'] . "'");
			if(count($query_history->rows) > 0){
				foreach($query_history->rows as $row1){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET 
					`order_history_id` = '" . $row1['order_history_id'] . "',
					`order_id` = '" . $row1['order_id'] . "',
					`order_status_id` = '" . $row1['order_status_id'] . "',
					`notify` = '" . $row1['notify'] . "',
					`comment` = '" . $row1['comment'] . "',
					`date_added` = '" . $row1['date_added'] . "'
					");
				}
			}
			$query_option = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option_pw3` WHERE `order_id` = '" . $row['order_id'] . "'");
			if(count($query_option->rows) > 0){
				foreach($query_option->rows as $row2){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "order_option` SET 
					`order_option_id` = '" . $row2['order_option_id'] . "',
					`order_id` = '" . $row2['order_id'] . "',
					`order_product_id` = '" . $row2['order_product_id'] . "',
					`product_option_id` = '" . $row2['product_option_id'] . "',
					`product_option_value_id` = '" . $row2['product_option_value_id'] . "',
					`name` = '" . $row2['name'] . "',
					`value` = '" . $row2['value'] . "',
					`type` = '" . $row2['type'] . "'
					");
				}
			}

			$query_product = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product_pw3` WHERE `order_id` = '" . $row['order_id'] . "'");
			if(count($query_product->rows) > 0){
				foreach($query_product->rows as $row3){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "order_product` SET 
					`order_product_id` = '" . $row3['order_product_id'] . "',
					`order_id` = '" . $row3['order_id'] . "',
					`product_id` = '" . $row3['product_id'] . "',
					`name` = '" . $row3['name'] . "',
					`model` = '" . $row3['model'] . "',
					`quantity` = '" . $row3['quantity'] . "',
					`price` = '" . $row3['price'] . "',
					`total` = '" . $row3['total'] . "',
					`tax` = '" . $row3['tax'] . "',
					`reward` = '" . $row3['reward'] . "'
					");
				}
			}
			$query_product_b2b = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product_pw3` WHERE `order_id` = '" . $row['order_id'] . "'");
			if(count($query_product_b2b->rows) > 0){
				foreach($query_product_b2b->rows as $row4){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_b2b_order_product` SET 
					`order_product_id` = '" . $row4['order_product_id'] . "',
					`order_id` = '" . $row4['order_id'] . "',
					`product_id` = '" . $row4['product_id'] . "',
					`name` = '" . $row4['name'] . "',
					`model` = '" . $row4['model'] . "',
					`quantity` = '" . $row4['quantity'] . "',
					`price` = '" . $row4['price'] . "',
					`total` = '" . $row4['total'] . "',
					`tax` = '" . $row4['tax'] . "'
					");
				}
			}
			// $query_order_option = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_order_option_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
			// if(count($query_order_option->rows) > 0){
			// 	foreach($query_order_option->rows as $row4){
			// 		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_order_option` SET 
			// 		`quotation_option_id` = '" . $row4['quotation_option_id'] . "',
			// 		`quotation_id` = '" . $row4['quotation_id'] . "',
			// 		`quotation_product_id` = '" . $row4['quotation_product_id'] . "',
			// 		`product_option_id` = '" . $row4['product_option_id'] . "',
			// 		`product_option_value_id` = '" . $row4['product_option_value_id'] . "',
			// 		`name` = '" . $row4['name'] . "',
			// 		`value` = '" . $row4['value'] . "',
			// 		`type` = '" . $row4['type'] . "'
			// 		");
			// 	}
			// }
			// $query_order_product = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_order_product_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
			// if(count($query_order_product->rows) > 0){
			// 	foreach($query_order_product->rows as $row5){
			// 		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_order_product` SET 
			// 		`quotation_product_id` = '" . $row5['quotation_product_id'] . "',
			// 		`quotation_id` = '" . $row5['quotation_id'] . "',
			// 		`product_id` = '" . $row5['product_id'] . "',
			// 		`name` = '" . $row5['name'] . "',
			// 		`model` = '" . $row5['model'] . "',
			// 		`quantity` = '" . $row5['quantity'] . "',
			// 		`price` = '" . $row5['price'] . "',
			// 		`total` = '" . $row5['total'] . "',
			// 		`tax` = '" . $row5['tax'] . "',
			// 		`discount` = '" . $row5['discount'] . "',
			// 		`tax_class_id` = '" . $row5['tax_class_id'] . "',
			// 		`sort` = '" . $row5['sort'] . "',
			// 		`reward` = '" . $row5['reward'] . "'
			// 		");
			// 	}
			// }
			$query_total = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total_pw3` WHERE `order_id` = '" . $row['order_id'] . "'");
			if(count($query_total->rows) > 0){
				foreach($query_total->rows as $row6){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "order_total` SET 
					`order_total_id` = '" . $row6['order_total_id'] . "',
					`order_id` = '" . $row6['order_id'] . "',
					`code` = '" . $row6['code'] . "',
					`title` = '" . $row6['title'] . "',
					`value` = '" . $row6['value'] . "',
					`sort_order` = '" . $row6['sort_order'] . "'
					");
				}
			}
			// $query_reminders = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_reminders_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
			// if(count($query_reminders->rows) > 0){
			// 	foreach($query_reminders->rows as $row7){
			// 		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_reminders` SET 
			// 		`id` = '" . $row7['id'] . "',
			// 		`date` = '" . $row7['date'] . "',
			// 		`quotation_id` = '" . $row7['quotation_id'] . "',
			// 		`created_at` = '" . $row7['created_at'] . "',
			// 		`updated_at` = '" . $row7['updated_at'] . "'
			// 		");
			// 	}
			// }
			$query_costs = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_order_costs` WHERE `order_id` = '" . $row['order_id'] . "'");
			if(count($query_costs->rows) > 0){
				foreach($query_costs->rows as $row8){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_order_costs` SET 
					`id` = '" . $row8['id'] . "',
					`order_id` = '" . $row8['order_id'] . "',
					`supplier` = '" . $row8['supplier'] . "',
					`type` = '" . $row8['type'] . "',
					`cost` = '" . $row8['cost'] . "',
					`label` = '" . $row8['label'] . "',
					`created_at` = '" . $row8['created_at'] . "',
					`updated_at` = '" . $row8['updated_at'] . "'
					");
				}
			}
		}
	}
}
