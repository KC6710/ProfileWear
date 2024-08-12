<?php
namespace Opencart\Catalog\Controller\Product;
class CatThumb extends \Opencart\System\Engine\Controller {
	public function index(array $data): string {
		$this->load->language('product/cat_thumb');

		return $this->load->view('product/cat_thumb', $data);
	}
}
