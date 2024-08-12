<?php
namespace Opencart\Admin\Model\Codevoc;
class B2bmanagerInventoryprint extends \Opencart\System\Engine\Model {
	public function api_get_items(){

		// Retrive all database items
    $items = $this->db->query("select *  from " . DB_PREFIX . "codevoc_inventory_print order by id desc");
    $items = $items->rows;

    return $items;
	}

	public function api_get_item($id){

		// Retrive all database items
    $item = $this->db->query("select *  from " . DB_PREFIX . "codevoc_inventory_print WHERE id = '" . (int)$id . "' order by id desc");
    $item = $item->row;

    return $item;
	}

	public function api_delete_item($id){
		if($id){
			$this->db->query("DELETE FROM " . DB_PREFIX . "codevoc_inventory_print WHERE id = '" . (int)$id . "'");
		}
	}

	public function api_copy_item($id){
		if($id){
			$this->db->query("INSERT INTO ". DB_PREFIX ."codevoc_inventory_print (number,client,name,color,type,category,quantity,location,comments,last_update_by,created_at,updated_at) SELECT number,client,name,color,type,category,quantity,location,comments,last_update_by,created_at,updated_at FROM ". DB_PREFIX ."codevoc_inventory_print WHERE id = '".intval($id)."'");
		}
	}

	public function api_create_item($data){
		$query = "INSERT INTO ". DB_PREFIX ."codevoc_inventory_print set 
		number='".$this->db->escape($data['number'])."' , 
		client='".$this->db->escape($data['client'])."' , 
		name='".$this->db->escape($data['name'])."', 
		color='".$this->db->escape($data['color'])."',
		type='".$this->db->escape($data['type'])."',
		category='".$this->db->escape($data['category'])."',
		quantity='".$this->db->escape($data['quantity'])."',
		location='".$this->db->escape($data['location'])."',
		comments='".$this->db->escape($data['comments'])."',
		last_update_by='".$data['last_update_by']."'";
		$this->db->query($query);
	  }

  public function api_update_article($id,$data,$last_update_by){
    $this->db->query("update  " . DB_PREFIX . "codevoc_inventory_print SET  
		number='".$this->db->escape($data['number'])."',  
		client='".$this->db->escape($data['client'])."',
		name='".$this->db->escape($data['name'])."',
		color='".$this->db->escape($data['color'])."',
		type='".$this->db->escape($data['type'])."',
		category='".$this->db->escape($data['category'])."',
		quantity='".intval($data['quantity'])."',
		location='".$this->db->escape($data['location'])."',
		comments='".$this->db->escape($data['comments'])."',
		last_update_by='".$this->db->escape($last_update_by)."' 
		where id='".$data['id']."'");
  }


	public function api_deduct_article_quantity($id,$quantity){
		$query = "update  " . DB_PREFIX . "codevoc_inventory_print SET  quantity = quantity - ".$quantity." where id='".$id."'";
		$this->db->query($query);
	}

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
}