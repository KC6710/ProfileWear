<?php
namespace Opencart\Catalog\Controller\Category;
class Category extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = [];

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = [];

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					$filter_data = [
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					];

					$children_data[] = [
						'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_' . $child['category_id'])
					];
				}

				// Level 1
				$data['categories'][] = [
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'])
				];
			}
		}
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('catalog/category', $data));
	}

	protected function getList(): array {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		// $data['action'] = $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['categories'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->load->model('catalog/category');

		// $category_total = $this->model_catalog_category->getTotalCategories();

		$results = $this->model_catalog_category->getAllCategories($filter_data);
		foreach ($results as $result) {
			$data['categories'][] = [
				'category_id' => $result['category_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'edit'        => HTTP_SERVER . 'index.php?route=product/category'.'&path=' . $result['path_id'] 
			];
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		// $data['sort_name'] = $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		// $data['sort_sort_order'] = $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		// $data['pagination'] = $this->load->controller('common/pagination', [
		// 	'total' => $category_total,
		// 	'page'  => $page,
		// 	'limit' => $this->config->get('config_pagination_admin'),
		// 	'url'   => $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		// ]);

		// $data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($category_total - $this->config->get('config_pagination_admin'))) ? $category_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $category_total, ceil($category_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return  $data;
	}

}
?>