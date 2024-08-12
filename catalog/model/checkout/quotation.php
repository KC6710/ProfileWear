<?php
namespace Opencart\Catalog\Model\Checkout;
class Quotation extends \Opencart\System\Engine\Model {
	public function addQuotation($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation` SET  
		
		store_id = '" . (int)$data['store_id'] . "', 
		store_name = '" . $this->db->escape($data['store_name']) . "', 
		store_url = '" . $this->db->escape($data['store_url']) . "', 
		customer_id = '" . (int)$data['customer_id'] . "', 
		customer_group_id = '" . (int)$data['customer_group_id'] . "', 
		firstname = '" . $this->db->escape($data['firstname']) . "', 
		lastname = '" . $this->db->escape($data['lastname']) . "', 
		email = '" . $this->db->escape($data['email']) . "', 
		telephone = '" . $this->db->escape($data['telephone']) . "', 
		custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', 
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
		payment_method = '" . $this->db->escape($data['payment_method']) . "', 
		payment_code = '" . $this->db->escape($data['payment_code']) . "', 
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
		shipping_method = '" . $this->db->escape($data['shipping_method']) . "', 
		shipping_code = '" . $this->db->escape($data['shipping_code']) . "', 
		total = '" . (float)$data['total'] . "', 
		affiliate_id = '" . (int)$data['affiliate_id'] . "', 
		commission = '" . (float)$data['commission'] . "', 
		marketing_id = '" . (int)$data['marketing_id'] . "', 
		tracking = '" . $this->db->escape($data['tracking']) . "', 
		language_id = '" . (int)$data['language_id'] . "', 
		currency_id = '" . (int)$data['currency_id'] . "', 
		currency_code = '" . $this->db->escape($data['currency_code']) . "', 
		currency_value = '" . (float)$data['currency_value'] . "', 
		quotation_status_id='".$this->db->escape($data['quotation_status_id'])."',
		comment = '" . $this->db->escape($data['comment']) . "',
		file_final_sketch = '" . $this->db->escape($data['file_final_sketch']) . "',
		date_added = NOW()");

		$quotation_id = $this->db->getLastId();
		
		//custom data
		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_other_details` SET  
		language_id = '" . (int)$this->config->get('config_language_id'). "', 
		quotation_id = '" . (int)$quotation_id . "', 
		assignee = '" . $this->db->escape($data['assignee']) . "', 
		vatnr = '" . $this->db->escape($data['vatnr']) . "', 
		payment_company = '" . $this->db->escape($data['payment_company']) . "', 
		shipping_company = '" . $this->db->escape($data['shipping_company']) . "', 
		create_date = '" . $this->db->escape($data['create_date']) . "', 
		delivery_date = '" . $this->db->escape($data['delivery_date']) . "', 
		expiration_date = '" . $this->db->escape($data['expiration_date']) . "', 
		shippment_terms = '" . $this->db->escape($data['shippment_terms']) . "', 
		rate_delay = '" . (int)$data['rate_delay'] . "', 
		custom_ordernr = '" . $data['custom_ordernr'] . "'");
		//custom data
		
		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_product SET quotation_id = '" . (int)$quotation_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "',discount = '" . (float)$product['b2b_product_discount'] . "',`sort` = '" . $this->db->escape($product['b2b_product_sort_order']) . "',tax_class_id = '" . $this->db->escape($product['b2b_product_tax_class_id']) . "'");

				$quotation_product_id = $this->db->getLastId();

				foreach ($product['option'] as $option) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_option SET quotation_id = '" . (int)$quotation_id . "', quotation_product_id = '" . (int)$quotation_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
				}
			}
		}

		
		// Totals
		if (isset($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_total SET quotation_id = '" . (int)$quotation_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}
		
		
		 // Check if files exist
		if (isset($data['files_data']) ) {
			$file_names = json_decode($data['files_data'], true);
			$original_file_name = json_decode($data['original_file_name'], true);
			if (!empty($file_names)) {
				$destination_dir = DIR_OPENCART . "uploads/temp/";

				// Create the directory if it does not exist
				if (!is_dir($destination_dir)) {
					mkdir($destination_dir, 0755, true);
				}

				foreach ($file_names as $key => $file_name) {
					$source_path = DIR_OPENCART . "uploads/temp/" . $file_name; // Assuming the file is jpg
					$destination_path = $destination_dir . $file_name;

					// Move file from temp to destination directory
					if (rename($source_path, $destination_path)) {
						// Insert into database
						$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_files SET language_id = '" . (int)$this->config->get('config_language_id') . "', quotation_id = '" . (int)$quotation_id . "', filename = '" . $this->db->escape($file_name) . "', original_filename = '" . $this->db->escape($original_file_name[$key]) . "'");
					}
				}
			}
		}

		
		return $quotation_id;
	}
	public function editQuotation($quotation_id, $data) {
		
		$this->db->query("UPDATE `" . DB_PREFIX . "codevoc_quotation` SET 
		
		store_id = '" . (int)$data['store_id'] . "', 
		store_name = '" . $this->db->escape($data['store_name']) . "', 
		store_url = '" . $this->db->escape($data['store_url']) . "', 
		customer_id = '" . (int)$data['customer_id'] . "', 
		customer_group_id = '" . (int)$data['customer_group_id'] . "', 
		firstname = '" . $this->db->escape($data['firstname']) . "', 
		lastname = '" . $this->db->escape($data['lastname']) . "', 
		email = '" . $this->db->escape($data['email']) . "', 
		telephone = '" . $this->db->escape($data['telephone']) . "', 
		custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', 
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
		payment_method = '" . $this->db->escape($data['payment_method']) . "', 
		payment_code = '" . $this->db->escape($data['payment_code']) . "', 
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
		shipping_method = '" . $this->db->escape($data['shipping_method']) . "', 
		shipping_code = '" . $this->db->escape($data['shipping_code']) . "', 
		total = '" . (float)$data['total'] . "', 
		affiliate_id = '" . (int)$data['affiliate_id'] . "', 
		commission = '" . (float)$data['commission'] . "', 
		marketing_id = '" . (int)$data['marketing_id'] . "', 
		tracking = '" . $this->db->escape($data['tracking']) . "', 
		language_id = '" . (int)$data['language_id'] . "', 
		currency_id = '" . (int)$data['currency_id'] . "', 
		currency_code = '" . $this->db->escape($data['currency_code']) . "', 
		currency_value = '" . (float)$data['currency_value'] . "', 
		quotation_status_id='".$this->db->escape($data['quotation_status_id'])."',
		comment = '" . $this->db->escape($data['comment']) . "',
		file_final_sketch = '" . $this->db->escape($data['file_final_sketch']) . "'
		WHERE quotation_id = '" . (int)$quotation_id . "'");

		//custom data
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_other_details WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_other_details` SET  
		language_id = '" . (int)$this->config->get('config_language_id'). "', 
		quotation_id = '" . (int)$quotation_id . "', 
		assignee = '" . $this->db->escape($data['assignee']) . "', 
		vatnr = '" . $this->db->escape($data['vatnr']) . "', 
		payment_company = '" . $this->db->escape($data['payment_company']) . "', 
		shipping_company = '" . $this->db->escape($data['shipping_company']) . "', 
		create_date = '" . $this->db->escape($data['create_date']) . "', 
		delivery_date = '" . $this->db->escape($data['delivery_date']) . "', 
		expiration_date = '" . $this->db->escape($data['expiration_date']) . "', 
		shippment_terms = '" . $this->db->escape($data['shippment_terms']) . "', 
		rate_delay = '" . (int)$data['rate_delay'] . "', 
		custom_ordernr = '" . $data['custom_ordernr'] . "'");
		//custom data
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_product WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_option WHERE quotation_id = '" . (int)$quotation_id . "'");
		
		
		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_product SET quotation_id = '" . (int)$quotation_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "',discount = '" . (float)$product['b2b_product_discount'] . "',`sort` = '" . $this->db->escape($product['b2b_product_sort_order']) . "',tax_class_id = '" . $this->db->escape($product['b2b_product_tax_class_id']) . "'");

				$quotation_product_id = $this->db->getLastId();
				
				foreach ($product['option'] as $option) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_option SET quotation_id = '" . (int)$quotation_id . "', quotation_product_id = '" . (int)$quotation_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
				}
			}
		}

		// Totals
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_total WHERE quotation_id = '" . (int)$quotation_id . "'");

		if (isset($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_total SET quotation_id = '" . (int)$quotation_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}
		
		if (isset($data['files_data']) && $data['files_data'] != "") 
		{
				
						// Retrive all database files
						$db_files = $this->db->query("select *  from " . DB_PREFIX . "codevoc_quotation_files where quotation_id = '" . (int)$quotation_id . "'");
						$db_files = $db_files->rows;
						$db_files_array = array();
						foreach($db_files as $file_item){
							$db_files_array[] = $file_item['filename'];
						}

						// Retrive posted files
						$files = $data['files_data'];
						$files = explode("__|__", $files);
						$files=array_unique($files);
            			// Unlink files from folder
						foreach($db_files_array as $db_files_item){
							if(!in_array($db_files_item,$files)){

								// remove from database
								$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_files WHERE filename = '" . $db_files_item . "'");

								$file_path = str_replace("/catalog", "", DIR_APPLICATION) . "uploads/codevoc/" . $db_files_item;
								@unlink($file_path);
							}
						}

						$upload_dir = str_replace("/catalog", "", DIR_APPLICATION) . "uploads/temp/";
						$destination_upload_dir = str_replace("/catalog", "", DIR_APPLICATION) . "uploads/codevoc/";

						foreach ($files as $file) {
							if ($file != "") {
								$file_tmp_path = $upload_dir . $file;
								$file_name = md5(uniqid(). $file);
								$file_destination_path = $destination_upload_dir . $file_name;
								$file_original_name = $file;

								if(!in_array($file,$db_files_array)){
									// Move file to destination path
									@rename($file_tmp_path,$file_destination_path);
									$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_files SET language_id = '" . (int)$this->config->get('config_language_id') . "', quotation_id = '" . (int)$quotation_id . "', filename = '" . $file_name . "', original_filename = '" . $file_original_name . "'");
								}

							}
						}
		}		
	}
	public function getQuotation($quotation_id) {
		$quotation_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = q.customer_id) AS customer, 
		(SELECT qs.name FROM " . DB_PREFIX . "codevoc_quotation_status qs WHERE qs.quotation_status_id = q.quotation_status_id AND qs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status
		FROM `" . DB_PREFIX . "codevoc_quotation` q WHERE q.quotation_id = '" . (int)$quotation_id . "'");

		if ($quotation_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$quotation_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$quotation_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$quotation_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$quotation_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			
			$this->load->model('account/customer');

			$affiliate_info = $this->model_account_customer->getCustomer($quotation_query->row['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($quotation_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			return array(
				'quotation_id'            => $quotation_query->row['quotation_id'],
				'store_id'                => $quotation_query->row['store_id'],
				'store_name'              => $quotation_query->row['store_name'],
				'store_url'               => $quotation_query->row['store_url'],
				'customer_id'             => $quotation_query->row['customer_id'],
				'customer'                => $quotation_query->row['customer'],
				'customer_group_id'       => $quotation_query->row['customer_group_id'],
				'firstname'               => $quotation_query->row['firstname'],
				'lastname'                => $quotation_query->row['lastname'],
				'email'                   => $quotation_query->row['email'],
				'telephone'               => $quotation_query->row['telephone'],
				'custom_field'            => json_decode($quotation_query->row['custom_field'], true),
				'payment_firstname'       => $quotation_query->row['payment_firstname'],
				'payment_lastname'        => $quotation_query->row['payment_lastname'],
				'payment_company'         => $quotation_query->row['payment_company'],
				'payment_address_1'       => $quotation_query->row['payment_address_1'],
				'payment_address_2'       => $quotation_query->row['payment_address_2'],
				'payment_postcode'        => $quotation_query->row['payment_postcode'],
				'payment_city'            => $quotation_query->row['payment_city'],
				'payment_zone_id'         => $quotation_query->row['payment_zone_id'],
				'payment_zone'            => $quotation_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $quotation_query->row['payment_country_id'],
				'payment_country'         => $quotation_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_custom_field'    => json_decode($quotation_query->row['payment_custom_field'], true),
				'payment_method'          => $quotation_query->row['payment_method'],
				'payment_code'            => $quotation_query->row['payment_code'],
				'shipping_firstname'      => $quotation_query->row['shipping_firstname'],
				'shipping_lastname'       => $quotation_query->row['shipping_lastname'],
				'shipping_company'        => $quotation_query->row['shipping_company'],
				'shipping_address_1'      => $quotation_query->row['shipping_address_1'],
				'shipping_address_2'      => $quotation_query->row['shipping_address_2'],
				'shipping_postcode'       => $quotation_query->row['shipping_postcode'],
				'shipping_city'           => $quotation_query->row['shipping_city'],
				'shipping_zone_id'        => $quotation_query->row['shipping_zone_id'],
				'shipping_zone'           => $quotation_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $quotation_query->row['shipping_country_id'],
				'shipping_country'        => $quotation_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_custom_field'   => json_decode($quotation_query->row['shipping_custom_field'], true),
				'shipping_method'         => $quotation_query->row['shipping_method'],
				'shipping_code'           => $quotation_query->row['shipping_code'],
				'comment'                 => $quotation_query->row['comment'],
				'file_final_sketch'       => $quotation_query->row['file_final_sketch'],
				'total'                   => $quotation_query->row['total'],
				'quotation_status_id'     => $quotation_query->row['quotation_status_id'],
				'affiliate_id'            => $quotation_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $quotation_query->row['commission'],
				'language_id'             => $quotation_query->row['language_id'],
				'language_code'           => $language_code,
				'currency_id'             => $quotation_query->row['currency_id'],
				'currency_code'           => $quotation_query->row['currency_code'],
				'currency_value'          => $quotation_query->row['currency_value'],
				'date_added'              => $quotation_query->row['date_added']
			);
		} else {
			return;
		}
	}
	public function getQuotationProducts($quotation_id) : array{
		$products = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_quotation_order_product WHERE quotation_id = '" . (int)$quotation_id . "' order by cast(`sort` as DECIMAL(10,5)) ASC");
		return $products->rows;
	}
	public function getQuotationOrderOptions($quotation_id, $quotation_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_quotation_order_option WHERE quotation_id = '" . (int)$quotation_id . "' AND quotation_product_id = '" . (int)$quotation_product_id . "'");

		return $query->rows;
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
	public function getQuotationOrderTotals($quotation_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_quotation_order_total WHERE quotation_id = '" . (int)$quotation_id . "' ORDER BY sort_order");

		return $query->rows;
	}
}