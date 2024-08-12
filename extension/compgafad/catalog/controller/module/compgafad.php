<?php
namespace Opencart\catalog\Controller\Extension\compgafad\module;
class compgafad extends \Opencart\System\Engine\Controller {
	private $error = array();
	private $modpath = 'module/compgafad'; 
	private $modvar = 'model_module_compgafad';
	private $modtpl = 'module/compgafad.tpl';
	private $modname = 'compgafad';
	private $evntcode = 'compgafad';
 	private $modurl = 'extension/module';
	private $status = false;
	private $setting = array();

	public function __construct($registry) {		
		parent::__construct($registry);		
		ini_set("serialize_precision", -1);
		
		if(substr(VERSION,0,3)=='1.5') {
			$this->modtpl = 'module/compgafad15X.tpl';
		}
		if(substr(VERSION,0,3)=='2.0') {
		}
		if(substr(VERSION,0,3)=='2.1') {
		}		
		if(substr(VERSION,0,3)=='2.2') {
			$this->modtpl = 'module/compgafad';
		}
		if(substr(VERSION,0,3)=='2.3') {
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modtpl = 'extension/module/compgafad';			
			$this->modurl = 'extension/extension';
		}
		if(substr(VERSION,0,3)=='3.0') {			
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modtpl = 'extension/module/compgafad30X';
			$this->modname = 'module_compgafad';
			$this->modurl = 'marketplace/extension'; 
		} 
		if(substr(VERSION,0,3)=='4.0') {
			$this->modpath = 'extension/compgafad/module/compgafad';
			$this->modvar = 'model_extension_compgafad_module_compgafad';
			$this->modtpl = 'extension/compgafad/module/compgafad40X';			
			$this->modname = 'module_compgafad';
			$this->modurl = 'marketplace/extension'; 
		}
		
		$this->setting = $this->getSetting();
		$this->status = ($this->config->get($this->modname.'_status') && $this->setting['status']) ? true : false;	
		//$this->status = $this->config->get($this->modname.'_status');
 	}
	public function pageview(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->pageview();
		$findcode = '</head>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function login(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->login();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function logoutbefore(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->logoutbefore();
	}
	public function logout(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->logout();
		$findcode = '</body>';
		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function signupbefore(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->signupbefore();
	}
	public function signup(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->signup();
		$findcode = '</body>';
		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function contact(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->contact();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function addtocart() {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->addtocart();
	}
	public function addtowishlist() {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->addtowishlist();
	}
	public function viewcont(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->viewcont();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function viewcategory(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->viewcategory();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function search(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->search();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function remove_from_cart(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->remove_from_cart();
	}
	public function viewcart(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->viewcart();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	public function beginchk(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->beginchk();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);		
	}
	public function purchase(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$replace_code = $this->{$this->modvar}->purchase();
		$findcode = '</body>';
 		$output = str_replace($findcode, $replace_code . $findcode, $output);
	}
	
	public function getSetting() {		
		$this->load->model($this->modpath);
		return $this->{$this->modvar}->getSetting();
	}
	public function loadjscss(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->loadjscss();
	}
}