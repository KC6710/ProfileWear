<?php
namespace Opencart\Admin\Model\Codevoc;
class B2bmanagerPurchase extends \Opencart\System\Engine\Model {

  public function generatePurhcase($user_id, $orders) {
    // Create entry in Purchase
    $name = "Inköp: ". implode(',', $orders);
    $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_purchase SET name = '" . $this->db->escape($name) ."', purchased_by='". (int)$user_id ."'");
    $purchase_id = $this->db->getLastId();

    $this->addOrdersToPurchase($purchase_id, $orders);

    return $purchase_id;
  }

  public function checkOrderExistInPurchase($purchase_id, $order_id) {
      // Check order is already added to purchase
      $purchase_order_query = $this->db->query("SELECT * FROM ". DB_PREFIX ."codevoc_purchase_order where purchase_id = '". (int)$purchase_id ."' and order_id = '". (int)$order_id ."'");
      $purchase_order = $purchase_order_query->row;

      // If order found then we will not add it because we already added order to purchase
      if($purchase_order)
        return true;

      return false;
  }

  public function addOrdersToPurchase($purchase_id, $orders) {
    // Create entries in purchase order table
    foreach($orders as $order_id) {
      $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_purchase_order SET purchase_id = '" . (int)$purchase_id ."', order_id = '". (int)$order_id ."'");
      $purchase_order_id = $this->db->getLastId();

      // Create entries in purchase product table
      $order_products_query = $this->db->query("SELECT * FROM ". DB_PREFIX ."codevoc_b2b_order_product where order_id = '". (int)$order_id ."'");
      $order_products = $order_products_query->rows;

      foreach($order_products as $order_product) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_purchase_product SET purchase_order_id = '" . (int)$purchase_order_id ."', purchase_id = '". (int)$purchase_id ."', product_id = '". (int)$order_product['product_id'] ."', name = '". $this->db->escape($order_product['name']) ."', model = '". $this->db->escape($order_product['model']) ."', quantity = '". (int)$order_product['quantity'] ."'");
        $purchase_product_id = $this->db->getLastId();

        // Create entries in purchase product option table
        $order_product_options_query = $this->db->query("SELECT * FROM ". DB_PREFIX ."order_option where order_product_id = '". (int)$order_product['order_product_id'] ."'");
        $order_product_options = $order_product_options_query->rows;

        foreach($order_product_options as $order_product_option) {
          $this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_purchase_product_option SET purchase_id = '" . (int)$purchase_id ."', purchase_product_id = '". (int)$purchase_product_id ."', product_option_id = '". (int)$order_product_option['product_option_id'] ."', product_option_value_id = '". (int)$order_product_option['product_option_value_id'] ."', name = '". $this->db->escape($order_product_option['name']) ."', value = '". $this->db->escape($order_product_option['value']) ."'");
        }
      }
    }
  }

  public function getSuppliers() {
    $sql = "SELECT * FROM ". DB_PREFIX . "codevoc_purchase_supplier";
    $query = $this->db->query($sql);
    return $query->rows;
  }

  public function getAllSuppliers() {
    $sql = "(select name from `".DB_PREFIX."codevoc_purchase_supplier` )
      union
    (select name from `".DB_PREFIX."codevoc_production_supplier`)
    order by name";
    $query = $this->db->query($sql);
    return $query->rows;
  }

  public function getReceivers() {
    $sql = "SELECT * FROM ". DB_PREFIX . "codevoc_purchase_receiver";
    $query = $this->db->query($sql);
    return $query->rows;
  }

  public function getPurchases($data) {
    $sql = "SELECT cp.*, cpr.name as purchase_receiver_name, CONCAT_WS(' ', u.firstname, u.lastname) as purchased_by_name ,( SELECT COUNT(*) FROM ".DB_PREFIX."codevoc_purchase_order cpo WHERE  cp.purchase_id = cpo.purchase_id) as total_orders FROM ". DB_PREFIX ."codevoc_purchase cp LEFT JOIN `". DB_PREFIX ."codevoc_purchase_receiver` cpr ON cpr.purchase_receiver_id = cp.purchase_receiver_id LEFT JOIN `".DB_PREFIX ."user` u ON u.user_id = cp.purchased_by ORDER BY cp.purchase_id DESC";
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

  public function getTotalPurchases($data) {
    $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "codevoc_purchase` ";

    $query = $this->db->query($sql);

		return $query->row['total'];
  }

  public function getPurchaseSuppliersName($purchase_id) {
    $query = $this->db->query("SELECT GROUP_CONCAT(DISTINCT cps.name) as supplier_name FROM `".DB_PREFIX."codevoc_purchase_product` cpp LEFT JOIN `". DB_PREFIX ."codevoc_purchase_supplier` cps ON cps.purchase_supplier_id = cpp.purchase_supplier_id WHERE cpp.purchase_order_id IN (SELECT purchase_order_id FROM `". DB_PREFIX ."codevoc_purchase_order` WHERE purchase_id = '". (int)$purchase_id ."')");

    return $query->row['supplier_name'];
  }

  public function getPurchase($purchase_id) {
    $sql = "SELECT cp.*, pr.name as purchase_receiver_name FROM ". DB_PREFIX ."codevoc_purchase cp LEFT JOIN `". DB_PREFIX ."codevoc_purchase_receiver` pr ON pr.purchase_receiver_id=cp.purchase_receiver_id WHERE purchase_id = ". $purchase_id;
    $purchase_query = $this->db->query($sql);
    if($purchase_query->row) {
      return [
        'purchase_id' => $purchase_query->row['purchase_id'],
        'purchase_receiver_id' => $purchase_query->row['purchase_receiver_id'],
        'purchase_receiver_name' => $purchase_query->row['purchase_receiver_name'],
        'purchased_by' => $purchase_query->row['purchased_by'],
        'purchase_finished' => (int)$purchase_query->row['purchase_finished'],
        'name'     => $purchase_query->row['name'],
        'orders' => $this->getPurchaseOrder($purchase_query->row['purchase_id']),
        'created_at'     => $purchase_query->row['created_at'],
        'updated_at'     => $purchase_query->row['updated_at'],
      ];
    }


  }

  public function getPurchaseOrderForListing($purchase_id) {
    // get purchase orders
    $sql = "SELECT cpo.*, CONCAT(o.firstname, ' ', o.lastname) full_name, o.firstname, o.lastname, o.email, o.order_type, o.quotation_id FROM ". DB_PREFIX ."codevoc_purchase_order cpo JOIN `". DB_PREFIX ."order` o ON o.order_id = cpo.order_id WHERE purchase_id = ". $purchase_id;
    $purchase_order_query = $this->db->query($sql);

    $orders = [];
    foreach($purchase_order_query->rows as $purchase_order) {
      $orders[] = [
        'purchase_order_id' => $purchase_order['purchase_order_id'],
        'purchase_id' => $purchase_order['purchase_id'],
        'order_id' => $purchase_order['order_id'],
        'comment' => $purchase_order['comment'],
        'purchase_cost' => $purchase_order['purchase_cost'],
        'full_name' => $purchase_order['full_name'],
        'firstname' => $purchase_order['firstname'],
        'lastname' => $purchase_order['lastname'],
        'email' => $purchase_order['email'],
        'quotation_id' => $purchase_order['quotation_id'],
        'products' => $this->getPurchaseOrderProductsWithoutOptions($purchase_order['purchase_order_id']),
        'link' => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $purchase_order['order_id'], true),
      ];
    }

    return $orders;
  }

  public function getPurchaseOrder($purchase_id) {
    // get purchase orders
    $sql = "SELECT cpo.*, CONCAT(o.firstname, ' ', o.lastname) full_name, o.firstname, o.lastname, o.email, o.order_type, o.quotation_id FROM ". DB_PREFIX ."codevoc_purchase_order cpo JOIN `". DB_PREFIX ."order` o ON o.order_id = cpo.order_id WHERE purchase_id = ". $purchase_id;
    $purchase_order_query = $this->db->query($sql);

    $orders = [];
    foreach($purchase_order_query->rows as $purchase_order) {
      if($purchase_order['order_type'] == 'Webshop'){
        $the_order_type = 'Oprofilerad';
      }elseif($purchase_order['order_type'] == 'Quotation'){
          $the_order_type = 'Profilerad';
      }else{
        $the_order_type = 'N/A';
      }

      // retrive products count
      $sql = "SELECT COUNT(*) as total FROM ". DB_PREFIX ."codevoc_purchase_product WHERE purchase_order_id = '". $purchase_order['purchase_order_id'] ."'";
      $product_count_query = $this->db->query($sql);
      $product_count = $product_count_query->row['total'];

      // retrive order products count
      $sql = "SELECT COUNT(*) as total FROM ". DB_PREFIX ."order_product WHERE order_id = '". $purchase_order['order_id'] ."'";
      $order_product_count_query = $this->db->query($sql);
      $order_product_count = $order_product_count_query->row['total'];

      $orders[] = [
        'purchase_order_id' => $purchase_order['purchase_order_id'],
        'purchase_id' => $purchase_order['purchase_id'],
        'order_id' => $purchase_order['order_id'],
        'comment' => $purchase_order['comment'],
        'purchase_cost' => $purchase_order['purchase_cost'],
        'full_name' => $purchase_order['full_name'],
        'firstname' => $purchase_order['firstname'],
        'lastname' => $purchase_order['lastname'],
        'email' => $purchase_order['email'],
        'order_type' => $the_order_type,
        'quotation_id' => $purchase_order['quotation_id'],
        'link' => $this->url->link('codevoc/b2bmanager_order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $purchase_order['order_id'], true),
        'products' => $this->getPurchaseOrderProducts($purchase_order['purchase_order_id']),
        'product_count' => $product_count,
        'order_product_count' => $order_product_count,
        'costs' => $this->getPurchaseCosts($purchase_order['order_id']),
      ];
    }

    return $orders;
  }

  public function getPurchaseOrderProducts($purchase_order_id) {
    /* Retrive order products */
    $sql = "SELECT cpp.*, cps.name as supplier_name FROM ". DB_PREFIX ."codevoc_purchase_product cpp LEFT JOIN ". DB_PREFIX ."codevoc_purchase_supplier cps ON cps.purchase_supplier_id = cpp.purchase_supplier_id WHERE purchase_order_id = ". $purchase_order_id;
    $purchase_order_product_query = $this->db->query($sql);
    $products = [];

    foreach($purchase_order_product_query->rows as $purchase_order_product) {
      $options = $this->getPurchaseOrderProductOptions($purchase_order_product['purchase_product_id']);
      $productInventory = $this->checkProductInsideInventory($purchase_order_product['model'], $options);
      $products[] = [
        'purchase_product_id' => $purchase_order_product['purchase_product_id'],
        'purchase_order_id' => $purchase_order_product['purchase_order_id'],
        'purchase_id' => $purchase_order_product['purchase_id'],
        'purchase_supplier_id' => $purchase_order_product['purchase_supplier_id'],
        'supplier_name' => $purchase_order_product['supplier_name'],
        'product_id' => $purchase_order_product['product_id'],
        'name' => $purchase_order_product['name'],
        'model' => $purchase_order_product['model'],
        'quantity' => (int)$purchase_order_product['quantity'],
        'purchased' => (int)$purchase_order_product['purchased'],
        'pending' => (int)$purchase_order_product['pending'],
        'options' => $options,
        'inventory' => $productInventory,
      ];
    }

    return $products;
  }

  public function getPurchaseOrderProductsWithoutOptions($purchase_order_id) {
    /* Retrive order products */
    $sql = "SELECT cpp.*, cps.name as supplier_name FROM ". DB_PREFIX ."codevoc_purchase_product cpp LEFT JOIN ". DB_PREFIX ."codevoc_purchase_supplier cps ON cps.purchase_supplier_id = cpp.purchase_supplier_id WHERE purchase_order_id = ". $purchase_order_id;
    $purchase_order_product_query = $this->db->query($sql);
    $products = [];

    foreach($purchase_order_product_query->rows as $purchase_order_product) {
      $products[] = [
        'purchase_product_id' => $purchase_order_product['purchase_product_id'],
        'purchase_order_id' => $purchase_order_product['purchase_order_id'],
        'purchase_id' => $purchase_order_product['purchase_id'],
        'purchase_supplier_id' => $purchase_order_product['purchase_supplier_id'],
        'supplier_name' => $purchase_order_product['supplier_name'],
        'product_id' => $purchase_order_product['product_id'],
        'name' => $purchase_order_product['name'],
        'model' => $purchase_order_product['model'],
        'quantity' => (int)$purchase_order_product['quantity'],
        'purchased' => (int)$purchase_order_product['purchased'],
        'pending' => (int)$purchase_order_product['pending'],
      ];
    }

    return $products;
  }

  public function getPurchaseOrderProductOptions($purchase_product_id) {
    /* Retrive order products */
    $sql = "SELECT * FROM ". DB_PREFIX ."codevoc_purchase_product_option WHERE purchase_product_id = ". $purchase_product_id;
    $purchase_product_option_query = $this->db->query($sql);
    $options = [];

    foreach($purchase_product_option_query->rows as $purchase_product_option) {
      $options[] = [
        'purchase_product_option_id' => $purchase_product_option['purchase_product_option_id'],
        'purchase_id' => $purchase_product_option['purchase_id'],
        'purchase_product_id' => $purchase_product_option['purchase_product_id'],
        'product_option_id' => $purchase_product_option['product_option_id'],
        'product_option_value_id' => $purchase_product_option['product_option_value_id'],
        'name' => $purchase_product_option['name'],
        'value' => $purchase_product_option['value'],
      ];
    }

    // Sort options alphabatically so colors comes first and size comes after
    usort($options, function($a, $b) {
      return strcmp($a['name'], $b['name']);
    });

    return $options;
  }

  public function checkProductInsideInventory($model, $options) {
    $color = '';
    $size = '';
    foreach($options as $option) {
        $option_name = strtolower($option['name']);
        if($option_name == 'color' || $option_name == 'colors' || $option_name == 'färg') {
          $color = $option['value'];
          continue;
        }
        if($option_name == 'size' || $option_name == 'sizes' || $option_name == 'storlek') {
          $size = $option['value'];
          continue;
        }
    }
    if(!$size && !$color)
      return null;

    $sql = "SELECT * FROM " . DB_PREFIX . "codevoc_inventory where article_nr = '$model' and color = '$color' and size = '$size' and quantity > 0 order by quantity desc";
    $query = $this->db->query($sql);

    return $query->row ?: null;
  }

  public function savePurchaseDetail($purchase_id, $data) {
    $this->db->query("UPDATE " . DB_PREFIX . "codevoc_purchase SET purchase_receiver_id = '" . (int)$data['purchase_receiver_id'] . "', name = '". $this->db->escape($data['name']) . "' WHERE purchase_id = '" . (int)$purchase_id . "'");
  }

  public function saveOrderComment($purchase_order_id, $data) {
    $this->db->query("UPDATE " . DB_PREFIX . "codevoc_purchase_order SET comment = '" . $this->db->escape($data['comment']) . "' WHERE purchase_order_id = '" . (int)$purchase_order_id . "'");
  }

  public function getPurchaseCosts($order_id) {
    $sql = "SELECT * FROM `". DB_PREFIX ."codevoc_order_costs` WHERE order_id = ". (int)$order_id;
    $query = $this->db->query($sql);
    return $query->rows;
  }

  public function savePurchaseCost($order_id, $data) {
    $sql = "INSERT INTO `". DB_PREFIX ."codevoc_order_costs` SET order_id = '". (int)$order_id ."', supplier='". $this->db->escape($data['supplier']) ."', cost='". (float)$data['cost'] ."', type='". $this->db->escape($data['type']) ."', label='". $this->db->escape($data['label']) ."', created_at='". date('Y-m-d H:i:s') ."', updated_at='". date('Y-m-d H:i:s') ."'";
    $this->db->query($sql);
    $cost_id = $this->db->getLastId();

    $sql = "SELECT * FROM `". DB_PREFIX ."codevoc_order_costs` WHERE id = ". $cost_id;
    $query = $this->db->query($sql);
    return $query->row;
  }

  public function removeCost($cost_id) {
    $sql = "DELETE FROM `". DB_PREFIX ."codevoc_order_costs` WHERE id = '".(int)$cost_id."'";
    $query = $this->db->query($sql);
  }

  public function removeOrder($purchase_order_id) {
    // Remove product options
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_product_option where purchase_product_id in (SELECT purchase_product_id from ". DB_PREFIX ."codevoc_purchase_product where purchase_order_id = ". $purchase_order_id .")");

    // Remove products
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_product where purchase_order_id = '". $purchase_order_id ."'");

    // Remove order
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_order where purchase_order_id = '". $purchase_order_id ."'");
  }

  public function removeOrderAndChangeStatus($purchase_order_id) {
    // Get order_id from purchase_order_id
    $sql = "SELECT * FROM `". DB_PREFIX ."codevoc_purchase_order` WHERE purchase_order_id = '". (int)$purchase_order_id ."'";
    $query = $this->db->query($sql);
    $order_id = $query->row['order_id'];

    // Update order status to 9
    $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '9' WHERE order_id = '" . (int)$order_id . "'");

    // Remove product options
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_product_option where purchase_product_id in (SELECT purchase_product_id from ". DB_PREFIX ."codevoc_purchase_product where purchase_order_id = ". $purchase_order_id .")");

    // Remove products
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_product where purchase_order_id = '". $purchase_order_id ."'");

    // Remove order
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_order where purchase_order_id = '". $purchase_order_id ."'");
  }

  public function saveSupplier($purchase_order_id, $data) {
    foreach($data['products'] as $product) {
      $purchased = (int)filter_var($product['purchased'], FILTER_VALIDATE_BOOLEAN);
      $pending = (int)filter_var($product['pending'], FILTER_VALIDATE_BOOLEAN);
      // if purchased is not set then we will remove supplier
      $supplier_id = 0;
      // update supplier id if purchased is set to true and supplier id is not set
      if($purchased && !$product['purchase_supplier_id']) {
        $supplier_id = (int)$data['supplier_id'];
      } else if($purchased && $product['purchase_supplier_id']){
        // if purchased is true and supplier id is already set then we will not update supplier id with new supplier id and we keep old supplier id
        $supplier_id = (int)$product['purchase_supplier_id'];
      }
      $this->db->query("UPDATE " . DB_PREFIX . "codevoc_purchase_product SET purchase_supplier_id = '" . $supplier_id . "', purchased ='" . $purchased. "', pending ='" . $pending . "' WHERE purchase_product_id = '" . (int)$product['purchase_product_id'] . "'");
    }
  }

  public function finishPurchaseAndChangeStatus($user_id, $purchase_id) {
    // Update purchase status to finish
    $this->db->query("UPDATE `". DB_PREFIX ."codevoc_purchase` SET purchase_finished = '1' WHERE purchase_id = '". (int)$purchase_id ."'");

    // Retrive orders for the purchase id
    $purchase_order_query = $this->db->query("SELECT * FROM `". DB_PREFIX ."codevoc_purchase_order` WHERE purchase_id = ". (int)$purchase_id);

    // Retrive Order status id for processing
    $order_status_query = $this->db->query("SELECT * FROM `". DB_PREFIX ."order_status` WHERE name = 'Behandlas'");
    $order_status_id = $order_status_query->row['order_status_id'];

    // Retrive user information
    $user_query = $this->db->query("SELECT * FROM `". DB_PREFIX ."user` WHERE user_id = '". (int)$user_id ."'");
    $user_fullname = implode(' ', [$user_query->row['firstname'], $user_query->row['lastname']]);
    $comment = "Order Purchased By ". $user_fullname;

    // Update order status to processing and add order history to each order
    foreach($purchase_order_query->rows as $purchase_order) {
      $this->db->query("UPDATE `". DB_PREFIX ."order` SET order_status_id = '". $order_status_id ."' WHERE order_id = '". $purchase_order['order_id'] ."'");

      $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$purchase_order['order_id'] . "', order_status_id = '" . $order_status_id . "', notify = '0', comment = '" . $comment. "', date_added = '" . date('Y-m-d H:i:s')."'");
    }
  }

  public function deletePurchase($purchase_id) {
    // Remove product options
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_product_option where purchase_id = '". (int)$purchase_id ."'");

    // Remove products
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_product where purchase_id = '". (int)$purchase_id ."'");

    // Remove orders
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase_order where purchase_id = '". (int)$purchase_id ."'");

    // Remove purchase order
    $this->db->query("DELETE FROM ". DB_PREFIX ."codevoc_purchase where purchase_id = '". (int)$purchase_id ."'");
  }

  public function getSaleOrder($order_id) {
    $sql = "SELECT * FROM ". DB_PREFIX ."order cpp WHERE order_id = ". (int)(trim($order_id));
    $order_query = $this->db->query($sql);

    return $order_query->row;
  }
  public function import(){
    $query= $this->db->query("SELECT * FROM `". DB_PREFIX ."codevoc_purchase_pw3` ORDER BY `purchase_id` ASC LIMIT 10");
    foreach($query->rows as $row){
      $this->db->query("INSERT INTO `" . DB_PREFIX . "codevoc_purchase` SET 
      `purchase_id` = '" . $row['purchase_id'] ."',
      `purchase_receiver_id` = '" . $row['purchase_receiver_id'] ."', 
      `purchased_by` = '" . $row['purchased_by'] ."', 
      `name` = '" . $row['name'] ."', 
      `purchase_finished` = '" . $row['purchase_finished'] ."', 
      `created_at` = '" . $row['created_at'] ."', 
      `updated_at` ='". $row['updated_at'] ."'");

      $query_order=$this->db->query("SELECT * FROM `". DB_PREFIX ."codevoc_purchase_order_pw3` WHERE `purchase_id` = '".$row['purchase_id']."'");
      if(count($query_order->rows) > 0){
        foreach($query_order->rows as $row1){
           $this->db->query("INSERT INTO `". DB_PREFIX ."codevoc_purchase_order` SET 
           `purchase_order_id` = '" . $row1['purchase_order_id'] ."',
           `purchase_id` = '" . $row1['purchase_id'] ."', 
           `order_id` = '" . $row1['order_id'] ."', 
           `comment` = '" . $row1['comment'] ."', 
           `purchase_cost` = '" . $row1['purchase_cost'] ."', 
           `created_at` = '" . $row1['created_at'] ."', 
           `updated_at` ='". $row1['updated_at'] ."'");
        }
      }

      $query_product=$this->db->query("SELECT * FROM `". DB_PREFIX ."codevoc_purchase_product_pw3` WHERE `purchase_id` = '".$row['purchase_id']."'");
      if(count($query_product->rows) > 0){
        foreach($query_product->rows as $row2){
           $this->db->query("INSERT INTO `". DB_PREFIX ."codevoc_purchase_product` SET 
           `purchase_product_id` = '" . $row2['purchase_product_id'] ."',
           `purchase_order_id` = '" . $row2['purchase_order_id'] ."', 
           `purchase_id` = '" . $row2['purchase_id'] ."', 
           `purchase_supplier_id` = '" . $row2['purchase_supplier_id'] ."', 
           `product_id` = '" . $row2['product_id'] ."', 
           `name` = '" . $row2['name'] ."', 
           `model` = '" . $row2['model'] ."', 
           `quantity` = '" . $row2['quantity'] ."', 
           `purchased` = '" . $row2['purchased'] ."', 
           `pending` = '" . $row2['pending'] ."', 
           `created_at` = '" . $row2['created_at'] ."', 
           `updated_at` ='". $row2['updated_at'] ."'");
        }
      }
      $query_product_option=$this->db->query("SELECT * FROM `". DB_PREFIX ."codevoc_purchase_product_option_pw3` WHERE `purchase_id` = '".$row['purchase_id']."'");
      if(count($query_product_option->rows) > 0){
        foreach($query_product_option->rows as $row3){
           $this->db->query("INSERT INTO `". DB_PREFIX ."codevoc_purchase_product_option` SET 
           `purchase_product_option_id` = '" . $row3['purchase_product_option_id'] ."',
           `purchase_id` = '" . $row3['purchase_id'] ."', 
           `purchase_product_id` = '" . $row3['purchase_product_id'] ."', 
           `product_option_id` = '" . $row3['product_option_id'] ."', 
           `product_option_value_id` = '" . $row3['product_option_value_id'] ."', 
           `name` = '" . $row3['name'] ."', 
           `value` = '" . $row3['value'] ."', 
           `created_at` = '" . $row3['created_at'] ."', 
           `updated_at` ='". $row3['updated_at'] ."'");
        }
      }
    }
  }
}