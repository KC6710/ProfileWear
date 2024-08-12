<?php
namespace Opencart\Admin\Model\Customer;
use \Opencart\System\Helper AS Helper;
class Customer extends \Opencart\System\Engine\Model {
	public function addCustomer(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET `store_id` = '" . (int)$data['store_id'] . "', `customer_group_id` = '" . (int)$data['customer_group_id'] . "', `firstname` = '" . $this->db->escape((string)$data['firstname']) . "', `lastname` = '" . $this->db->escape((string)$data['lastname']) . "', `email` = '" . $this->db->escape((string)$data['email']) . "', `telephone` = '" . $this->db->escape((string)$data['telephone']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode([])) . "', `newsletter` = '" . (isset($data['newsletter']) ? (bool)$data['newsletter'] : 0) . "', `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "', `status` = '" . (isset($data['status']) ? (bool)$data['status'] : 0) . "', `safe` = '" . (isset($data['safe']) ? (bool)$data['safe'] : 0) . "', `date_added` = NOW()");

		$customer_id = $this->db->getLastId();

		if (isset($data['address'])) {
			foreach ($data['address'] as $key => $address) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET `customer_id` = '" . (int)$customer_id . "', `firstname` = '" . $this->db->escape($address['firstname']) . "', `lastname` = '" . $this->db->escape($address['lastname']) . "', `company` = '" . $this->db->escape($address['company']) . "', `address_1` = '" . $this->db->escape($address['address_1']) . "', `address_2` = '" . $this->db->escape($address['address_2']) . "', `city` = '" . $this->db->escape($address['city']) . "', `postcode` = '" . $this->db->escape($address['postcode']) . "', `country_id` = '" . (int)$address['country_id'] . "', `zone_id` = '" . (int)$address['zone_id'] . "', `custom_field` = '" . $this->db->escape(isset($address['custom_field']) ? json_encode($address['custom_field']) : json_encode([])) . "', `default` = '" . (isset($address['default']) ? (int)$address['default'] : 0) . "'");
			}
		}

		return $customer_id;
	}

	public function editCustomer(int $customer_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `store_id` = '" . (int)$data['store_id'] . "', `customer_group_id` = '" . (int)$data['customer_group_id'] . "', `firstname` = '" . $this->db->escape((string)$data['firstname']) . "', `lastname` = '" . $this->db->escape((string)$data['lastname']) . "', `email` = '" . $this->db->escape((string)$data['email']) . "', `telephone` = '" . $this->db->escape((string)$data['telephone']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode([])) . "', `newsletter` = '" . (isset($data['newsletter']) ? (bool)$data['newsletter'] : 0) . "', `status` = '" . (isset($data['status']) ? (bool)$data['status'] : 0) . "', `safe` = '" . (isset($data['safe']) ? (bool)$data['safe'] : 0) . "' WHERE `customer_id` = '" . (int)$customer_id . "'");

		if ($data['password']) {
			$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");

		if (isset($data['address'])) {
			foreach ($data['address'] as $key => $address) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET `address_id` = '" . (int)$address['address_id'] . "', `customer_id` = '" . (int)$customer_id . "', `firstname` = '" . $this->db->escape($address['firstname']) . "', `lastname` = '" . $this->db->escape($address['lastname']) . "', `company` = '" . $this->db->escape($address['company']) . "', `address_1` = '" . $this->db->escape($address['address_1']) . "', `address_2` = '" . $this->db->escape($address['address_2']) . "', `city` = '" . $this->db->escape($address['city']) . "', `postcode` = '" . $this->db->escape($address['postcode']) . "', `country_id` = '" . (int)$address['country_id'] . "', `zone_id` = '" . (int)$address['zone_id'] . "', `custom_field` = '" . $this->db->escape(isset($address['custom_field']) ? json_encode($address['custom_field']) : json_encode([])) . "', `default` = '" . (isset($address['default']) ? (int)$address['default'] : 0) . "'");
			}
		}
	}

	public function updateNewDetails(int $customer_id, array $data): void {
		$company = json_encode(array($data['custom_field_id_forerag'] => $data['forerag'] , $data['custom_field_id_vatnr'] => $data['vatnr']));
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `firstname` = '" . $this->db->escape((string)$data['firstname']) . "', `lastname` = '" . $this->db->escape((string)$data['lastname']) . "', `custom_field` = '" . $this->db->escape((string)$company) . "', `email` = '" . $this->db->escape((string)$data['email']) . "', `telephone` = '" . $this->db->escape((string)$data['telephone']) . "' WHERE `customer_id` = '" . (int)$customer_id . "'");

	}

	public function editToken(int $customer_id, string $token): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `token` = '" . $this->db->escape($token) . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	public function deleteCustomer(int $customer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_activity` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_affiliate` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_affiliate_report` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_approval` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_history` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_wishlist` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_ip` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	public function getCustomer(int $customer_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function getCustomerByEmail(string $email): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");

		return $query->row;
	}

	public function getCustomers(array $data = []): array {
		$sql = "SELECT *, CONCAT(c.`firstname`, ' ', c.`lastname`) AS `name`, cgd.`name` AS `customer_group` FROM `" . DB_PREFIX . "customer` c LEFT JOIN `" . DB_PREFIX . "customer_group_description` cgd ON (c.`customer_group_id` = cgd.`customer_group_id`)";

		$sql .= " WHERE cgd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_search']) && $data['filter_search'] != '')
		{

			$implode = array();
			$implode[]= " c.customer_id= '".$this->db->escape($data['filter_search'])."'";
			$implode[]=" CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
			$implode[]=" c.custom_field LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
			$implode[]=" c.email LIKE '%" . $this->db->escape($data['filter_search']) . "%'";

			$sql .= " AND (" . implode(" OR ", $implode) . ")";

		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND CONCAT(c.`firstname`, ' ', c.`lastname`) LIKE '" . $this->db->escape('%' . (string)$data['filter_name'] . '%') . "'";
		}

		if (!empty($data['filter_email'])) {
			$sql .= " AND c.`email` LIKE '" . $this->db->escape((string)$data['filter_email'] . '%') . "'";
		}

		if (isset($data['filter_newsletter']) && $data['filter_newsletter'] !== '') {
			$sql .= " AND c.`newsletter` = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$sql .= " AND c.`customer_group_id` = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
			$sql .= " AND c.`customer_id` IN (SELECT `customer_id` FROM `" . DB_PREFIX . "customer_ip` WHERE `ip` = '" . $this->db->escape((string)$data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND c.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(c.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(c.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}

		$sort_data = [
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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

	public function getTotalCustomers(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer` c";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(c.`firstname`, ' ', c.`lastname`) LIKE '" . $this->db->escape('%' . (string)$data['filter_name'] . '%') . "'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "c.`email` LIKE '" . $this->db->escape((string)$data['filter_email'] . '%') . "'";
		}

		if (isset($data['filter_newsletter']) && $data['filter_newsletter'] !== '') {
			$implode[] = "c.`newsletter` = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "c.`customer_group_id` = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
			$implode[] = "c.`customer_id` IN (SELECT `customer_id` FROM " . DB_PREFIX . "customer_ip WHERE `ip` = '" . $this->db->escape((string)$data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "c.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$implode[] = "DATE(c.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$implode[] = "DATE(c.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getAddress(int $address_id): array {
		$address_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "address` WHERE `address_id` = '" . (int)$address_id . "'");

		if ($address_query->num_rows) {
			$country_query = $this->db->query("SELECT *, c.name FROM `" . DB_PREFIX . "country` c LEFT JOIN `" . DB_PREFIX . "address_format` af ON (c.`address_format_id` = af.`address_format_id`) WHERE `country_id` = '" . (int)$address_query->row['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$address_query->row['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			return [
				'address_id'     => $address_query->row['address_id'],
				'customer_id'    => $address_query->row['customer_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'custom_field'   => json_decode($address_query->row['custom_field'], true),
				'default'        => $address_query->row['default']
			];
		}

		return [];
	}

	public function getAddresses(int $customer_id): array {
		$address_data = [];

		$query = $this->db->query("SELECT `address_id` FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");

		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);

			if ($address_info) {
				$address_data[] = $address_info;
			}
		}

		return $address_data;
	}

	public function getTotalAddressesByCustomerId(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalAddressesByCountryId(int $country_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address` WHERE `country_id` = '" . (int)$country_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalAddressesByZoneId(int $zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address` WHERE `zone_id` = '" . (int)$zone_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalCustomersByCustomerGroupId(int $customer_group_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer` WHERE `customer_group_id` = '" . (int)$customer_group_id . "'");

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function addHistory(int $customer_id, string $comment): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_history` SET `customer_id` = '" . (int)$customer_id . "', `comment` = '" . $this->db->escape(strip_tags($comment)) . "', `date_added` = NOW()");
	}

	public function getHistories(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `comment`, `date_added` FROM `" . DB_PREFIX . "customer_history` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalHistories(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_history` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	public function addTransaction(int $customer_id, string $description = '', float $amount = 0, int $order_id = 0): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_transaction` SET `customer_id` = '" . (int)$customer_id . "', `order_id` = '" . (int)$order_id . "', `description` = '" . $this->db->escape($description) . "', `amount` = '" . (float)$amount . "', `date_added` = NOW()");
	}

	public function deleteTransactionByOrderId(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_transaction` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	public function getTransactions(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalTransactions(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total`  FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	public function getTransactionTotal(int $customer_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (float)$query->row['total'];
	}

	public function getTotalTransactionsByOrderId(int $order_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_transaction` WHERE `order_id` = '" . (int)$order_id . "'");

		return (int)$query->row['total'];
	}

	public function addReward(int $customer_id, string $description = '', int $points = 0, int $order_id = 0): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_reward` SET `customer_id` = '" . (int)$customer_id . "', `order_id` = '" . (int)$order_id . "', `points` = '" . (int)$points . "', `description` = '" . $this->db->escape($description) . "', `date_added` = NOW()");
	}

	public function deleteReward(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_reward` WHERE `order_id` = '" . (int)$order_id . "' AND `points` > '0'");
	}

	public function getRewards(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalRewards(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	public function getRewardTotal(int $customer_id): int {
		$query = $this->db->query("SELECT SUM(points) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalRewardsByOrderId(int $order_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `order_id` = '" . (int)$order_id . "' AND `points` > '0'");

		return (int)$query->row['total'];
	}

	public function getIps(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}
		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalIps(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_ip` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalCustomersByIp(string $ip): int {
		$query = $this->db->query("SELECT COUNT(DISTINCT `customer_id`) AS `total` FROM `" . DB_PREFIX . "customer_ip` WHERE `ip` = '" . $this->db->escape($ip) . "'");

		return (int)$query->row['total'];
	}

	public function getTotalLoginAttempts(string $email): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE `email` = '" . $this->db->escape(oc_strtolower($email)) . "'");

		return $query->row;
	}

	public function deleteLoginAttempts(string $email): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE `email` = '" . $this->db->escape(oc_strtolower($email)) . "'");
	}

	public function updateNewAddress($address_id, $data): void{
		$this->db->query("UPDATE `" . DB_PREFIX . "address` SET 
		`firstname` = '" . $this->db->escape((string)$data['firstname']) . "', 
		`lastname` = '" . $this->db->escape((string)$data['lastname']) . "', 
		`company` = '" . $this->db->escape((string)$data['company']) . "', 
		`address_1` = '" . $this->db->escape((string)$data['address_1']) . "', 
		`address_2` = '" . $this->db->escape((string)$data['address_2']) . "', 
		`postcode` = '" . $this->db->escape((string)$data['postcode']) . "', 
		`city` = '" . $this->db->escape((string)$data['city']) . "', 
		`zone_id` = '" . $data['zone_id'] . "',
		`country_id` = '" . $data['country_id'] . "' 
		 WHERE `address_id` = '" . (int)$address_id . "'");
	}
	public function import(){
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_pw3` ORDER BY `customer_id`");
		$i = 0; 
		foreach($query->rows as $row){
			if($this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET 
			`customer_id` = '" . $row['customer_id'] . "',
			`store_id` = '" . $row['store_id'] . "',
			`customer_group_id` = '" . $row['customer_group_id'] . "', 
			`language_id` = '2', 
			`firstname` = '" . $row['firstname'] . "', 
			`lastname` = '" . $row['lastname']. "', 
			`email` = '" . $row['email'] . "', 
			`telephone` = '" . $row['telephone'] . "', 
			`custom_field` = '" . $row['custom_field'] . "', 
			`newsletter` = '" . $row['newsletter'] . "', 
			`ip` = '" . $row['ip'] . "', 
			`password` = '" . $row['password'] . "', 
			`status` = '" . $row['status'] . "', 
			`safe` = '" . $row['safe'] . "',
			`token` = '" . $row['token'] . "',
			`code` = '" . $row['code'] . "', 
			`date_added` = '" . $row['date_added'] . "'")){
				$i++;
			}

			$query_activity = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_activity_pw3` WHERE `customer_id` = '".$row['customer_id']."'");
			if(count($query_activity->rows)>0){
				foreach($query_activity->rows as $row1){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_activity` SET 
					`customer_activity_id` = '" . $row1['customer_activity_id'] . "',
					`customer_id` = '" . $row1['customer_id'] . "',
					`key` = '" . $row1['key'] . "', 
					`data` = '" . $row1['data'] . "', 
					`ip` = '" . $row1['ip']. "', 
					`date_added` = '" . $row1['date_added'] . "'");
				}
			}
			$query_affiliate = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_affiliate_pw3` WHERE `customer_id` = '".$row['customer_id']."'");
			if(count($query_affiliate->rows)>0){
				foreach($query_affiliate->rows as $row2){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_affiliate` SET 
					`customer_id` = '" . $row2['customer_id'] . "',
					`company` = '" . $row2['company'] . "',
					`website` = '" . $row2['website'] . "', 
					`tracking` = '" . $row2['tracking'] . "', 
					`commission` = '" . $row2['commission']. "', 
					`tax` = '" . $row2['tax']. "', 
					`payment` = '" . $row2['payment']. "', 
					`cheque` = '" . $row2['cheque']. "', 
					`paypal` = '" . $row2['paypal']. "', 
					`bank_name` = '" . $row2['bank_name']. "', 
					`bank_branch_number` = '" . $row2['bank_branch_number']. "', 
					`bank_swift_code` = '" . $row2['bank_swift_code']. "', 
					`bank_account_name` = '" . $row2['bank_account_name']. "', 
					`bank_account_number` = '" . $row2['bank_account_number']. "', 
					`custom_field` = '" . $row2['custom_field']. "', 
					`status` = '" . $row2['status']. "', 
					`date_added` = '" . $row2['date_added'] . "'");
				}
			}
			$query_login = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login_pw3` WHERE `email` = '".$row['email']."'");
			if(count($query_login->rows)>0){
				foreach($query_login->rows as $row3){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_login` SET 
					`customer_login_id` = '" . $row3['customer_login_id'] . "',
					`email` = '" . $row3['email'] . "',
					`ip` = '" . $row3['ip'] . "', 
					`total` = '" . $row3['total'] . "',  
					`date_modified` = '" . $row3['date_modified']. "', 
					`date_added` = '" . $row3['date_added'] . "'");
				}
			}
			$query_online = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_online_pw3` WHERE `customer_id` = '".$row['customer_id']."'");
			if(count($query_online->rows)>0){
				foreach($query_online->rows as $row4){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_online` SET 
					`ip` = '" . $row4['ip'] . "',
					`customer_id` = '" . $row4['customer_id'] . "',
					`url` = '" . str_replace('profilewear.se','pw4.profilewear.se',$row4['url']) . "', 
					`referer` = '" . str_replace('profilewear.se','pw4.profilewear.se',$row4['referer']) . "',  
					`date_added` = '" . $row4['date_added'] . "'");
				}
			}
			$query_ip = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip_pw3` WHERE `customer_id` = '".$row['customer_id']."'");
			if(count($query_ip->rows)>0){
				foreach($query_ip->rows as $row5){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_ip` SET 
					`customer_ip_id` = '" . $row5['customer_ip_id'] . "',
					`customer_id` = '" . $row5['customer_id'] . "',
					`ip` = '" . $row5['ip'] . "', 
					`date_added` = '" . $row5['date_added'] . "'");
				}
			}
			$query_wish = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_wishlist_pw3` WHERE `customer_id` = '".$row['customer_id']."'");
			if(count($query_wish->rows)>0){
				foreach($query_wish->rows as $row6){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_wishlist` SET 
					`customer_id` = '" . $row6['customer_id'] . "',
					`product_id` = '" . $row6['product_id'] . "', 
					`date_added` = '" . $row6['date_added'] . "'");
				}
			}
			$query_address = $this->db->query("SELECT * FROM `" . DB_PREFIX . "address_pw3` WHERE `customer_id` = '".$row['customer_id']."'");
			if(count($query_address->rows)>0){
				foreach($query_address->rows as $row7){
					$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET 
					`address_id` = '" . $row7['address_id'] . "',
					`customer_id` = '" . $row7['customer_id'] . "',
					`firstname` = '" . $row7['firstname'] . "',
					`lastname` = '" . $row7['lastname'] . "',
					`company` = '" . $row7['company'] . "',
					`address_1` = '" . $row7['address_1'] . "',
					`address_2` = '" . $row7['address_2'] . "',
					`city` = '" . $row7['city'] . "',
					`postcode` = '" . $row7['postcode'] . "',
					`country_id` = '" . $row7['country_id'] . "',
					`zone_id` = '" . $row7['zone_id'] . "', 
					`custom_field` = '" . $row7['custom_field'] . "'");
				}
			}
		}
		// echo $i; die;
		// if($i == count($query->rows)){
		// 	echo "done";
		// }
	}
}
