<?php namespace Ocme\OpenCart\Catalog\Controller;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

trait BrowseCatalog {
	
	public function index() {
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
		
		/* @var $filter string */
		$filter = ocme()->request()->query('filter', '');
		
		/* @var $sort string */
		$sort = ocme()->request()->query('sort', 'p.sort_order');
		
		/* @var $order string */
		$order = ocme()->request()->query('order', 'ASC');
		
		/* @var $page int */
		$page = max( 1, (int) ocme()->request()->query('page', 1) );
		
		/* @var $limit int */
		$limit = (int) ocme()->request()->query('limit', $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'));

		$data['ocme'] = ocme();
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => ocme()->trans('browse::catalog.text_home'),
			'href' => $this->url->link('common/home')
		);

		if ( ocme()->request()->hasQuery( 'path' ) ) {			
			$url = ocme()->request()->createOpenCartUrlParams('sort', 'order', 'limit');

			$path = '';

			$parts = explode('_', (string)ocme()->request()->query( 'path' ));

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('browse/catalog', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		if( $category_id ) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$this->document->setTitle($category_info['meta_title']);
				$this->document->setDescription($category_info['meta_description']);
				$this->document->setKeywords($category_info['meta_keyword']);

				$data['heading_title'] = $category_info['name'];
				
				// Set the last category breadcrumb
				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('browse/catalog', 'path=' . ocme()->request()->query('path'))
				);

				if ($category_info['image']) {
					$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
				} else {
					$data['thumb'] = '';
				}

				$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			}
		} else {
			$this->document->setTitle(ocme()->trans('browse::catalog.text_catalog'));
			$data['heading_title'] = ocme()->trans('browse::catalog.text_products');
			
			$data['breadcrumbs'][] = array(
				'text' => ocme()->trans('browse::catalog.text_catalog'),
				'href' => $this->url->link('browse/catalog')
			);
		}

		$data['text_compare'] = sprintf(ocme()->trans('browse::catalog.text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$data['compare'] = $this->url->link('product/compare');

		$url = ocme()->request()->createOpenCartUrlParams('filter', 'sort', 'order', 'limit');

		$data['categories'] = array();

		if( $category_id ) {
			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'href' => $this->url->link('browse/catalog', 'path=' . ocme()->request()->query('path') . '_' . $result['category_id'] . $url)
				);
			}
		}

		$data['products'] = array();

		$filter_data = array(
			'filter_filter'      => $filter,
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		if( $category_id ) {
			$filter_data['filter_category_id'] = $category_id;
		}

		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}

			if ($this->config->get('config_review_status')) {
				$rating = (int)$result['rating'];
			} else {
				$rating = false;
			}

			$data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
				'rating'      => $result['rating'],
				'href'        => $this->url->link('product/product', ( ocme()->request()->hasQuery('path') ? 'path=' . ocme()->request()->query('path') . '&' : '' ) . 'product_id=' . $result['product_id'] . $url)
			);
		}

		$url = ocme()->request()->createOpenCartUrlParams('path', 'filter', 'limit');

		$data['sorts'] = array();
		
		foreach( array(
			'default' => 'p.sort_order',
			'name' => 'pd.name',
			'price' => 'p.price',
			'rating' => 'rating',
			'model' => 'p.model',
		) as $key => $val ) {
			if( $val == 'rating' && ! $this->config->get('config_review_status') ) {
				continue;
			}
			
			foreach( array( 'ASC', 'DESC' ) as $stype ) {
				$data['sorts'][] = array(
					'text'  => ocme()->trans( 'browse::catalog.text_' . $key . ( $key == 'default' ? '' : '_' . strtolower( $stype ) ) ),
					'value' => $val . '-' . $stype,
					'href'  => $this->url->link('browse/catalog', 'sort=' . $val . '&order=' . $stype . $url)
				);
				
				if( $key == 'default' ) {
					break;
				}
			}
		}

		$url = ocme()->request()->createOpenCartUrlParams('path', 'filter', 'sort', 'order');

		$data['limits'] = array();

		$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('browse/catalog', $url . ( $url ? '&' : '' ) . 'limit=' . $value)
			);
		}

		$url = ocme()->request()->createOpenCartUrlParams('path', 'filter', 'sort', 'order', 'limit');

		$pagination = new \Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('browse/catalog', $url . ( $url ? '&' : '' ) . 'page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf(ocme()->trans('browse::catalog.text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

		// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
		if ($page == 1) {
			$this->document->addLink($this->url->link('browse/catalog', $category_id ? 'path=' . $category_id : ''), 'canonical');
		} else {
			$this->document->addLink($this->url->link('browse/catalog', ( $category_id ? 'path=' . $category_id . '&' : '' ) . 'page='. $page), 'canonical');
		}

		if ($page > 1) {
			$this->document->addLink($this->url->link('browse/catalog', ( $category_id ? 'path=' . $category_id . '&' : '' ) . (($page - 2) ? 'page='. ($page - 1) : '')), 'prev');
		}

		if ($limit && ceil($product_total / $limit) > $page) {
			$this->document->addLink($this->url->link('browse/catalog', ( $category_id ? 'path=' . $category_id . '&' : '' ) . 'page='. ($page + 1)), 'next');
		}

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('browse/catalog', $data));
	}
	
}
