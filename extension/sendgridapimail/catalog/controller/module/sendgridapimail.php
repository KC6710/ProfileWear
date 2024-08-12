<?php
namespace Opencart\Catalog\Controller\Extension\Sendgridapimail\module;
class Sendgridapimail extends \Opencart\System\Engine\Controller {
	public function index(): void {

	}
	
	public function sendgridorderadd(&$route, &$data) {
		file_put_contents('sendgrid.txt',print_r($route,1));
		if ($this->config->get('module_sendgridapimail_status')) {
			$minorderamount = $this->config->get('module_sendgridapimail_min_amount');
			$minorderalert = $this->config->get('module_sendgridapimail_alert');
			$minordertype = $this->config->get('module_sendgridapimail_totaltype');
			$comareamount = (isset($minordertype) && $minordertype == 'total'?$this->cart->getTotal():$this->cart->getSubTotal());
			
			$minorderalertmsg = $minorderalert[$this->config->get('config_language')];
			
			if (isset($minorderamount) && $minorderamount > 0){					
				if ($comareamount < $minorderamount){
					$data['error_warning'] = $minorderalertmsg;
				}
			}
				
		}        
    }
	public function sendgridforgottenindex(&$route, &$data) {
		file_put_contents('sendgrid.txt',print_r($route,1));

		if ($this->config->get('module_sendgridapimail_status')) {
			
				
		}        
    }
	
}
