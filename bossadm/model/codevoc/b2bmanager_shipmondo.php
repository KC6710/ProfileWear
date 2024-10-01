<?php
namespace Opencart\Admin\Model\Codevoc;

class B2bmanagerShipmondo extends \Opencart\System\Engine\Model {
    public function insertDetails($data){
        $this->db->query("INSERT INTO `". DB_PREFIX . "codevoc_shipmondo` SET 
        shipment_id='".(int)$data['shipment_id']."',
        order_id='".(int)$data['order_id']."',
        quantity='".$data['quantity']."',
        weight='".$data['weight']."',
        carrier_code='".$data['carrier_code']."',
        product_code='".$data['product_code']."',
        service_codes='".$data['service_codes']."',
        pdf_data='".$data['pdf_data']."',
        package_type='".$data['package_type']."'");

        return $shipment_id = $this->db->getLastId();

    }
    public function getShipmentDetails($order_id){
        // echo "SELECT * FROM `". DB_PREFIX . "codevoc_shipmondo` WHERE order_id = '".(int)$order_id."'"; die;
        $query = $this->db->query("SELECT * FROM `". DB_PREFIX . "codevoc_shipmondo` WHERE order_id = '".(int)$order_id."'");
        // echo "<pre>"; print_r($query->num_rows);die;
        if($query->num_rows > 0){
            return $query->rows;
        }else{
            return array();
        }
    }
    public function getPdfData($id){
        $query = $this->db->query("SELECT * FROM `". DB_PREFIX . "codevoc_shipmondo` WHERE id ='".$id."'");
        return $query->rows[0];
    }
    public function insertPdfId($id, $shipment_id){
        $query = $this->db->query("UPDATE `". DB_PREFIX . "codevoc_shipmondo` SET `pdf_id`= '".$shipment_id."' WHERE `id`= '".$id."'");
        return $shipment_id = $this->db->getLastId();
    }

}