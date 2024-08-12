<?php
namespace Opencart\admin\model\Extension\compgafad\module;
class compgafad extends \Opencart\System\Engine\model {
	private $error = array();
	private $modpath = 'module/compgafad'; 
	private $modvar = 'model_module_compgafad';
	private $modtpl = 'module/compgafad.tpl';
	private $modname = 'compgafad';
	private $evntcode = 'compgafad';
 	private $modurl = 'extension/module';
	private $token = '';

	public function __construct($registry) {		
		parent::__construct($registry);		
		ini_set("serialize_precision", -1);
		
		if(substr(VERSION,0,3)=='1.5') {
			if(isset($this->session->data['token'])) { $this->token = 'token=' . $this->session->data['token'] . '&type=module'; }
			$this->modtpl = 'module/compgafad15X.tpl';
		}
		if(substr(VERSION,0,3)=='2.0') {
			if(isset($this->session->data['token'])) { $this->token = 'token=' . $this->session->data['token'] . '&type=module'; }
		}
		if(substr(VERSION,0,3)=='2.1') {
			if(isset($this->session->data['token'])) { $this->token = 'token=' . $this->session->data['token'] . '&type=module'; }
		}		
		if(substr(VERSION,0,3)=='2.2') {
			$this->modtpl = 'module/compgafad';
			if(isset($this->session->data['token'])) { $this->token = 'token=' . $this->session->data['token'] . '&type=module'; }
		}
		if(substr(VERSION,0,3)=='2.3') {
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modtpl = 'extension/module/compgafad';			
			$this->modurl = 'extension/extension';
			if(isset($this->session->data['token'])) { $this->token = 'token=' . $this->session->data['token'] . '&type=module'; }
		}
		if(substr(VERSION,0,3)=='3.0') {			
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modtpl = 'extension/module/compgafad30X';
			$this->modname = 'module_compgafad';
			$this->modurl = 'marketplace/extension'; 
			if(isset($this->session->data['user_token'])) { $this->token = 'user_token=' . $this->session->data['user_token'] . '&type=module'; }
		} 
		if(substr(VERSION,0,3)=='4.0') {
			$this->modpath = 'extension/compgafad/module/compgafad';
			$this->modvar = 'model_extension_compgafad_module_compgafad';
			$this->modtpl = 'extension/compgafad/module/compgafad40X';			
			$this->modname = 'module_compgafad';
			$this->modurl = 'marketplace/extension'; 
			if(isset($this->session->data['user_token'])) { $this->token = 'user_token=' . $this->session->data['user_token'] . '&type=module'; }
		}
 	}
	public function get_InpTxt_html($name, $val, $divcls, $lblcls, $entry, $help = '') {
		return sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> <input type="text" name="%s" value="%s" class="form-control"/> %s </div> </div>', $entry, $name, $val, $help);
	}
	public function get_status_html($name, $val, $divcls, $lblcls, $entry, $txtyes, $text_no) {
		$sel1 = $val == 1 ? 'checked="checked"' : '';
		$sel2 = $val == 0 ? 'checked="checked"' : '';
		return sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> <label class="radio-inline"> <input type="radio" name="%s" value="1" %s/> %s </label> <label class="radio-inline"> <input type="radio" name="%s" value="0" %s/> %s </label> </div> </div>', $entry, $name, $sel1, $txtyes, $name, $sel2, $text_no);		
	}
	public function save() {
		$this->load->language($this->modpath);

		$json = array();

		if (!$this->user->hasPermission('modify', $this->modpath)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting($this->modname, $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function install() { 		
		$query = $this->db->query("SHOW COLUMNS FROM `".DB_PREFIX."order` LIKE 'compgafad_ordflag' ");
		if(!$query->num_rows){
			$this->db->query("ALTER TABLE `".DB_PREFIX."order` ADD `compgafad_ordflag` TINYINT(1) NULL DEFAULT '0' ");
			$this->db->query("UPDATE `" . DB_PREFIX . "order` set compgafad_ordflag = 1");	
		}
		
		$seprtor = '/';
		if(substr(VERSION,0,5) == '4.0.0' || substr(VERSION,0,5) == '4.0.1') {
			$seprtor = '|';
		} 
		if(substr(VERSION,0,5) >= '4.0.2') {
			$seprtor = '.';
		}
		
		$viewtmp = (substr(VERSION,0,3)=='2.2' || substr(VERSION,0,3)=='2.3') ? '*/template/' : '';
		// After Events
		$this->addtoevent('catalog/view/'.$viewtmp.'common/header/after', $seprtor. 'pageview');
		$this->addtoevent('catalog/view/'.$viewtmp.'account/login/after', $seprtor. 'login');
		$this->addtoevent('catalog/view/'.$viewtmp.'common/success/after', $seprtor. 'logout');
		$this->addtoevent('catalog/view/'.$viewtmp.'common/success/after', $seprtor. 'signup');
		$this->addtoevent('catalog/view/'.$viewtmp.'information/contact/after', $seprtor. 'contact');
		$this->addtoevent('catalog/view/'.$viewtmp.'product/product/after', $seprtor. 'viewcont');
		$this->addtoevent('catalog/view/'.$viewtmp.'product/category/after', $seprtor. 'viewcategory');
		$this->addtoevent('catalog/view/'.$viewtmp.'product/search/after', $seprtor. 'search');
		$this->addtoevent('catalog/view/'.$viewtmp.'checkout/cart/after', $seprtor. 'viewcart');
		$this->addtoevent('catalog/view/'.$viewtmp.'*/checkout/after', $seprtor. 'beginchk');
		$this->addtoevent('catalog/view/'.$viewtmp.'*/*/checkout/after', $seprtor. 'beginchk');
		$this->addtoevent('catalog/view/'.$viewtmp.'common/success/after', $seprtor. 'purchase');
		$this->addtoevent('catalog/view/'.$viewtmp.'extension/module/xtensions/*/xheader/after', $seprtor. 'pageview');		
		$this->addtoevent('catalog/view/'.$viewtmp.'extension/module/xtensions/*/xfooter/after', $seprtor. 'beginchk');
		$this->addtoevent('catalog/view/'.$viewtmp.'extension/module/xtensions_success_header/after', $seprtor. 'pageview');			
		$this->addtoevent('catalog/view/'.$viewtmp.'extension/module/xtensions_success/after', $seprtor. 'purchase');	
		
		
		$viewtmp = (substr(VERSION,0,3)=='2.2') ? '*/template/' : '';
		// Before Events		
		$this->addtoevent('catalog/controller/account/logout/before', '/logoutbefore');
		$this->addtoevent('catalog/controller/account/success/before', '/signupbefore');		
		$this->addtoevent('catalog/controller/checkout/cart/remove/before', '/remove_from_cart');
 	}
	public function uninstall() {
		if(substr(VERSION,0,3)=='2.2') {
			$this->load->model('extension/event');
			$this->model_extension_event->deleteEvent($this->evntcode);
		}
		if(substr(VERSION,0,3)=='2.3') {
			$this->load->model('extension/event');
			$this->model_extension_event->deleteEvent($this->evntcode);
		}
		if(substr(VERSION,0,3)=='3.0') {			
			$this->load->model('setting/event');
			$this->model_setting_event->deleteEventByCode($this->evntcode);
		} 
		if(substr(VERSION,0,3)=='4.0') {
			$this->load->model('setting/event');
			$this->model_setting_event->deleteEventByCode($this->evntcode);
		}
	}
	public function addtoevent($taregt, $func) {
		if(stristr($taregt, '/before') && substr(VERSION,0,3)!='2.2') {
			str_replace('*/template/','',$taregt);
		}
		
		if(substr(VERSION,0,3)=='2.2') {
			$this->load->model('extension/event');
			$this->model_extension_event->addEvent($this->evntcode, $taregt, $this->modpath. $func);
		}
		if(substr(VERSION,0,3)=='2.3') {
			$this->load->model('extension/event');
			$this->model_extension_event->addEvent($this->evntcode, $taregt, $this->modpath. $func);
		}
		if(substr(VERSION,0,3)=='3.0') {		
			$this->load->model('setting/event');	
			$this->model_setting_event->addEvent($this->evntcode, $taregt, $this->modpath. $func);
		}
		if(substr(VERSION,0,3)=='4.0') {
			$this->load->model('setting/event');
			$comval = array('code'=> $this->evntcode, 'description' => '', 'status'=>1, 'sort_order'=>1);
			$this->model_setting_event->addEvent(array_merge($comval, array('trigger' => $taregt, 'action' => $this->modpath. $func)));
		}
	}	
	public function loadjscss() {
		if($this->config->get($this->modname.'_status')) {
			if(substr(VERSION,0,3)=='4.0') {
				$this->document->addScript('../extension/compgafad/admin/view/javascript/compgafad.js?vr='.rand());
				$this->document->addStyle('../extension/compgafad/admin/view/javascript/compgafad.css?vr='.rand());
			} else { 
				$this->document->addScript('view/javascript/compgafad.js?vr='.rand());
				$this->document->addStyle('view/javascript/compgafad.css?vr='.rand());
			}
		}			
	}
	public function getStores() {
		$result = array();
		$result[0] = array('store_id' => '0', 'name' => $this->config->get('config_name'));
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store WHERE 1 ORDER BY store_id");
		if($query->num_rows) { 
			foreach($query->rows as $rs) { 
				$result[$rs['store_id']] = $rs;
			}
		}
		return $result;
	} 
	public function getCustomerGroups() {
 		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_group_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");
 		return $query->rows;
	}
	public function getLang() {
 		$lang = array();
		$this->load->model('localisation/language');
  		$languages = $this->model_localisation_language->getLanguages();
		foreach($languages as $language) {
			if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') {
				$imgsrc = "language/".$language['code']."/".$language['code'].".png";
			} else {
				$imgsrc = "view/image/flags/".$language['image'];
			}
			$lang[] = array("language_id" => $language['language_id'], "name" => $language['name'], "imgsrc" => $imgsrc);
		}
 		return $lang;
	}
}