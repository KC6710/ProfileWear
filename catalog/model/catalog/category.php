<?php
namespace Opencart\Catalog\Model\Catalog;
class Category extends \Opencart\System\Engine\Model {
	public function getCategory(int $category_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.`category_id` = c2s.`category_id`) WHERE c.`category_id` = '" . (int)$category_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND c.`status` = '1'");

		return $query->row;
	}

	public function getCategories(int $parent_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.`category_id` = c2s.`category_id`) WHERE c.`parent_id` = '" . (int)$parent_id . "' AND cd.`language_id` = '2' AND c2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "'  AND c.`status` = '1' ORDER BY c.`sort_order`, LCASE(cd.`name`)");

		return $query->rows;
	}

	public function getFilters(int $category_id): array {
		$implode = [];

		$query = $this->db->query("SELECT `filter_id` FROM `" . DB_PREFIX . "category_filter` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$implode[] = (int)$result['filter_id'];
		}

		$filter_group_data = [];

		if ($implode) {
			$filter_group_query = $this->db->query("SELECT DISTINCT f.`filter_group_id`, fgd.`name`, fg.`sort_order` FROM `" . DB_PREFIX . "filter` f LEFT JOIN `" . DB_PREFIX . "filter_group` fg ON (f.`filter_group_id` = fg.`filter_group_id`) LEFT JOIN `" . DB_PREFIX . "filter_group_description` fgd ON (fg.`filter_group_id` = fgd.`filter_group_id`) WHERE f.`filter_id` IN (" . implode(',', $implode) . ") AND fgd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY f.`filter_group_id` ORDER BY fg.`sort_order`, LCASE(fgd.`name`)");

			foreach ($filter_group_query->rows as $filter_group) {
				$filter_data = [];

				$filter_query = $this->db->query("SELECT DISTINCT f.`filter_id`, fd.`name` FROM `" . DB_PREFIX . "filter` f LEFT JOIN `" . DB_PREFIX . "filter_description` fd ON (f.`filter_id` = fd.`filter_id`) WHERE f.`filter_id` IN (" . implode(',', $implode) . ") AND f.`filter_group_id` = '" . (int)$filter_group['filter_group_id'] . "' AND fd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY f.`sort_order`, LCASE(fd.`name`)");

				foreach ($filter_query->rows as $filter) {
					$filter_data[] = [
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['name']
					];
				}

				if ($filter_data) {
					$filter_group_data[] = [
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'filter'          => $filter_data
					];
				}
			}
		}

		return $filter_group_data;
	}

	public function getLayoutId($category_id): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_to_layout` WHERE `category_id` = '" . (int)$category_id . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getCategoriesByFilter($filter_data) {
		$sql = "SELECT c.category_id, cd.name, c.image, (SELECT COUNT(*) FROM `" . DB_PREFIX . "product_to_category` p2c WHERE p2c.category_id = c.category_id) as product_count FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id) WHERE cd.name LIKE '%" . $this->db->escape($filter_data['filter_name']) . "%' AND c.status = 1";
		
		$query = $this->db->query($sql);
	
		$categories = array();
		foreach ($query->rows as $result) {
			
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $result['image'];
			} else {
				$image = 'image/placeholder.png'; // Path to your placeholder image
			}
			$categories[] = array(
				'category_id' => $result['category_id'],
				'name'         => $result['name'],
				'image'        => $this->model_tool_image->resize($result['image'], 80, 80), // Thumbnail
				'product_count'=> $result['product_count']
			);
		}
	
		return $categories;
	}

	public function getAllCategories(array $data = []): array {
		$sql = "SELECT cp.`category_id` AS `category_id`,cp.`path_id` as path_id, GROUP_CONCAT(cd1.`name` ORDER BY cp.`level` SEPARATOR ' > ') AS `name`, c1.`parent_id`, c1.`sort_order` FROM `" . DB_PREFIX . "category_path` cp LEFT JOIN `" . DB_PREFIX . "category` c1 ON (cp.`category_id` = c1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category` c2 ON (cp.`path_id` = c2.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` cd1 ON (cp.`path_id` = cd1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` cd2 ON (cp.`category_id` = cd2.`category_id`) WHERE cd1.`language_id` = '2' AND cd2.`language_id` = '2'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.`name` LIKE '" . $this->db->escape((string)$data['filter_name']) . "'";
		}

		$sql .= " GROUP BY cp.`category_id`";

		$sort_data = [
			'name',
			'sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `sort_order`";
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
		}
		// echo $sql; die;
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getPath($category_id): string {
		return implode('_', array_column($this->getPaths($category_id), 'path_id'));
	}

	public function getPaths($category_id): array {
		$query = $this->db->query("SELECT `category_id`, `path_id`, `level` FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category_id . "' ORDER BY `level` ASC");

		return $query->rows;
	}
	public function getSeoUrls($category_id): array {
		$category_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'path' AND `value` = '" . $this->db->escape($this->getPath($category_id)) . "'");

		foreach ($query->rows as $result) {
			$category_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $category_seo_url_data;
	}
	
	  
}
