<?php
namespace Opencart\Admin\Controller\Extension\Faqmanager\Module;
use \Opencart\System\Helper AS Helper;
class Faqmanager extends \Opencart\System\Engine\Controller {
	private $error = array();
	public function index(): void {
		$this->load->language('extension/faqmanager/module/faqmanager');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$token_prefix = 'user_token';
		$modules_url = 'marketplace/extension';

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_sort_order'] = $this->language->get('text_sort_order');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['button_save'] = $this->language->get('button_save');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_description'] = $this->language->get('entry_description');
		
		$data['text_add_section'] = $this->language->get('text_add_section');
		$data['text_groups_heading'] = $this->language->get('text_groups_heading');
		$data['text_section_title'] = $this->language->get('text_section_title');
		$data['text_input_question'] = $this->language->get('text_input_question');
		$data['text_input_answer'] = $this->language->get('text_input_answer');
		
		$data['button_add_section'] = $this->language->get('button_add_section');
		$data['button_add_group'] = $this->language->get('button_add_group');
		$data['button_remove'] = $this->language->get('button_remove'); 
		$data['tab_section'] = $this->language->get('tab_section');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/faqmanager/module/faqmanager', 'user_token=' . $this->session->data['user_token'])
			];
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/faqmanager/module/faqmanager', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
			];
		}
		
		if (!isset($this->request->get['module_id'])) {
			$data['save'] = $this->url->link('extension/faqmanager/module/faqmanager|save', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['save'] = $this->url->link('extension/faqmanager/module/faqmanager|save', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']);
		}
		
		
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');
	
		if (isset($this->request->get['module_id'])) {
			$this->load->model('setting/module');

			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
		        
        if (isset($this->request->post['sections'])) {
			$data['sections'] = $this->request->post['sections'];
		} elseif (!empty($module_info['sections'])) {
			$sections = $module_info['sections'];
		} else {
			$sections = array();
		}
		
		$data['sections'] = array();
		
		foreach ($sections as $section) {
			$groups = array();
			
			$i = 0;
            
			if (isset($section['groups'])) {
				foreach($section['groups'] as $group) {
					$groups[$i] = $group;
					$i++;
				}
				usort($groups, function ($a, $b) { return $a['sort'] - $b['sort']; });
			}
				
			$data['sections'][] = array(
				'title'   => $section['title'],
				'sort'   => $section['sort'],
				'groups'  => $groups,
			);
		}
		
		usort($data['sections'], function ($a, $b) { return $a['sort'] - $b['sort']; });
				
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/faqmanager/faqmanager', $data));
	}

	
	public function save(): void {
		$this->load->language('extension/faqmanager/module/faqmanager');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/faqmanager/module/faqmanager')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if ((strlen($this->request->post['name']) < 3) || (strlen($this->request->post['name']) > 64)) {
			$json['error']['name'] = $this->language->get('error_name');
		}

		if (!$json) {
			$this->load->model('setting/module');

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('faqmanager.faqmanager', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}