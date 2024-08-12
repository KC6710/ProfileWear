<?php
namespace Opencart\Admin\Model\Catalog;
class Manufacturer extends \Opencart\System\Engine\Model {
	public function addManufacturer(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `sort_order` = '" . (int)$data['sort_order'] . "', `manufacturerDescription = ` '". $this->db->escape((string)$data['description']). "'");

		$manufacturer_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['background_image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer_description` SET `background_image` = '" . $this->db->escape((string)$data['background_image']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['color'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer_description` SET `color` = '" . $this->db->escape((string)$data['color']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['meta_description'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer_description` SET `meta_description` = '" . $this->db->escape((string)$data['meta_description']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['meta_keyword'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer_description` SET `meta_keyword` = '" . $this->db->escape((string)$data['meta_keyword']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['browser_title'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer_description` SET `custom_title` = '" . $this->db->escape((string)$data['custom_title']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` SET `manufacturer_id` = '" . (int)$manufacturer_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'manufacturer_id', `value` = '" . (int)$manufacturer_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		if (isset($data['manufacturer_layout'])) {
			foreach ($data['manufacturer_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_to_layout` SET `manufacturer_id` = '" . (int)$manufacturer_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('manufacturer');

		return $manufacturer_id;
	}

	public function editManufacturer(int $manufacturer_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `sort_order` = '" . (int)$data['sort_order'] . "', `manufacturerDescription` =  '". $this->db->escape($data['description'])."' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_description` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_description` SET 
		`description` = '" . $this->db->escape($data['description']) . "', 
		`manufacturer_id` = '" . (int)$manufacturer_id . "',  
		`background_image` = '" . $this->db->escape((string)$data['background_image']) . "',
		`color` = '" . $this->db->escape((string)$data['color']) . "',
		`meta_description` = '" . $this->db->escape((string)$data['meta_description']) . "',
		`meta_keyword` = '" . $this->db->escape((string)$data['meta_keyword']) . "',
		`custom_title` = '" . $this->db->escape((string)$data['browser_title']) . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` SET `manufacturer_id` = '" . (int)$manufacturer_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'manufacturer_id', `value` = '" . (int)$manufacturer_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_layout` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_layout'])) {
			foreach ($data['manufacturer_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_to_layout` SET `manufacturer_id` = '" . (int)$manufacturer_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('manufacturer');
	}

	

	public function deleteManufacturer(int $manufacturer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_layout` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = '" . (int)$manufacturer_id . "'");

		$this->cache->delete('manufacturer');
	}

	public function getManufacturer(int $manufacturer_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "manufacturer` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		return $query->row;
	}

	public function getManufacturerDescripton(int $manufacturer_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "manufacturer_description` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		return $query->row;
	}

	public function getManufacturers(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "manufacturer`";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE `name` LIKE '" . $this->db->escape((string)$data['filter_name'] . '%') . "'";
		}

		$sort_data = [
			'name',
			'sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `name`";
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

	public function getStores(int $manufacturer_id): array {
		$manufacturer_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}

	public function getSeoUrls(int $manufacturer_id): array {
		$manufacturer_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $manufacturer_seo_url_data;
	}

	public function getLayouts(int $manufacturer_id): array {
		$manufacturer_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer_to_layout` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $manufacturer_layout_data;
	}

	public function getTotalManufacturers(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "manufacturer`");

		return (int)$query->row['total'];
	}

	public function getTotalManufacturersByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "manufacturer_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}
	// public function import(){
	// 	// $query = $this->db->query("SELECT * FROM `". DB_PREFIX ."manufacturer_pw3`");
	// 	// $i=0;
	// 	// foreach($query->rows as $row){
	// 	// 	$this->db->query('INSERT INTO `'. DB_PREFIX .'manufacturer` SET `manufacturer_id`="'.$row['manufacturer_id'].'", `name`="'.$row['name'].'", `image`= "'.$row['image'].'", `sort_order`="'.$row['sort_order'].'", `manufacturerDescription`="'.$row['manufacturerDescription'].'"' );
	// 	// 	$i++;
	// 	// }

	// 	// $query_desc = $this->db->query("SELECT * FROM `". DB_PREFIX ."manufacturer_description_pw3`");
	// 	// $i=0;
	// 	// foreach($query_desc->rows as $row){
	// 	// 	$this->db->query('INSERT INTO `'. DB_PREFIX .'manufacturer_description` SET `manufacturer_id`="'.$row['manufacturer_id'].'", `language_id`="'.$row['language_id'].'", `description`="'.$row['description'].'", `meta_description`= "'.$row['meta_description'].'", `meta_keyword`="'.$row['meta_keyword'].'", `custom_title`="'.$row['custom_title'].'"' );
	// 	// 	$i++;
	// 	// }
	// 	$query_store = $this->db->query("SELECT * FROM `". DB_PREFIX ."manufacturer_to_store_pw3`");
	// 	$i=0;
	// 	foreach($query_store->rows as $row){
	// 		$this->db->query('INSERT INTO `'. DB_PREFIX .'manufacturer_to_store` SET `manufacturer_id`="'.$row['manufacturer_id'].'", `store_id`="'.$row['store_id'].'"' );
	// 		$i++;
	// 	}
	// 	if($i == count($query_store->rows)){
	// 		echo "done"; die;
	// 	}
	// }
}
