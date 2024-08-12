<?php
namespace Opencart\Admin\Model\Codevoc;
class B2bmanagerFortnox extends \Opencart\System\Engine\Model {
	public function name($id) {
	}

	public function getInvoiceByOrderId($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "codevoc_b2b_fortnox`  WHERE order_id = ". (int)$order_id);

		return $query->row;
	}

	public function attachOrderToInvoice($order_id, $fortnox_invoice_id) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_b2b_fortnox SET order_id = '" . (int)$order_id . "', fortnox_invoice_nr = '" . (int)$fortnox_invoice_id . "'");
	}
}
