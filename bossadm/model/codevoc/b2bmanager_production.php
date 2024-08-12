<?php
namespace Opencart\Admin\Model\Codevoc;
class B2bmanagerProduction extends \Opencart\System\Engine\Model {
	public function addProduction($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production SET name = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', order_id = '" . (int)$data['order_id'] . "', order_date = '" .  $this->db->escape($data['order_date']) . "',delivery_date = '" .  $this->db->escape($data['delivery_date']) . "',production_date = '" .  $this->db->escape($data['production_date']) . "',item_arrival = '" . $this->db->escape($data['item_arrival']). "',print_arrival = '" .$this->db->escape($data['print_arrival']) . "',file = '" .  $this->db->escape($data['file']) . "',attention = '" .  $this->db->escape($data['attention']) . "',duration = '" .  (float)$data['duration'] . "',status = '" .$this->db->escape($data['status']) . "', created_at = NOW(), updated_at = NOW()");

		$production_id = $this->db->getLastId();

		// Create entries in suppliers table
		if($data['suppliers']) {
			foreach($data['suppliers'] as $supplier) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production_to_suppliers SET production_id = '" . (int)$production_id . "', `supplier_id` = '" . (int)$supplier ."'");
			}
		}

		// Create methods entries
		if($data['methods']) {
			foreach($data['methods'] as $method) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production_to_methods SET production_id = '" . (int)$production_id . "', `method_id` = '" . (int)$method ."'");
			}
		}

		return $production_id;
	}

	public function editProduction($production_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "codevoc_production SET name = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', order_id = '" . (int)$data['order_id'] . "', order_date = '" .  $this->db->escape($data['order_date']) . "',delivery_date = '" .  $this->db->escape($data['delivery_date']) . "',production_date = '" .  $this->db->escape($data['production_date']) . "',item_arrival = '" . $this->db->escape($data['item_arrival']). "',print_arrival = '" .$this->db->escape($data['print_arrival']) . "',file = '" .  $this->db->escape($data['file']) . "',attention = '" .  $this->db->escape($data['attention']) . "',duration = '" .  (float)$data['duration'] . "',status = '" .$this->db->escape($data['status']) . "',updated_at = NOW() WHERE production_id = '" . (int)$production_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_production_to_suppliers WHERE production_id = '" . (int)$production_id . "'");
		if($data['suppliers']) {
			foreach($data['suppliers'] as $supplier) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production_to_suppliers SET production_id = '" . (int)$production_id . "', `supplier_id` = '" . (int)$supplier ."'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_production_to_methods WHERE production_id = '" . (int)$production_id . "'");
		if($data['methods']) {
			foreach($data['methods'] as $method) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production_to_methods SET production_id = '" . (int)$production_id . "', `method_id` = '" . (int)$method ."'");
			}
		}

	}

	public function deleteProduction($production_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_production WHERE production_id = '" . (int)$production_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_production_to_suppliers WHERE production_id = '" . (int)$production_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_production_to_methods WHERE production_id = '" . (int)$production_id . "'");
	}

	public function getProduction($production_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production  WHERE production_id = '" . (int)$production_id . "'");

		$data = $query->row;

		$suppliers_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_to_suppliers  WHERE production_id = '" . (int)$production_id . "'");
		$suppliers_result = [];
		foreach($suppliers_query->rows as $supplier) {
			$suppliers_result[] = $supplier['supplier_id'];
		}
		$data['suppliers'] = $suppliers_result;

		$methods_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_to_methods  WHERE production_id = '" . (int)$production_id . "'");
		$methods_result = [];
		foreach($methods_query->rows as $method) {
			$methods_result[] = $method['method_id'];
		}
		$data['methods'] = $methods_result;

		return $data;
	}

	public function getProductions($data = array()) {
		$sql = "SELECT * ,(
			select GROUP_CONCAT(name) from ". DB_PREFIX."codevoc_production_to_suppliers pts
			join ". DB_PREFIX ."codevoc_production_supplier ps on ps.supplier_id = pts.supplier_id
			where production_id = p.production_id
			) as suppliers,(
				select GROUP_CONCAT(name) from ". DB_PREFIX."codevoc_production_to_methods ptm
				join ". DB_PREFIX ."codevoc_production_method pm on pm.method_id = ptm.method_id
				where production_id = p.production_id
			) as methods FROM " . DB_PREFIX . "codevoc_production p ";

			if (isset($data['filter_status']) && $data['filter_status'] != 'All') {
				$sql .= " WHERE p.status = '". $data['filter_status'] ."'";
			}

		$sort_data = array(
			'name',
			'order_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY order_id";
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

	public function getTotalProductions($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "codevoc_production ";
		if (isset($data['filter_status']) && $data['filter_status'] != 'All') {
			$sql .= " WHERE status = '". $data['filter_status'] ."'";
		}

	$sort_data = array(
		'name',
		'order_id'
	);

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getProductionMethods() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_method order by name ASC");

		return $query->rows;
	}

	public function getProductionMethodName($method_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_method where method_id='".(int)$method_id."'");
		if($query->num_rows>0)
		{
			return $query->row['name'];
		}
		else
		{
		return '';
		}
	}

	public function getProductionSuppliers() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_supplier order by name ASC");

		return $query->rows;
	}

	public function getProductionSupplierName($supplier_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_supplier where supplier_id='".(int)$supplier_id."'");
		if($query->num_rows>0)
		{
			return $query->row['name'];
		}
		else
		{
		return '';
		}
	}

	public function import(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_pw3 ORDER BY `production_id` LIMIT 10");
		foreach($query->rows as $row){
			// $query_prod = $this->db->query("INSERT INTO "  . DB_PREFIX . "codevoc_production SET 
			// `production_id` = '" . $this->db->escape($row['production_id']) . "',
			// `name` = '" . $this->db->escape($row['name']) . "',
			// `description` = '" . $this->db->escape($row['description']) . "', 
			// `order_id` = '" . (int)$row['order_id'] . "', 
			// `order_date` = '" .  $this->db->escape($row['order_date']) . "', 
			// `delivery_date` = '" .  $this->db->escape($row['delivery_date']) . "', 
			// `production_date` = '" .  $row['production_date'] . "', 
			// `item_arrival` = '" . $this->db->escape($row['item_arrival']). "', 
			// `print_arrival` = '" .$this->db->escape($row['print_arrival']) . "', 
			// `file` = '" .  $row['file'] . "', 
			// `attention` = '" .  $row['attention'] . "', 
			// `duration` = '" .  (float)$row['duration'] . "', 
			// `status` = '" .$this->db->escape($row['status']) . "', 
			// `created_at` = '" .$row['created_at'] . "', 
			// `updated_at` = '" .$row['updated_at'] . "'");
			$query_get_pw3_prod_time = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_time_pw3 WHERE `production_id` ='".$row['production_id']."'");
			if(count($query_get_pw3_prod_time->rows) > 0){
				foreach($query_get_pw3_prod_time as $row1){
					$insert_time = $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production_time SET 
					`production_time_id` = '".$row1['production_time_id']."', 
					`production_id` = '".$row1['production_id']."', 
					`duration` = '".$row1['duration']."', 
					`assignuser` = '".$row1['assignuser']."', 
					`created_at` = '".$row1['created_at']."', 
					`updated_at` = '".$row1['updated_at']."'" );
				}
			}

			$query_get_pw3_prod_to_methods = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_to_methods_pw3 WHERE `production_id` ='".$row['production_id']."'");
			foreach($query_get_pw3_prod_to_methods->rows as $row2){
				$insert_prod_to_methods = $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production_to_methods SET 
				`production_id` = '".$row2['production_id']."', 
                `method_id` = '".$row2['method_id']."'
				");
			}
			$query_get_pw3_prod_to_supp = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production_to_suppliers_pw3 WHERE `production_id` ='".$row['production_id']."'");
			foreach($query_get_pw3_prod_to_supp->rows as $row3){
				$insert_prod_to_methods = $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_production_to_suppliers SET 
				`production_id` = '".$row3['production_id']."', 
                `supplier_id` = '".$row3['supplier_id']."'
				");
			}
		}
	}


}