<?php
namespace Opencart\Catalog\Controller\Product;
class Thumb extends \Opencart\System\Engine\Controller {
	public function index(array $data): string {
		$this->load->language('product/thumb');
        
		$this->load->model('catalog/manufacturer');
		
		if(array_key_exists('manufacturer_id', $data) && $data['manufacturer_id'] != 0){
			$data['manufacturer'] = $this->model_catalog_manufacturer->getManufacturer($data['manufacturer_id']);

			if (is_file(DIR_IMAGE . html_entity_decode($data['manufacturer']['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $this->model_tool_image->resize(html_entity_decode($data['manufacturer']['image'], ENT_QUOTES, 'UTF-8'), 60, 37);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', 60, 37);
			}
	
			$data['manufacturer']['thumb'] = $image;
		}

		$data['cart'] = $this->url->link('common/cart.info', 'language=' . $this->config->get('config_language'));

		$data['add_to_cart'] = $this->url->link('checkout/cart.add', 'language=' . $this->config->get('config_language'));
		
		$data['add_to_wishlist'] = $this->url->link('account/wishlist.add', 'language=' . $this->config->get('config_language'));

		$data['remove'] = $this->url->link('account/wishlist.remove', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));

		$data['add_to_compare'] = $this->url->link('product/compare.add', 'language=' . $this->config->get('config_language'));

		$data['review_status'] = (int)$this->config->get('config_review_status');
		$data['wishlist'] = $this->url->link('account/wishlist.list', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		return $this->load->view('product/thumb', $data);
	}
}
