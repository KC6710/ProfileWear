<?php
namespace Opencart\Admin\Model\Codevoc;
class B2bmanagerQuotation extends \Opencart\System\Engine\Model {

	public function addQuotation($data)
	{
		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation` SET

		customer_id = '" . (int)$data['customer_id'] . "',
		firstname = '" . $this->db->escape($data['firstname']) . "',
		lastname = '" . $this->db->escape($data['lastname']) . "',
		email = '" . $this->db->escape($data['email']) . "',
		telephone = '" . $this->db->escape($data['telephone']) . "',
		custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "',
		payment_address_id = '" . $this->db->escape($data['payment_address_id']) . "',
		shipping_address_id = '" . $this->db->escape($data['shipping_address_id']) . "',
		language_id = '" . (int)$this->config->get('config_language_id'). "',
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
		shipping_method = '" . $this->db->escape($data['shipping_method']) . "',
		payment_method = '" . $this->db->escape($data['payment_method']) . "',
		quotation_status_id='".$this->db->escape($data['quotation_status_id'])."',
		comment = '" . $this->db->escape($data['comment']) . "',
		total = '" .(float)$data['total']."',
		file_final_sketch = '" . $this->db->escape($data['file_final_sketch']) . "',
		date_added = NOW()");

		$quotation_id = $this->db->getLastId();
		//custom data
		$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_other_details` SET
		language_id = '" . (int)$this->config->get('config_language_id'). "',
		quotation_id = '" . (int)$quotation_id . "',
		assignee = '" . $this->db->escape($data['assignee']) . "',
		vatnr = '" . $this->db->escape($data['vatnr']) . "',
		create_date = '" . $this->db->escape($data['create_date']) . "',
		expiration_date = '" . $this->db->escape($data['expiration_date']) . "',
		shippment_terms = '" . $this->db->escape($data['shippment_terms']) . "',
		payment_company = '". $this->db->escape($data['payment_company']) . "',
		shipping_company = '". $this->db->escape($data['shipping_company']) . "'"
	);
		//custom data
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_product SET quotation_id = '" . (int)$quotation_id . "',
				product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "',
				model = '" . $this->db->escape($product['model']) . "',
				quantity = '" . (int)$product['quantity'] . "',
				price = '" . (float)$product['price'] . "',
				total = '" . (float)$product['total'] . "',
				sort = '" . $this->db->escape($product['b2b_product_sort_order']) . "',
				tax = '" . $this->db->escape($product['tax']) . "',
				tax_class_id = '" . $this->db->escape($product['tax_class_id']) . "',
				discount = '" . (float)$product['discount'] . "'");

				$quotation_product_id = $this->db->getLastId();

				foreach ($product['option'] as $option) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_option SET quotation_id = '" . (int)$quotation_id . "', quotation_product_id = '" . (int)$quotation_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
					
				}
			}
		}

		// Totals
		if (isset($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_total SET 
				quotation_id = '" . (int)$quotation_id . "', 
				code = '" . $this->db->escape($total['code']) . "',
				title = '" . $this->db->escape($total['title']) . "', 
				`value` = '" . (float)$total['value'] . "', 
				sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}



		 // check files are exist or not
        if (isset($data['files_data']) && $data['files_data'] != "") {
			foreach($data['files'] as $files)
			{
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_files SET language_id = '" . (int)$files['language_id'] . "', quotation_id = '" . (int)$quotation_id . "', filename = '" . $files['name'] . "', original_filename = '" . $files['original_name'] . "'");
			}

        }

		return $quotation_id;
	}

	public function editQuotation($quotation_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "codevoc_quotation` SET 
		
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
		quotation_status_id='".$this->db->escape($data['quotation_status_id'])."',
		total = '" .(float)$data['total']."',
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
		expiration_date = '" . $this->db->escape($data['expiration_date']) . "', 
		shippment_terms = '" . $this->db->escape($data['shippment_terms']) . "'");
		//custom data
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_product WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_option WHERE quotation_id = '" . (int)$quotation_id . "'");
		
		
		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_product SET 
				quotation_id = '" . (int)$quotation_id . "', 
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
		
		if (isset($data['files_data'])) 
		{
				
						// Retrive all database files
						$db_files = $this->db->query("SELECT *  from " . DB_PREFIX . "codevoc_quotation_files where quotation_id = '" . (int)$quotation_id . "'");
						$db_files = $db_files->rows;
						$db_files_array = array();
						foreach($db_files as $file_item){
							$db_files_array[] = $file_item['filename'];
						}

						// Retrive posted files
						$files = $data['files'];
						// $files = explode("__|__", $files);
						$files=array_unique($files,SORT_REGULAR);
						foreach($files as $file){
							$posted_file_names[] = $file['name'];
						}
            			// Unlink files from folder
						if(count($db_files_array) > 0){
							foreach($db_files_array as $file_item){
								if(!in_array($file_item, $posted_file_names )|| empty($posted_file_names)){
									// echo "DELETE FROM " . DB_PREFIX . "codevoc_quotation_files WHERE filename = '" . $file_item . "'"; die;
									// remove from database
									$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_files WHERE filename = '" . $file_item . "'");
			
									$file_path = DIR_OPENCART . "uploads/temp/" . $file_item;
									if(file_exists($file_path)){
										@unlink($file_path);
									}
								}
							}

						}

						$upload_dir = DIR_OPENCART . "uploads/temp/";
						$destination_upload_dir = DIR_OPENCART . "uploads/temp/";

						foreach ($files as $file) {
							if ($file['name'] != "") {
								$file_tmp_path = $upload_dir . $file['original_name'];
								$file_name =  $file['name'];
								$file_destination_path = $destination_upload_dir . $file['name'];
								$file_original_name = $file['original_name'];
								
								if(!in_array($file['name'],$db_files_array)){
									// Move file to destination path
									@rename($file_tmp_path,$file_destination_path);
									$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_files SET language_id = '" . (int)$this->config->get('config_language_id') . "', quotation_id = '" . (int)$quotation_id . "', filename = '" . $file_name . "', original_filename = '" . $file_original_name . "'");
								}

							}
						}
		}		
	}


	public function copyQuotation($quotation_id)
	{

		 $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "codevoc_quotation where quotation_id = '" . (int)$quotation_id . "'");

		 if ($query->num_rows) {
			$data = $query->row;

			$other_detail = $this->getQuotationOtherdetails($quotation_id);

			$data['language_id'] = $other_detail['language_id'];
			$data['quotation_id'] = $other_detail['quotation_id'];
			$data['assignee'] = $other_detail['assignee'];
			$data['vatnr'] = $other_detail['vatnr'];
			$data['payment_company'] = $other_detail['payment_company'];
			$data['shipping_company'] = $other_detail['shipping_company'];
			$data['create_date'] = $other_detail['create_date'];
			$data['delivery_date'] = $other_detail['delivery_date'];
			$data['expiration_date'] = $other_detail['expiration_date'];
			$data['shippment_terms'] = $other_detail['shippment_terms'];
			$data['rate_delay'] = $other_detail['rate_delay'];
			$data['custom_ordernr'] = $other_detail['custom_ordernr'];

			$data['files_data'] = $this->getFilesDetail($quotation_id);
			$data['files'] = $this->getFilesDetail($quotation_id);
			$data['totals'] = $this->getQuotationOrderTotals($quotation_id);
			//set copy quotation status draft
			$data['quotation_status_id'] ='1';
			//set copy quotation status draft
			$new_quotation_id=$this->addQuotation($data);

			$order_products=$this->getQuotationOrderProducts($quotation_id);
			foreach($order_products as $order_product)
			{
					$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_product SET quotation_id = '" . (int)$new_quotation_id . "', product_id = '" . (int)$order_product['product_id'] . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int)$order_product['quantity'] . "', price = '" . (float)$order_product['price'] . "', total = '" . (float)$order_product['total'] . "', tax = '" . (float)$order_product['tax'] . "',discount = '" . (float)$order_product['discount'] . "',`sort` = '" . $this->db->escape($order_product['sort']) . "',tax_class_id = '" . $this->db->escape($order_product['tax_class_id']) . "'");

						$quotation_product_id = $this->db->getLastId();

						$order_options=$this->getQuotationOrderOptions($quotation_id,$order_product['quotation_product_id']);
						foreach($order_options as $option)
						{

								$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_order_option SET quotation_id = '" . (int)$new_quotation_id . "', quotation_product_id = '" . (int)$quotation_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");


						}
			}

		 }


	}

	public function deleteQuotation($quotation_id)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_other_details WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_files WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_product WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_option WHERE quotation_id = '" . (int)$quotation_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_quotation_order_total WHERE quotation_id = '" . (int)$quotation_id . "'");

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


			$this->load->model('customer/customer');

			$affiliate_info = $this->model_customer_customer->getCustomer($quotation_query->row['affiliate_id']);

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
				'payment_address_id'      => $quotation_query->row['payment_address_id'],
				'shipping_address_id'     => $quotation_query->row['shipping_address_id'],
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

	public function getQuotations($data = array()) {

		$sql = "SELECT q.quotation_id, CONCAT(q.firstname, ' ', q.lastname) AS customer,q.custom_field, qo.vatnr, q.total,q.quotation_status_id, q.currency_code, qo.assignee, q.currency_value, q.date_added FROM `" . DB_PREFIX . "codevoc_quotation` q LEFT JOIN `" . DB_PREFIX . "codevoc_quotation_other_details` qo ON q.quotation_id = qo.quotation_id";

		if (isset($data['filter_quotation_status_id']) && $data['filter_quotation_status_id'] !== '')
		{
			if($data['filter_quotation_status_id']!='all')
			{
				$sql .= " WHERE quotation_status_id = '" . (int)$data['filter_quotation_status_id'] . "'";
			}
			else
			{
				$sql .= " WHERE quotation_status_id >= '0'";
			}
		} else {
			$sql .= " WHERE quotation_status_id >= '0'";
		}
		//v7
			if (isset($data['filter_search']) && $data['filter_search'] != '')
			{

				$implode = array();
				$implode[]= " q.quotation_id= '".$this->db->escape($data['filter_search'])."'";
				$implode[]= " q.customer_id= '".$this->db->escape($data['filter_search'])."'";
				$implode[]=" CONCAT(q.firstname, ' ', q.lastname) LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.custom_field LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.email LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.telephone LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.payment_company LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.shipping_company LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.payment_country LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.shipping_country LIKE '%" . $this->db->escape($data['filter_search']) . "%'";

				$sql .= " AND (" . implode(" OR ", $implode) . ")";

			}
			if (isset($data['assignee_filter']) && $data['assignee_filter'] != '') {
				$sql .= " AND qo.assignee = '" . (int)$data['assignee_filter'] ."'" ;
			}
		//v7
		$sort_data = array(
			'q.quotation_id',
			'q.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY q.quotation_id";
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

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getQuotationOtherdetails($quotation_id) {
		$query = $this->db->query("SELECT *  FROM " . DB_PREFIX . "codevoc_quotation_other_details WHERE quotation_id = '" . (int)$quotation_id . "' and language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;

	}

	public function getQuotationOrderProducts($quotation_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_quotation_order_product WHERE quotation_id = '" . (int)$quotation_id . "' order by cast(`sort` as DECIMAL(10,5)) ASC");
		return $query->rows;
	}

	public function getQuotationOrderOptions($quotation_id, $quotation_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_quotation_order_option WHERE quotation_id = '" . (int)$quotation_id . "' AND quotation_product_id = '" . (int)$quotation_product_id . "'");

		return $query->rows;
	}

	//get products autocomplete
	public function getFindproducts($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND p.status = 1";

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

	public function getQuotationOrderTotals($quotation_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_quotation_order_total WHERE quotation_id = '" . (int)$quotation_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getProductOptions($product_id){
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.required = 1 AND po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY po.product_option_id");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();
            $product_option_value_query = $this->db->query("SELECT *, ovd.name as option_name FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name ASC");

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
						'selected'				  =>$selected,
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

	public function getFilesDetail($quotation_id) {
        $query = $this->db->query("SELECT   *  FROM " . DB_PREFIX . "codevoc_quotation_files WHERE  quotation_id = '" . (int) $quotation_id . "'");

        $filesArr = array();
        foreach ($query->rows as $result) {

            //get file information
            $filesArr[] = array(
                'id' => $result['id'],
                'language_id' => $result['language_id'],
                'quotation_id' => $result['quotation_id'],
                'filename' => $result['filename'],
                'original_filename' => $result['original_filename'],
            );
        }

        return $filesArr;
    }

		public function getFileDetail($quotation_id, $file_id) {
			$query = $this->db->query("SELECT   *  FROM " . DB_PREFIX . "codevoc_quotation_files WHERE  quotation_id = '" . (int) $quotation_id . "' AND id = ". (int)$file_id);

			return $query->row;
	}

	public function getTotalQuotations($data = array()) {
		//$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "codevoc_quotation`";
        //v7
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "codevoc_quotation` q LEFT JOIN `" . DB_PREFIX . "codevoc_quotation_other_details` qo ON q.quotation_id = qo.quotation_id";
		//v7
		if (isset($data['filter_quotation_status_id']) && $data['filter_quotation_status_id'] !== '')
		{
			if($data['filter_quotation_status_id']!='all')
			{
				$sql .= " WHERE q.quotation_status_id = '" . (int)$data['filter_quotation_status_id'] . "'";
			}
			else
			{
				$sql .= " WHERE q.quotation_status_id > '0'";
			}
		} else {
			$sql .= " WHERE quotation_status_id > '0'";
		}
		if (isset($data['assignee_filter']) && $data['assignee_filter'] != '') {
			$sql .= " AND qo.assignee = '" . (int)$data['assignee_filter'] ."'" ;
		}
		//v7
			if (isset($data['filter_search']) && $data['filter_search'] !== '')
			{
				$implode = array();
				$implode[]= " q.quotation_id= '".$this->db->escape($data['filter_search'])."'";
				$implode[]=" CONCAT(q.firstname, ' ', q.lastname) LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.custom_field LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.email LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.telephone LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.payment_company LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.shipping_company LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.payment_country LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
				$implode[]=" q.shipping_country LIKE '%" . $this->db->escape($data['filter_search']) . "%'";

				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}

		//v7
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getOrderByQuotation($quotation_id)
	{
		$query = $this->db->query("SELECT order_id,total FROM `" . DB_PREFIX . "order` WHERE quotation_id = '". (int)$quotation_id ."'");
		return $query->row['order_id'];
	}
	public function getOrderTotalsList($order_id) {
		$order_data = $this->db->query("SELECT code,value FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "'");
		if ($order_data->num_rows) {
			return $order_data->rows;
		} else {
			return false;
		}       
	}
	    	
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->rows;
	}	

	public function getQuotationStatuses(){
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "' order by name ASC");
		return $query->rows;
	}
	 public function getQuotationsettings() {
        $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "codevoc_quotation_settings where setting_id = '1'");
        return $query->row;
    }

		public function getQuotationReminders($quotation_id) {
			$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "codevoc_quotation_reminders where quotation_id = '". (int)$quotation_id ."'");
			return $query->rows;
		}

		public function getLastQuotationReminder($quotation_id) {
			$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "codevoc_quotation_reminders where quotation_id = '". (int)$quotation_id ."' order by date desc limit 1");
			return $query->row;
		}

		public function createQuotationReminder($quotation_id) {
			$query = $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_reminders SET quotation_id = '". (int)$quotation_id ."', date = '". date('Y-m-d H:i:s') ."'");
		}

		public function updateQuotationStatus($quotation_id, $status) {
			$query = $this->db->query("UPDATE " . DB_PREFIX . "codevoc_quotation SET quotation_status_id = '". (int)$status ."' WHERE quotation_id = '". (int)$quotation_id ."'");
		}

		public function updateAssigneeStatus($quotation_id, $status) {
			$query = $this->db->query("UPDATE " . DB_PREFIX . "codevoc_quotation_other_details SET assignee = '". (int)$status ."' WHERE quotation_id = '". (int)$quotation_id ."'");
		}

		public function saveCancelReason($quotation_id, $data) {
			$timestamp = date("Y-m-d H:i:s");

			$query = $this->db->query("select * from " . DB_PREFIX . "codevoc_quotation_cancelled where quotation_id = '".(int)$quotation_id."' limit 1");
			if($query->num_rows <= 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_quotation_cancelled SET quotation_id = '" . (int)$quotation_id . "', reason = '" . $this->db->escape($data['reason']) . "', comment = '" . $this->db->escape($data['comment']) . "', created_at = '" . $timestamp . "', updated_at = '" . $timestamp ."'" );

					$reasonId = $this->db->getLastId();
			}else if($query->num_rows > 0) {
					$this->db->query("UPDATE ". DB_PREFIX . "codevoc_quotation_cancelled SET reason = '". $this->db->escape($data['reason']) . "', comment = '".$this->db->escape($data['comment'])."' where quotation_id = '".(int)$quotation_id."'");
			}

			// Set status to cancel
			$this->db->query("update " . DB_PREFIX . "codevoc_quotation set quotation_status_id=4 WHERE quotation_id = '" . (int)$quotation_id . "'");

			// add history
			// $this->addHistory($data['user_id'],$quotation_id,2,'');

			return $reasonId;
		}

		public function addAddress($address){
			$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET `customer_id` = '" . (int)$address['customer_id'] . "', `firstname` = '" . $this->db->escape($address['firstname']) . "', `lastname` = '" . $this->db->escape($address['lastname']) . "', `company` = '" . $this->db->escape($address['company']) . "', `address_1` = '" . $this->db->escape($address['address_1']) . "', `address_2` = '" . $this->db->escape($address['address_2']) . "', `city` = '" . $this->db->escape($address['city']) . "', `postcode` = '" . $this->db->escape($address['postcode']) . "', `country_id` = '" . (int)$address['country_id'] . "', `zone_id` = '" . (int)$address['zone_id'] . "'");
			$address_id = $this->db->getLastId();

			return $address_id;
		}
        public function import(){
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_pw3` ORDER BY `quotation_id`");
			// echo "<pre>"; print_r($row); die;
			foreach($query->rows as $row){
				$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation` SET 
				`quotation_id` = '" . $row['quotation_id'] . "',
				`store_id` = '" . $row['store_id'] . "',
				`store_name` = '" . $row['store_name'] . "',
				`store_url` = '" . $row['store_url'] . "',
				`customer_id` = '" . $row['customer_id'] . "',
				`customer_group_id` = '" . $row['customer_group_id'] . "',
				`firstname` = '" . $row['firstname'] . "',
				`lastname` = '" . $row['lastname'] . "',
				`email` = '" . $row['email'] . "',
				`telephone` = '" . $row['telephone'] . "',
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
				`comment` = '" . $row['comment'] . "',
				`file_final_sketch` = '" . $row['file_final_sketch'] . "',
				`total` = '" . $row['total'] . "',
				`quotation_status_id` = '" . $row['quotation_status_id'] . "',
				`affiliate_id` = '" . $row['affiliate_id'] . "',
				`commission` = '" . $row['commission'] . "',
				`marketing_id` = '" . $row['marketing_id'] . "',
				`tracking` = '" . $row['tracking'] . "',
				`language_id` = '2',
				`currency_id` = '" . $row['currency_id'] . "',
				`currency_code` = '" . $row['currency_code'] . "',
				`currency_value` = '" . $row['currency_value'] . "',
				`date_added` = '" . $row['date_added'] . "',
				`purchase_link` = '" . $row['purchase_link'] . "'
				");

				$query_other = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_other_details_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
				if(count($query_other->rows) > 0){
					foreach($query_other->rows as $row1){
						$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_other_details` SET 
						`id` = '" . $row1['id'] . "',
						`language_id` = '2',
						`quotation_id` = '" . $row1['quotation_id'] . "',
						`assignee` = '" . $row1['assignee'] . "',
						`vatnr` = '" . $row1['vatnr'] . "',
						`payment_company` = '" . $row1['payment_company'] . "',
						`shipping_company` = '" . $row1['shipping_company'] . "',
						`create_date` = '" . $row1['create_date'] . "',
						`delivery_date` = '" . $row1['delivery_date'] . "',
						`expiration_date` = '" . $row1['expiration_date'] . "',
						`payment_terms` = '" . $row1['payment_terms'] . "',
						`shippment_terms` = '" . $row1['shippment_terms'] . "',
						`rate_delay` = '" . $row1['rate_delay'] . "',
						`custom_ordernr` = '" . $row1['custom_ordernr'] . "',
						`utmcsr` = '" . $row1['utmcsr'] . "',
						`utmcmd` = '" . $row1['utmcmd'] . "',
						`utmccn` = '" . $row1['utmccn'] . "'
						");
					}
				}
				$query_cancel = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_cancelled_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
				if(count($query_cancel->rows) > 0){
					foreach($query_cancel->rows as $row2){
						$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_cancelled` SET 
						`id` = '" . $row2['id'] . "',
						`quotation_id` = '" . $row2['quotation_id'] . "',
						`reason` = '" . $row2['reason'] . "',
						`comment` = '" . $row2['comment'] . "',
						`created_at` = '" . $row2['created_at'] . "',
						`updated_at` = '" . $row2['updated_at'] . "'
						");
					}
				}

				$query_files = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_files_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
				if(count($query_files->rows) > 0){
					foreach($query_files->rows as $row3){
						$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_files` SET 
						`id` = '" . $row3['id'] . "',
						`language_id` = '2',
						`quotation_id` = '" . $row3['quotation_id'] . "',
						`filename` = '" . $row3['filename'] . "',
						`original_filename` = '" . $row3['original_filename'] . "'
						");
					}
				}
				$query_order_option = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_order_option_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
				if(count($query_order_option->rows) > 0){
					foreach($query_order_option->rows as $row4){
						$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_order_option` SET 
						`quotation_option_id` = '" . $row4['quotation_option_id'] . "',
						`quotation_id` = '" . $row4['quotation_id'] . "',
						`quotation_product_id` = '" . $row4['quotation_product_id'] . "',
						`product_option_id` = '" . $row4['product_option_id'] . "',
						`product_option_value_id` = '" . $row4['product_option_value_id'] . "',
						`name` = '" . $row4['name'] . "',
						`value` = '" . $row4['value'] . "',
						`type` = '" . $row4['type'] . "'
						");
					}
				}
				$query_order_product = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_order_product_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
				if(count($query_order_product->rows) > 0){
					foreach($query_order_product->rows as $row5){
						$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_order_product` SET 
						`quotation_product_id` = '" . $row5['quotation_product_id'] . "',
						`quotation_id` = '" . $row5['quotation_id'] . "',
						`product_id` = '" . $row5['product_id'] . "',
						`name` = '" . $row5['name'] . "',
						`model` = '" . $row5['model'] . "',
						`quantity` = '" . $row5['quantity'] . "',
						`price` = '" . $row5['price'] . "',
						`total` = '" . $row5['total'] . "',
						`tax` = '" . $row5['tax'] . "',
						`discount` = '" . $row5['discount'] . "',
						`tax_class_id` = '" . $row5['tax_class_id'] . "',
						`sort` = '" . $row5['sort'] . "',
						`reward` = '" . $row5['reward'] . "'
						");
					}
				}
				$query_total = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_order_total_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
				if(count($query_total->rows) > 0){
					foreach($query_total->rows as $row6){
						$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_order_total` SET 
						`quotation_total_id` = '" . $row6['quotation_total_id'] . "',
						`quotation_id` = '" . $row6['quotation_id'] . "',
						`code` = '" . $row6['code'] . "',
						`title` = '" . $row6['title'] . "',
						`value` = '" . $row6['value'] . "',
						`sort_order` = '" . $row6['sort_order'] . "'
						");
					}
				}
				$query_reminders = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_quotation_reminders_pw3` WHERE `quotation_id` = '" . $row['quotation_id'] . "'");
				if(count($query_reminders->rows) > 0){
					foreach($query_reminders->rows as $row7){
						$this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_quotation_reminders` SET 
						`id` = '" . $row7['id'] . "',
						`date` = '" . $row7['date'] . "',
						`quotation_id` = '" . $row7['quotation_id'] . "',
						`created_at` = '" . $row7['created_at'] . "',
						`updated_at` = '" . $row7['updated_at'] . "'
						");
					}
				}
			}
		}

}
