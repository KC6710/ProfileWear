<?php
namespace Opencart\Admin\Controller\Extension\compgafad\module;
class compgafad extends \Opencart\System\Engine\Controller {
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
	
	public function index() {
		$lang = $this->load->language($this->modpath); 		
		$data = $this->load->language($this->modpath);
		
		$this->load->model($this->modpath);
		$data['langs'] = $this->{$this->modvar}->getLang();
		$data['stores'] = $this->{$this->modvar}->getStores();
		$data['cgs'] = $this->{$this->modvar}->getCustomerGroups();

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() && substr(VERSION,0,3)!='4.0') {
			$this->model_setting_setting->editSetting($this->modname, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if(substr(VERSION,0,3)=='1.5') {
				$this->redirect($this->url->link($this->modpath, $this->token, true));
			} else {
				$this->response->redirect($this->url->link($this->modpath, $this->token, true));
			}
		}
 
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['text_success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['text_success'] = '';
		}

		if(substr(VERSION,0,3)=='1.5') {
			$this->data['breadcrumbs'] = array();
			$this->data['breadcrumbs'][] = array(
				'separator' => ':',
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->modpath, $this->token, true)
			);
		} else {
			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->modpath, $this->token, true)
			);
		}
		
		$data['action'] = $this->url->link($this->modpath, $this->token, true);
		$data['cancel'] = $this->url->link($this->modurl, $this->token , true); 
		
		if(substr(VERSION,0,5) == '4.0.0' || substr(VERSION,0,5) == '4.0.1') {
			$data['action'] = $this->url->link($this->modpath.'|save', $this->token);
			$data['cancel'] = $this->url->link($this->modurl, $this->token);
		} 
		if(substr(VERSION,0,5) >= '4.0.2') {
			$data['action'] = $this->url->link($this->modpath.'.save', $this->token);
			$data['cancel'] = $this->url->link($this->modurl, $this->token);
		}
		
		if(substr(VERSION,0,3)>='3.0') { 
			$data['user_token'] = $this->session->data['user_token'];
		} else {
			$data['token'] = $this->session->data['token'];
		}
		
		$html = array();
		if(substr(VERSION,0,3)=='1.5') { 
			$html = array('<style>.panel-primary {border: 2px solid black; padding: 20px;} .panel-heading { font-size: 15px; font-weight: bold;} .form-group {padding: 15px; width: 90%; display: block; clear: both; border-bottom: 1px solid #ccc; min-height: 30px;} .form-group .control-label { float: left; width: 150px;}</style>');
		}
		$divcls = substr(VERSION,0,3)>='4.0' ? 'row mb-3' : 'form-group';
		$lblcls = substr(VERSION,0,3)>='4.0' ? 'col-form-label' : 'control-label';
		$wellcls = substr(VERSION,0,3)>='4.0' ? 'form-control' : 'well well-sm';
		$grpcls = substr(VERSION,0,3)>='4.0' ? 'input-group-text' : 'input-group-addon';
		 
		$data[$this->modname.'_status'] = $this->setvalue($this->modname.'_status');	
		$data[$this->modname.'_setting'] = $this->setvalue($this->modname.'_setting');
		if(empty($data[$this->modname.'_setting'])) {
			$data[$this->modname.'_setting'] = array();
		}
		
		foreach($data['stores'] as $store) {
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['status']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['status'] = 0;
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['gmid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['gmid'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['prch_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['prch_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['prch_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['prch_adwlbl'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwlbl'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['addtc_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['addtc_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['addtc_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['addtc_adwlbl'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['signup_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['signup_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['signup_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['signup_adwlbl'] = '';
			}
			
			if(substr(VERSION,0,3)>='4.0') {
				$html[] = sprintf('<div class="card"><div class="card-body"><h3 class="card-title">%s</h3>', $store['name']);
			} else {
				$html[] = sprintf('<div class="panel panel-primary"><div class="panel-heading">%s</div><div class="panel-body">', $store['name']);
			}
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'status');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['status'];
			$html[] = $this->{$this->modvar}->get_status_html($name, $val, $divcls, $lblcls, $lang['entry_status'], $lang['text_yes'], $lang['text_no']);
			
			$html[] = $lang['entry_ga4'];
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'gmid');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['gmid'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_gmid'], '');
			
			$html[] = $lang['entry_gadw'];
			
			$html[] = $lang['entry_prch'];
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'prch_adwid');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['prch_adwid'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwid'], '');
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'prch_adwlbl');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['prch_adwlbl'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwlbl'], '');
			
			$html[] = $lang['entry_bgnchk'];
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'bgnchk_adwid');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwid'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwid'], '');
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'bgnchk_adwlbl');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwlbl'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwlbl'], '');
			
			$html[] = $lang['entry_addtc'];
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'addtc_adwid');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['addtc_adwid'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwid'], '');
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'addtc_adwlbl');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['addtc_adwlbl'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwlbl'], '');
			
			$html[] = $lang['entry_signup'];
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'signup_adwid');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['signup_adwid'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwid'], '');
			
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'signup_adwlbl');
			$val = $data[$this->modname.'_setting'][$store['store_id']]['signup_adwlbl'];
			$html[] = $this->{$this->modvar}->get_InpTxt_html($name, $val, $divcls, $lblcls, $lang['entry_adwlbl'], '');
			
			$html[] = '</div></div>';
		}
		
		if(substr(VERSION,0,3)=='1.5') {
			$this->data['fields_html'] = join($html);
			
			$this->template = $this->modtpl;
			$this->children = array(
				'common/header',
				'common/footer'
			);
			$this->response->setOutput($this->render());
		} else {
			$data['fields_html'] = join($html);
			
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			$this->response->setOutput($this->load->view($this->modtpl, $data));
		}
	}	
	public function save() {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->save();
	}	
	public function install() {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->install();
	}
	public function uninstall() {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->uninstall();
	}
	public function loadjscss(&$route, &$data, &$output = '') {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->loadjscss();
	}
	protected function setvalue($postfield) {
		if (isset($this->request->post[$postfield])) {
			return $this->request->post[$postfield];
		} else {
			return $this->config->get($postfield);
		} 	
	}
	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->modpath)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}