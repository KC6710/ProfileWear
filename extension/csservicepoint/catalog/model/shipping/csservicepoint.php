<?php
namespace Opencart\catalog\model\Extension\Csservicepoint\Shipping;
class Csservicepoint extends \Opencart\System\Engine\Model {
	function getQuote($address) {
		$this->load->language('extension/csservicepoint/shipping/csservicepoint');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_csservicepoint_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_csservicepoint_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$quote_data = array();

			$quote_data['csservicepoint'] = array(
				'code'         => 'csservicepoint.csservicepoint',
				'shipping_code' => 'csservicepoint',
				'name'         => $this->language->get('text_description'),
				'cost'         => $this->config->get('shipping_csservicepoint_cost'),
				'tax_class_id' => $this->config->get('shipping_csservicepoint_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($this->config->get('shipping_csservicepoint_cost'), $this->config->get('shipping_csservicepoint_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'csservicepoint',
				'name'       => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_csservicepoint_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}