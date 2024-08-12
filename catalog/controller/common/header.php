<?php
namespace Opencart\Catalog\Controller\Common;
class Header extends \Opencart\System\Engine\Controller {
	public function index(): string {
		// Analytics
		$data['analytics'] = [];

		if (!$this->config->get('config_cookie_id') || (isset($this->request->cookie['policy']) && $this->request->cookie['policy'])) {
			$this->load->model('setting/extension');

			$analytics = $this->model_setting_extension->getExtensionsByType('analytics');

			foreach ($analytics as $analytic) {
				if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
					$data['analytics'][] = $this->load->controller('extension/' . $analytic['extension'] . '/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
				}
			}
		}
		if(isset($this->request->get['route'])){
			$data['thisURL'] = $this->request->get['route'];
		}
				
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['title'] = $this->document->getTitle();
		$data['base'] = $this->config->get('config_url');
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();

		// Hard coding css so they can be replaced via the event's system.
		$data['bootstrap'] = 'catalog/view/stylesheet/bootstrap.css';
		$data['icons'] = 'catalog/view/stylesheet/fonts/fontawesome/css/all.min.css';
		$data['stylesheet'] = 'catalog/view/stylesheet/stylesheet.css';

		// Hard coding scripts so they can be replaced via the event's system.
		$data['jquery'] = 'catalog/view/javascript/jquery/jquery-3.6.1.min.js';

		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['home'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));
		$data['wishlist'] = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['logged'] = $this->customer->isLogged();

		if (!$this->customer->isLogged()) {
			$data['register'] = $this->url->link('account/register', 'language=' . $this->config->get('config_language'));
			$data['login'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'));
		} else {
			$data['account'] = $this->url->link('account/edit', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['order'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['transaction'] = $this->url->link('account/transaction', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['download'] = $this->url->link('account/download', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['logout'] = $this->url->link('account/logout', 'language=' . $this->config->get('config_language'));
		}

		$data['shopping_cart'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'));
		$data['checkout'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'));
		$data['contact'] = $this->url->link('information/contact', 'language=' . $this->config->get('config_language'));
		$data['telephone'] = $this->config->get('config_telephone');

		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');

		$data['list'] = $this->load->controller('account/wishlist.getList');
		$this->load->model('catalog/category');
		$this->load->model('tool/image');
		foreach($this->headerMenu() as $header_menu){
			$menu_name = explode("&gt;",$header_menu['admin_name']);
			$name = ltrim($menu_name[1]," ");
			$categories = explode(",",$header_menu['code']);
			$dropdown_data = [];
			foreach($categories as $category){
				$category_info = $this->model_catalog_category->getCategory($category);
				$category_image ='';
				if (is_file(DIR_IMAGE . html_entity_decode($category_info['image'], ENT_QUOTES, 'UTF-8'))) {
					$category_image = $this->model_tool_image->resize(html_entity_decode($category_info['image'], ENT_QUOTES, 'UTF-8'), 288, 216);
				} else {
					$$category_image = '';
				}
				$seo_url = $this->model_catalog_category->getSeoUrls($category);
				$dropdown_data[] = [
					'name'   => $category_info['name'],
					'image'  => $category_image,
					'url'    => count($seo_url) > 0 ? HTTPS_SERVER . ltrim($seo_url[0][$this->config->get('config_language_id')],"/"): '' 
				];
			}
			$menu_id = str_replace(" ", "", $name);
			$data['header_menu'][]=[
				'name'            => $name,
				'id'     => str_replace("&amp;", "_", $menu_id),
				'dropdown_data'   => $dropdown_data 
			];	
		}

		if($this->cart->hasProducts()) {
			$group_product = array('00-prt-d-s','00-prt-d-25','00-prt-d-100','00-prt-d-200','00-prt-d-300','00-prt-scr-s','00-prt-scr-1','00-prt-scr-2','00-prt-scr-3','00-prt-scr-4');
			$cartProducts = $this->cart->getCartProducts();
			$products = 0;
			foreach($cartProducts as $product){
				if(!in_array($product['model'],$group_product)){
					$products = $products + (int)$product['quantity'];
				}
			}
		} else {
			$products = 0;
		}
		$data['cart_items'] = $products;
		$data['SITE_URL'] = HTTPS_SERVER;
		
		return $this->load->view('common/header', $data);
	}
	public function headerMenu(){
		$query = $this->db->query("SELECT * FROM `". DB_PREFIX ."custom_shortcodes` WHERE `admin_name` LIKE '%menu%'");
		return $query->rows;
	}
}
