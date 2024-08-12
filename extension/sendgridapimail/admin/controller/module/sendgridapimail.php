<?php
namespace Opencart\Admin\Controller\Extension\Sendgridapimail\module;
class Sendgridapimail extends \Opencart\System\Engine\Controller {
	private $route_extension     = 'extension/sendgridapimail/module/sendgridapimail';
	private $events              = array();
	private $event_code          = 'sendgridapimail';
	
	public function index(): void {
		$this->load->language($this->route_extension);
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->route_extension, 'user_token=' . $this->session->data['user_token'])
		];
		$data['save'] = $this->url->link($this->route_extension . '|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');
		$this->events_init();
        $this->checkEvents();

		$this->load->model('setting/setting');
		$module_setting = $this->model_setting_setting->getSetting('module_sendgridapimail');
		if($module_setting){
			foreach($module_setting as $key=>$pddata){
				$data[$key] = $pddata;
			}
		}
        if($this->request->post){
			foreach($this->request->post as $key=>$pddata){
				$data[$key] = $pddata;
			}
		}
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->route_extension, $data));
	}

	public function save(): void {
		$this->load->language($this->route_extension);
		$json = [];
		if (!$this->user->hasPermission('modify', $this->route_extension)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('module_sendgridapimail', $this->request->post);
			$json['success'] = $this->language->get('text_success');		
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	protected function events_init() {

        $this->events = array(
            'catalog/controller/mail/order/add/after' => $this->route_extension . '|sendgridorderadd',
			'catalog/model/account/customer/editCode/after' => $this->route_extension . '|sendgridforgottenindex',
			//'catalog/view/checkout/cart/before' => $this->route_extension . '|eventCatalogViewCheckoutCartBefore',
        );
    }
	// EVENTS: check
    protected function checkEvents(): void {
		
		$this->load->language($this->route_extension);
        $description = $this->language->get('heading_title');
        $status = true;
        $sort_order = 0;
        $this->load->model('setting/event');
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($this->event_code) . "'");
        $event_info = $query->rows;
        foreach($this->events as $trigger => $action) {
            $event_exist = false;
            foreach ($event_info as $event) {
                if ($event['trigger'] == $trigger && $event['action'] == $action) {
                    $event_exist = true;
                    break;
                }
            }
			if (!$event_exist) {
				$aevent['code'] = $this->event_code;
				$aevent['description'] = $description;
				$aevent['trigger'] = $trigger;
				$aevent['action'] = $action;
				$aevent['status'] = $status;
				$aevent['sort_order'] = $sort_order;

                $this->model_setting_event->addEvent($aevent);
            }
        }
        // delete if not defined
        foreach ($event_info as $event) {
            $event_defined = false;
            foreach($this->events as $trigger => $action) {
                if ($event['trigger'] == $trigger && $event['action'] == $action) {
                    $event_defined = true;
                    break;
                }
            }
            if (!$event_defined) {
                $this->model_setting_event->deleteEvent($event['event_id']);
            }
        }
    }
	public function install(): void {
        //$this->setUsergroupPermissions($this->route_extension);	
		$npath = DIR_CATALOG.'controller/mail/';
		$files = array('forgotten','order','review');
		$search = '$mail = new \Opencart\System\Library\Mail($this->config->get(\'config_mail_engine\'));';
		$replace = '
		if($this->config->get(\'module_sendgridapimail_status\') == 1){
			require_once (DIR_EXTENSION . \'sendgridapimail/system/library/sendgridapimail.php\');
			$mail = new \Opencart\System\Library\Controller\Extension\Sendgridapimail\Sendgridapimail($this->config->get(\'config_mail_engine\'));
		}else{
			$mail = new \Opencart\System\Library\Mail($this->config->get(\'config_mail_engine\'));
		}';
		foreach($files as $file){
			$path = $npath.$file.'.php';
			if (file_exists($path)) {
				$this->replaceInFile($search, $replace, $path);
			}
		}	
		$this->events_init();
        $this->checkEvents();
    }
	public function uninstall(): void {
		$npath = DIR_CATALOG.'controller/mail/';
		$files = array('forgotten','order','review');
		$replace = '$mail = new \Opencart\System\Library\Mail($this->config->get(\'config_mail_engine\'));';
		$search = '
		if($this->config->get(\'module_sendgridapimail_status\') == 1){
			require_once (DIR_EXTENSION . \'sendgridapimail/system/library/sendgridapimail.php\');
			$mail = new \Opencart\System\Library\Controller\Extension\Sendgridapimail\Sendgridapimail($this->config->get(\'config_mail_engine\'));
		}else{
			$mail = new \Opencart\System\Library\Mail($this->config->get(\'config_mail_engine\'));
		}';
		foreach($files as $file){
			$path = $npath.$file.'.php';
			if (file_exists($path)) {
				$this->replaceInFile($search, $replace, $path);
			}
		}
    }
	protected function replaceInFile($search, $replace, $path)
	{
		file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
	}
	
	
}