<?php
//==============================================================================
// Redirect Manager v2023-5-11
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

namespace Opencart\Admin\Controller\Extension\RedirectManager\Module;
class RedirectManager extends \Opencart\System\Engine\Controller {

//class ControllerExtensionModuleRedirectManager extends Controller { 
	
	private $type = 'module';
	private $name = 'redirect_manager';
	
	//==============================================================================
	// uninstall()
	//==============================================================================
	public function uninstall() {
		if (version_compare(VERSION, '4.0', '>=')) {
			$this->load->model('setting/event');
			$this->model_setting_event->deleteEventByCode($this->name);
		}
	}
	
	//==============================================================================
	// index()
	//==============================================================================
	public function index() {
		$data = array(
			'type'			=> $this->type,
			'name'			=> $this->name,
			'autobackup'	=> true,
			'save_type'		=> 'keepediting',
			'permission'	=> $this->hasPermission('modify'),
		);
		
		// extension-specific
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->name . "` (
			`" . $this->name . "_id` int(11) NOT NULL AUTO_INCREMENT,
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`from_url` text COLLATE utf8_bin NOT NULL,
			`to_url` text COLLATE utf8_bin NOT NULL,
			`response_code` int(3) NOT NULL DEFAULT '301',
			`date_start` date NOT NULL DEFAULT '0000-00-00',
			`date_end` date NOT NULL DEFAULT '0000-00-00',
			`times_used` int(5) NOT NULL DEFAULT '0',
			PRIMARY KEY (`" . $this->name . "_id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin
		");
		
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->name . "_404` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`date_time` datetime NOT NULL,
			`url` text COLLATE utf8_bin NOT NULL,
			`ip` varchar(40) COLLATE utf8_bin NOT NULL,
			`user_agent` varchar(255) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin
		");
		
		if (version_compare(VERSION, '4.0', '<')) {
			$data['page'] = (isset($this->request->get['page'])) ? (int)$this->request->get['page'] : 1;
			$data['limit'] = $this->config->get('config_limit_admin');
		} else {
			$data['page'] = (isset($this->request->get['page'])) ? (int)$this->request->get['page'] : 1;
			$data['limit'] = $this->config->get('config_pagination_admin');
		}
		// end
		
		$this->loadSettings($data);
		
		// Add Event hooks
		if (version_compare(VERSION, '4.0', '>=')) {
			$this->load->model('setting/event');
			if (!empty($this->request->get['save'])) {
				$this->model_setting_event->deleteEventByCode($this->name);
			}
			$event = $this->model_setting_event->getEventByCode($this->name);
			
			if (empty($event)) {
				$extension_base = 'extension/' . $this->name . '/' . $this->type . '/' . $this->name;
				$separator = (version_compare(VERSION, '4.0.2.0', '<')) ? '|' : '.';
				
				$triggers = array(
					'catalog/controller/common/header/before'	=> $extension_base,
					'catalog/controller/error/not_found/after'	=> $extension_base . $separator . 'record404',
				);
				
				foreach ($triggers as $trigger => $action) {
					$this->model_setting_event->addEvent(array(
						'code'			=> $this->name,
						'description'	=> 'Event hook for the ' . $data['heading_title'] . ' extension by Clear Thinking',
						'trigger'		=> $trigger,
						'action'		=> $action,
						'status'		=> 1,
						'sort_order'	=> 0,
					));
				}
			}
		}
		
		// extension-specific
		if (isset($this->request->get['action'])) {
			if ($this->request->get['action'] == 'reset') {
				$this->db->query("UPDATE `" . DB_PREFIX . $this->name . "` SET times_used = 0");
			} elseif ($this->request->get['action'] == 'delete') {
				$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . $this->name . "`");
			} elseif ($this->request->get['action'] == 'delete404') {
				$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . $this->name . "_404`");
			}
			
			$token = (version_compare(VERSION, '3.0', '<')) ? 'token=' . $this->session->data['token'] : 'user_token=' . $this->session->data['user_token'];
			$this->response->redirect(str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $this->url->link($data['extension_route'], $token, 'SSL')));
		}
		
		foreach ($data['saved'] as &$saved) {
			if ($saved == '0000-00-00') $saved = '';
		}
		
		//------------------------------------------------------------------------------
		// Settings
		//------------------------------------------------------------------------------
		$data['settings'] = array();
		
		$data['settings'][] = array(
			'type'		=> 'tabs',
			'tabs'		=> array('settings_and_redirects', 'page_not_found_404_urls'),
		);
		$data['settings'][] = array(
			'key'		=> 'settings',
			'type'		=> 'heading',
			'buttons'	=> 'backup_restore',
		);
		$data['settings'][] = array(
			'key'		=> 'status',
			'type'		=> 'select',
			'options'	=> array(1 => $data['text_enabled'], 0 => $data['text_disabled']),
			'default'	=> 1,
		);
		$data['settings'][] = array(
			'key'		=> 'check_for_updates',
			'type'		=> 'select',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 0,
		);
		$data['settings'][] = array(
			'key'		=> 'tooltips',
			'type'		=> 'select',
			'options'	=> array(1 => $data['text_enabled'], 0 => $data['text_disabled']),
			'default'	=> 1,
		);
		$data['settings'][] = array(
			'key'		=> 'sorting',
			'type'		=> 'select',
			'options'	=> array(
				'active'		=> $data['column_active'],
				'from_url'		=> str_replace(':', '', $data['text_from_url']),
				'to_url'		=> str_replace(':', '', $data['text_to_url']),
				'response_code'	=> $data['column_response_code'],
				'date_start'	=> $data['column_date_start'],
				'date_end'		=> $data['column_date_end'],
				'times_used'	=> $data['column_times_used'],
			),
			'default'	=> 'from_url'
		);
		$data['settings'][] = array(
			'key'		=> 'filter_from_url',
			'type'		=> 'text',
			'attributes'=> array('style' => 'width: 95% !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'filter_to_url',
			'type'		=> 'text',
			'attributes'=> array('style' => 'width: 95% !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'sort_and_filter',
			'type'		=> 'html',
			'content'	=> '<a class="btn btn-primary" onclick="saveSettings($(this)); setTimeout(function(){ location = \'index.php?route=' . $data['extension_route'] . '&token=' . $data['token'] . '\' }, 2000);"><i class="fa fa-filter pad-right-sm"></i>' . $data['button_sort_and_filter'] . '</a>',
		);
		
		//------------------------------------------------------------------------------
		// Redirects
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'redirects',
			'type'		=> 'heading',
			'buttons'	=> '
				<a class="btn btn-danger" data-help=\'' . $data['help_reset_all'] . '\' onclick="if (confirm(\'' . $data['standard_confirm'] . '\')) location = location + \'&action=reset\'"><i class="fa fa-undo pad-right-sm"></i>' . $data['button_reset_all'] . '</a>
				<a class="btn btn-danger" data-help=\'' . $data['help_delete_all'] . '\' onclick="if (confirm(\'' . $data['standard_confirm'] . '\')) location = location + \'&action=delete\'"><i class="fa fa-trash-o fa-trash-alt pad-right-sm"></i>' . $data['button_delete_all'] . '</a>
			',
		);
		
		if (version_compare(VERSION, '4.0', '<')) {
			$pagination = new Pagination();
			$pagination->total = $data['table_total'];
			$pagination->page = $data['page'];
			$pagination->limit = $data['limit'];
			$pagination->text = $data['text_pagination'];
			$pagination->url = $this->url->link($data['extension_route'], (version_compare(VERSION, '3.0', '<') ? 'token=' : 'user_token=') . $data['token'] . '&page={page}', 'SSL');
			$pagination_html = $pagination->render();
		} else {
			$pagination_html = $this->load->controller('common/pagination', array(
				'total'	=> $data['table_total'],
				'page'	=> $data['page'],
				'limit'	=> $data['limit'],
				'url'	=> $this->url->link($data['extension_route'], 'user_token=' . $data['token'] . '&page={page}'),
			));
		}
		
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="pagination" style="border: none; margin: -10px 0 15px;">' . $pagination_html. '</div>',
		);
		
		$data['settings'][] = array(
			'key'		=> 'redirect_table',
			'type'		=> 'table_start',
			'columns'	=> array('action', 'active', 'redirect', 'response_code', 'date_start', 'date_end', 'times_used'),
			'attributes'=> array('data-autoincrement' => $data['table_autoincrement']),
			'buttons'	=> 'add_row',
		);
		
		foreach ($data['table_ids'] as $id) {
			$prefix = 'redirect_table_' . $id . '_';
			$data['settings'][] = array(
				'type'		=> 'row_start',
			);
			$data['settings'][] = array(
				'type'		=> 'html',
				'content'	=> '<a class="btn btn-danger" onclick="if (!confirm(\'' . $data['standard_confirm'] . '\')) return; element = $(this); $.get(\'index.php?route=' . $data['extension_route'] . '/deleteRow&id=' . $id . '&token=' . $data['token'] . '\', function(data) { if (data) { confirm(data); } else { element.parent().parent().parent().remove(); }});" data-help="' . $data['button_delete'] . '"><i class="fa fa-trash-o fa-trash-alt fa-lg fa-fw"></i></a>',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'active',
				'type'		=> 'select',
				'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
				'default'	=> 1,
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'from_url',
				'type'		=> 'text',
				'attributes'=> array('style' => 'font-size: 11px; width: 400px !important;'),
				'before'	=> '<div style="display: inline-block; text-align: right; width: 60px;">' . $data['text_from_url'] . '</div>',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'to_url',
				'type'		=> 'text',
				'attributes'=> array('style' => 'font-size: 11px; width: 400px !important;'),
				'before'	=> '<div style="display: inline-block; text-align: right; width: 60px;">' . $data['text_to_url'] . '</div>',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'response_code',
				'type'		=> 'select',
				'options'	=> array(
					'301'	=> $data['text_moved_permanently'],
					'302'	=> $data['text_found'],
					'307'	=> $data['text_temporary_redirect'],
				),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'date_start',
				'type'		=> 'date',
				'attributes'=> array('placeholder' => $data['placeholder_date_format'], 'style' => 'width: 140px !important'),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'date_end',
				'type'		=> 'date',
				'attributes'=> array('placeholder' => $data['placeholder_date_format'], 'style' => 'width: 140px !important'),
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'key'		=> $prefix . 'times_used',
				'type'		=> 'text',
				'attributes'=> array('style' => 'width: 50px !important'),
			);
			$data['settings'][] = array(
				'type'		=> 'row_end',
			);
		}
		
		$data['settings'][] = array(
			'type'		=> 'table_end',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="pagination" style="border: none;">' . $pagination_html . '</div>',
		);
		
		//------------------------------------------------------------------------------
		// 404 URLs
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'page_not_found_404_urls',
			'type'		=> 'tab',
			'active'	=> !empty($this->request->get['showall']),
		);
		
		$data['settings'][] = array(
			'key'		=> 'settings',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'record_404s',
			'type'		=> 'select',
			'options'	=> array(1 => $data['text_enabled'], 0 => $data['text_disabled']),
			'default'	=> 1,
		);
		$data['settings'][] = array(
			'key'		=> 'ignore_ips',
			'type'		=> 'textarea',
			'attributes'=> array('style' => 'width: 100% !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'ignore_user_agents',
			'type'		=> 'textarea',
			'attributes'=> array('style' => 'width: 100% !important'),
		);
		
		// 404 URLs
		if (empty($this->request->get['showall'])) {
			$showall_button = '<a class="btn btn-default btn-light" data-help=\'' . $data['help_show_all_404_urls'] . '\' onclick="if (confirm(\'' . $data['standard_confirm'] . '\')) location = location + \'&showall=true\'"><i class="fa fa-eye pad-right-sm"></i>' . $data['button_show_all_404_urls'] . '</a>';
		} else {
			$showall_button = '<a class="btn btn-default btn-light" href="index.php?route=' . $data['extension_route'] . '&token=' . $data['token'] . '"><i class="fa fa-eye pad-right-sm"></i>' . $data['button_show_last_100_urls'] . '</a>';
		}
		
		$data['settings'][] = array(
			'key'		=> 'page_not_found_404_urls',
			'type'		=> 'heading',
			'buttons'	=> $showall_button . '
				<a class="btn btn-default btn-light" href="index.php?route=' . $data['extension_route'] . '/download404&token=' . $data['token'] . '"><i class="fa fa-download pad-right-sm"></i>' . $data['button_download_404_list'] . '</a>
				<a class="btn btn-danger" onclick="if (confirm(\'' . $data['standard_confirm'] . '\')) location = location + \'&action=delete404\'"><i class="fa fa-trash-o fa-trash-alt pad-right-sm"></i>' . $data['button_delete_all'] . '</a>
			',
		);
		
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '
			<style type="text/css">
				.table {
					-webkit-user-select: text;
					-moz-user-select: text;
					-ms-user-select: text;
					user-select: text;
				}
			</style>
			
			<div id="redirect-404-modal" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">' . $data['button_add_row'] . '</h5>
							<button type="button" class="close btn-close" data-dismiss="modal" data-bs-dismiss="modal"></button>
						</div>
						<div class="modal-body">
							<div style="width: 120px; display: inline-block"><b>' . $data['text_from_url'] . '</b></div>
							<span id="404-url"></span>
							<br><br>
							
							<div style="width: 120px; display: inline-block"><b>' . $data['text_to_url'] . '</b></div>
							<textarea id="to-url" class="form-control" style="width: 400px !important"></textarea>
							<br><br>
							
							<div style="width: 120px; display: inline-block"><b>' . $data['column_response_code'] . ':</b></div>
							<select class="form-control" id="response-code">
								<option value="301">' . $data['text_moved_permanently'] . '</option>
								<option value="302">' . $data['text_found'] . '</option>
								<option value="307">' . $data['text_temporary_redirect'] . '</option>
							</select>
							<br><br>
							
							<a class="btn btn-primary" style="margin-left: 123px" onclick="if (confirm(\'' . $data['help_add_redirect_delete_404'] . '\')) redirect404($(this))">' . $data['button_add_row'] . '</a>
							<br><br>
							
							<div class="text-center"><em>' . $data['help_you_will_need_to_reload'] . '</em></div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default btn-light" data-dismiss="modal" data-bs-dismiss="modal"><i class="fa fa-times pad-right-sm"></i> ' . $data['button_cancel'] . '</button>
						</div>
					</div>
				</div>
			</div>
			
			<script>
				function redirect404(element) {
					$.ajax({
						type: "POST",
						url: "index.php?route=' . $data['extension_route'] . '/redirect404&token=' . $data['token'] . '",
						data: {from_url: $("#404-url").text(), to_url: $("#to-url").val(), response_code: $("#response-code").val()},
						beforeSend: function() {
							element.attr("disabled", "disabled").html("' . $data['standard_please_wait'] . '");
						},
						success: function(data) {
							if (data) {
								confirm(data);
							} else {
								confirm("' . $data['standard_success'] . '");
								$("#redirect-404-modal").modal("hide");
								$("#page_not_found_404_urls a[href=\'" + $("#404-url").text() + "\']").parents("tr").remove();
							}
							element.removeAttr("disabled").html("' . $data['button_add_row'] . '");
						}
					});
				}
			</script>
			
			<br>
			',
		);		
		
		$urls = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->name . "_404` ORDER BY date_time DESC " . (empty($this->request->get['showall']) ? "LIMIT 100" : ""))->rows;
		$data['settings'][] = array(
			'key'		=> '404_url',
			'type'		=> 'table_start',
			'columns'	=> array('action', 'date_time', 'url', 'ip', 'user_agent'),
		);
		foreach ($urls as $url) {
			$data['settings'][] = array(
				'type'		=> 'row_start',
			);
			$data['settings'][] = array(
				'type'		=> 'html',
				'content'	=> '
					<a class="btn btn-danger" onclick="if (!confirm(\'' . $data['standard_confirm'] . '\')) return; element = $(this); $.get(\'index.php?route=' . $data['extension_route'] . '/delete404&id=' . $url['id'] . '&token=' . $data['token'] . '\', function(data) { if (data) { confirm(data); } else { element.parent().parent().parent().remove(); }});" data-help="' . $data['button_delete'] . '"><i class="fa fa-trash-o fa-trash-alt fa-lg fa-fw"></i></a>
					<a class="btn btn-primary" href="#redirect-404-modal" data-toggle="modal" data-bs-toggle="modal" data-help="' . $data['button_add_row'] . '" onclick="$(\'#404-url\').html(\'' . $url['url'] . '\')"><i class="fa fa-share fa-lg fa-fw"></i></a>
				',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'type'		=> 'html',
				'content'	=> $url['date_time'],
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'type'		=> 'html',
				'content'	=> '<div style="word-wrap: break-word; width: 400px"><a target="_blank" href="' . $url['url'] . '">' . $url['url'] . '</a></div>',
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'type'		=> 'html',
				'content'	=> $url['ip'],
			);
			$data['settings'][] = array(
				'type'		=> 'column',
			);
			$data['settings'][] = array(
				'type'		=> 'html',
				'content'	=> '<div style="width: 400px">' . $url['user_agent'] . '</div>',
			);
			$data['settings'][] = array(
				'type'		=> 'row_end',
			);
		}
		$data['settings'][] = array(
			'type'		=> 'table_end',
		);
		
		//------------------------------------------------------------------------------
		// end settings
		//------------------------------------------------------------------------------
		
		$this->document->setTitle($data['heading_title']);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		if (version_compare(VERSION, '4.0', '<')) {
			$template_file = DIR_TEMPLATE . 'extension/' . $this->type . '/' . $this->name . '.twig';
		} elseif (defined('DIR_EXTENSION')) {
			$template_file = DIR_EXTENSION . $this->name . '/admin/view/template/' . $this->type . '/' . $this->name . '.twig';
		}
		
		if (is_file($template_file)) {
			// extension-specific
			$saved_settings = array();
			foreach ($data['saved'] as $key => $value) {
				$saved_settings[$key] = $value;
			}
			
			extract($data);
			$saved = $saved_settings;
			// end
			
			ob_start();
			if (version_compare(VERSION, '4.0', '<')) {
				require(class_exists('VQMod') ? \VQMod::modCheck(modification($template_file)) : modification($template_file));
			} else {
				require(class_exists('VQMod') ? \VQMod::modCheck($template_file) : $template_file);
			}
			$output = ob_get_clean();
			
			if (version_compare(VERSION, '3.0', '>=')) {
				$output = str_replace(array('&token=', '&amp;token='), '&user_token=', $output);
			}
			
			if (version_compare(VERSION, '4.0', '>=')) {
				$separator = (version_compare(VERSION, '4.0.2.0', '<')) ? '|' : '.';
				$output = str_replace($data['extension_route'] . '/', $data['extension_route'] . $separator, $output);
			}
			
			echo $output;
		} else {
			echo 'Error loading template file: ' . $template_file;
		}
	}
	
	//==============================================================================
	// Helper functions
	//==============================================================================
	private function hasPermission($permission) {
		if (version_compare(VERSION, '2.3', '<')) {
			return $this->user->hasPermission($permission, $this->type . '/' . $this->name);
		} elseif (version_compare(VERSION, '4.0', '<')) {
			return $this->user->hasPermission($permission, 'extension/' . $this->type . '/' . $this->name);
		} else {
			return $this->user->hasPermission($permission, 'extension/' . $this->name . '/' . $this->type . '/' . $this->name);
		}
	}
	
	private function loadLanguage($path) {
		$_ = array();
		$language = array();
		if (version_compare(VERSION, '2.2', '<')) {
			$admin_language = $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE `code` = '" . $this->db->escape($this->config->get('config_admin_language')) . "'")->row['directory'];
		} elseif (version_compare(VERSION, '4.0', '<')) {
			$admin_language = $this->config->get('config_admin_language');
		} else {
			$admin_language = $this->config->get('config_language_admin');
		}
		foreach (array('english', 'en-gb', $admin_language) as $directory) {
			$file = DIR_LANGUAGE . $directory . '/' . $directory . '.php';
			if (file_exists($file)) require(class_exists('VQMod') ? \VQMod::modCheck($file) : $file);
			$file = DIR_LANGUAGE . $directory . '/default.php';
			if (file_exists($file)) require(class_exists('VQMod') ? \VQMod::modCheck($file) : $file);
			$file = DIR_LANGUAGE . $directory . '/' . $path . '.php';
			if (file_exists($file)) require(class_exists('VQMod') ? \VQMod::modCheck($file) : $file);
			$file = DIR_LANGUAGE . $directory . '/extension/' . $path . '.php';
			if (file_exists($file)) require(class_exists('VQMod') ? \VQMod::modCheck($file) : $file);
			if (defined('DIR_EXTENSION')) {
				$file = DIR_EXTENSION . 'opencart/admin/language/' . $directory . '/' . $path . '.php';
				if (file_exists($file)) require(class_exists('VQMod') ? \VQMod::modCheck($file) : $file);
				$explode = explode('/', $path);
				$file = DIR_EXTENSION . $explode[1] . '/admin/language/' . $directory . '/' . $path . '.php';
				if (file_exists($file)) require(class_exists('VQMod') ? \VQMod::modCheck($file) : $file);
				$file = DIR_EXTENSION . $this->name . '/admin/language/' . $directory . '/' . $path . '.php';
				if (file_exists($file)) require(class_exists('VQMod') ? \VQMod::modCheck($file) : $file);
			}
			$language = array_merge($language, $_);
		}
		return $language;
	}
	
	private function getTableRowNumbers(&$data, $table, $sorting) {
		$groups = array();
		$rules = array();
		
		foreach ($data['saved'] as $key => $setting) {
			if (preg_match('/' . $table . '_(\d+)_' . $sorting . '/', $key, $matches)) {
				$groups[$setting][] = $matches[1];
			}
			if (preg_match('/' . $table . '_(\d+)_rule_(\d+)_type/', $key, $matches)) {
				$rules[$matches[1]][] = $matches[2];
			}
		}
		
		if (empty($groups)) $groups = array('' => array('1'));
		ksort($groups, defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
		
		foreach ($rules as $key => $rule) {
			ksort($rules[$key], defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
		}
		
		$data['used_rows'][$table] = array();
		$rows = array();
		foreach ($groups as $group) {
			foreach ($group as $num) {
				$data['used_rows'][preg_replace('/module_(\d+)_/', '', $table)][] = $num;
				$rows[$num] = (empty($rules[$num])) ? array() : $rules[$num];
			}
		}
		sort($data['used_rows'][$table]);
		
		return $rows;
	}
	
	//==============================================================================
	// Setting functions (custom)
	//==============================================================================
	private $encryption_key = '';
	
	public function loadSettings(&$data) {
		$backup_type = (empty($data)) ? 'manual' : 'auto';
		if ($backup_type == 'manual' && !$this->hasPermission('modify')) {
			return;
		}
		
		$this->cache->delete($this->name);
		unset($this->session->data[$this->name]);
		$code = (version_compare(VERSION, '3.0', '<') ? '' : $this->type . '_') . $this->name;
		
		// Set URL data
		$data['token'] = $this->session->data[version_compare(VERSION, '3.0', '<') ? 'token' : 'user_token'];
		$data['exit'] = $this->url->link((version_compare(VERSION, '3.0', '<') ? 'extension' : 'marketplace') . '/' . (version_compare(VERSION, '2.3', '<') ? '' : 'extension&type=') . $this->type . '&token=' . $data['token'], '', 'SSL');
		$data['extension_route'] = 'extension/' . (version_compare(VERSION, '4.0', '<') ? '' : $this->name . '/') . $this->type . '/' . $this->name;
		
		// Load saved settings
		$data['saved'] = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$key = str_replace($code . '_', '', $setting['key']);
			$value = $setting['value'];
			if ($setting['serialized']) {
				$value = (version_compare(VERSION, '2.1', '<')) ? unserialize($setting['value']) : json_decode($setting['value'], true);
			}
			
			$data['saved'][$key] = $value;
			
			if (is_array($value)) {
				foreach ($value as $num => $value_array) {
					foreach ($value_array as $k => $v) {
						$data['saved'][$key . '_' . $num . '_' . $k] = $v;
					}
				}
			}
		}
		
		// extension-specific
		$where = '';
		foreach ($data['saved'] as $key => $value) {
			$parts = explode('_', $key, 2);
			if ($parts[0] == 'filter') {
				$where .= (empty($where) ? " WHERE " : " AND ") . $parts[1] . " LIKE '%" . $this->db->escape(str_replace('%', '\%', $value)) . "%'";
			}
		}
		$sorting = (isset($data['saved']['sorting'])) ? " ORDER BY " . $data['saved']['sorting'] : '';
		$limit = (isset($data['page']) && isset($data['limit'])) ? " LIMIT " . (((int)$data['page']-1) * (int)$data['limit']) . "," . (int)$data['limit'] : '';
		
		$table_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->name . "`" . $where . $sorting . $limit);
		
		$data['table_autoincrement'] = $this->db->query("SELECT MAX(" . $this->name . "_id) AS autoincrement FROM `" . DB_PREFIX . $this->name . "`")->row['autoincrement'] + 1;
		$data['table_total'] = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . $this->name . "`" . $where)->row['total'];
		$data['table_ids'] = array();
		
		foreach ($table_query->rows as $row) {
			$data['table_ids'][] = $row[$this->name . '_id'];
			foreach ($row as $key => $value) {
				$data['saved']['redirect_table_' . $row[$this->name . '_id'] . '_' . $key] = $value;
			}
		}
		
		if (empty($data['table_ids'])) {
			foreach ($this->db->query("DESCRIBE `" . DB_PREFIX . $this->name . "`")->rows as $column) {
				$data['saved']['redirect_table_1_' . $column['Field']] = '';
			}
			$data['table_ids'][] = 1;
			$data['table_autoincrement'] = 2;
		}
		
		// Load language and run standard checks
		$data = array_merge($data, $this->loadLanguage($this->type . '/' . $this->name));
		
		if (ini_get('max_input_vars') && ((ini_get('max_input_vars') - count($data['saved'])) < 50)) {
			$data['warning'] = $data['standard_max_input_vars'];
		}
		
		// Modify files according to OpenCart version
		if ($this->type == 'total') {
			if (version_compare(VERSION, '2.2', '<')) {
				$filepath = DIR_CATALOG . 'model/' . $this->type . '/' . $this->name . '.php';
				file_put_contents($filepath, str_replace('public function getTotal($total) {', 'public function getTotal(&$total_data, &$order_total, &$taxes) {' . "\n\t\t" . '$total = array("totals" => &$total_data, "total" => &$order_total, "taxes" => &$taxes);', file_get_contents($filepath)));
			} elseif (defined('DIR_EXTENSION')) {
				$filepath = DIR_EXTENSION . $this->name . '/catalog/model/' . $this->type . '/' . $this->name . '.php';
				file_put_contents($filepath, str_replace('public function getTotal($total_input) {', 'public function getTotal(&$total_data, &$taxes, &$order_total) {', file_get_contents($filepath)));
			}
		}
		
		if (version_compare(VERSION, '2.3', '>=')) {
			$filepaths = array(
				DIR_APPLICATION . 'controller/' . $this->type . '/' . $this->name . '.php',
				DIR_CATALOG . 'controller/' . $this->type . '/' . $this->name . '.php',
				DIR_CATALOG . 'model/' . $this->type . '/' . $this->name . '.php',
			);
			foreach ($filepaths as $filepath) {
				if (file_exists($filepath)) {
					rename($filepath, str_replace('.php', '.php-OLD', $filepath));
				}
			}
		}
		
		if (version_compare(VERSION, '4.0', '>=')) {
			$extension_install_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension_install WHERE `code` = '" . $this->db->escape($this->name) . "'");
			if ($extension_install_query->row['version'] == 'unlicensed') {
				$this->db->query("UPDATE " . DB_PREFIX . "extension_install SET version = '" . $this->db->escape($data['version']) . "' WHERE `code` = '" . $this->db->escape($this->name) . "'");
			}
		}
		
		// Set save type and skip auto-backup if not needed
		if (!empty($data['saved']['autosave'])) {
			$data['save_type'] = 'auto';
		}
		
		if ($backup_type == 'auto' && empty($data['autobackup'])) {
			return;
		}
		
		// Create settings auto-backup file
		$table_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->name . "`");
		
		$manual_filepath = DIR_LOGS . $this->name . $this->encryption_key . '.backup';
		$auto_filepath = DIR_LOGS . $this->name . $this->encryption_key . '.autobackup';
		$filepath = ($backup_type == 'auto') ? $auto_filepath : $manual_filepath;
		if (file_exists($filepath)) unlink($filepath);
		
		file_put_contents($filepath, strtoupper(implode(',', array_keys($table_query->row))) . "\n", FILE_APPEND|LOCK_EX);
		
		foreach ($table_query->rows as $row) {
			file_put_contents($filepath, implode(',', array_values(str_replace(',', '‚', $row))) . "\n", FILE_APPEND|LOCK_EX);
		}
		
		$data['autobackup_time'] = date('Y-M-d @ g:i a');
		$data['backup_time'] = (file_exists($manual_filepath)) ? date('Y-M-d @ g:i a', filemtime($manual_filepath)) : '';
		
		if ($backup_type == 'manual') {
			echo $data['autobackup_time'];
		}
	}
	
	public function saveSettings() {
		if (!$this->hasPermission('modify')) {
			echo 'PermissionError';
			return;
		}
		
		foreach ($this->request->post as $key => $value) {
			if (strpos($key, 'redirect_table_') === 0) {
				$parts = explode('_', $key, 4);
				$sql = $this->db->escape($parts[3]) . " = '" . $this->db->escape(stripslashes(is_array($value) ? implode(';', $value) : trim($value))) . "'";
				$this->db->query("INSERT INTO `" . DB_PREFIX . $this->name . "` SET " . $this->name . "_id = " . (int)$parts[2] . ", " . $sql . " ON DUPLICATE KEY UPDATE " . $sql);
			} else {
				$code = (version_compare(VERSION, '3.0', '<') ? '' : $this->type . '_') . $this->name;
				$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($code) . "'AND `key` = '" . $this->db->escape($code . '_' . $key) . "'");
				$this->db->query("
					INSERT INTO " . DB_PREFIX . "setting SET
					`store_id` = 0,
					`code` = '" . $this->db->escape($code) . "',
					`key` = '" . $this->db->escape($code . '_' . $key) . "',
					`value` = '" . $this->db->escape(stripslashes(is_array($value) ? implode(';', $value) : $value)) . "',
					`serialized` = 0
				");
			}
		}
	}
	
	//==============================================================================
	// checkVersion()
	//==============================================================================
	public function checkVersion() {
		$data = $this->loadLanguage($this->type . '/' . $this->name);
		
		$curl = curl_init('https://www.getclearthinking.com/downloads/checkVersion?extension=' . urlencode($data['heading_title']));
		curl_setopt_array($curl, array(
			CURLOPT_CONNECTTIMEOUT	=> 10,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_TIMEOUT			=> 10,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		
		echo $response;
	}
	
	//==============================================================================
	// update()
	//==============================================================================
	public function update() {
		$data = $this->loadLanguage($this->type . '/' . $this->name);
		
		$curl = curl_init('https://www.getclearthinking.com/downloads/update?extension=' . urlencode($data['heading_title']) . '&domain=' . $this->request->server['HTTP_HOST'] . '&key=' . $this->request->post['license_key']);
		curl_setopt_array($curl, array(
			CURLOPT_CONNECTTIMEOUT	=> 10,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_TIMEOUT			=> 10,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		
		if (strpos($response, '<i') === 0) {
			echo $response;
			return;
		}
		
		$first_zip = DIR_DOWNLOAD . 'clearthinking.zip';
		$file = fopen($first_zip, 'w+');
		fwrite($file, $response);
		fclose($file);
		
		$temp_directory = DIR_DOWNLOAD . 'clearthinking/';
		$zip = new \ZipArchive();

		if ($zip->open($first_zip)) {
			$zip->extractTo($temp_directory);
			$zip->close();
		} else {
			echo 'Zip archive failed to unzip';
			return;
		}
		
		@unlink($first_zip);
		
		if (version_compare(VERSION, '2.0', '<')) {
			$second_zip = $temp_directory . 'OpenCart 1.5 Versions.zip';
		} elseif (version_compare(VERSION, '2.3', '<')) {
			$second_zip = $temp_directory . 'OpenCart 2.0-2.2 Versions.ocmod.zip';
		} elseif (version_compare(VERSION, '4.0', '<')) {
			$second_zip = $temp_directory . 'OpenCart 2.3-3.0 Versions.ocmod.zip';
		} else {
			$second_zip = $temp_directory . 'OpenCart 4.0 Versions.zip';
		}
		
		$zip = new \ZipArchive();
		
		if (version_compare(VERSION, '4.0', '<')) {
			if ($zip->open($second_zip)) {
				$admin_directory = basename(DIR_APPLICATION);
				
				for ($i = 0; $i < $zip->numFiles; $i++) {
					$filepath = str_replace(array('upload/', 'admin/'), array('', $admin_directory . '/'), $zip->getNameIndex($i));
					
					if (strpos($filepath, '.txt')) {
						continue;
					}
					
					if ($filepath === 'install.xml') {
						$xml = $zip->getFromIndex($i);
						
						foreach (array('name', 'code', 'version', 'author', 'link') as $tag) {
							$first_explosion = explode('<' . $tag . '>', $xml);
							$second_explosion = explode('</' . $tag . '>', $first_explosion[1]);
							${'xml_'.$tag} = $second_explosion[0];
						}
						
						$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE code = '" . $this->db->escape($xml_code) . "'");
						
						$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($xml_code) . "', name = '" . $this->db->escape($xml_name) . "', author = '" . $this->db->escape($xml_author) . "', version = '" . $this->db->escape($xml_version) . "', link = '" . $this->db->escape($xml_link) . "', xml = '" . $this->db->escape($xml) . "', status = 1, date_added = NOW()");
						
						continue;
					}
					
					$full_filepath = DIR_APPLICATION . '../' . $filepath;
					
					if (!strpos($filepath, '.')) {
						if (!is_dir($full_filepath)) {
							mkdir($full_filepath, 0777);
						}
						continue;
					}
					
					file_put_contents($full_filepath, $zip->getFromIndex($i));
				}
				
				$zip->close();
			} else {
				echo 'Zip archive failed to unzip';
				return;
			}
		} else {
			if ($zip->open($second_zip)) {
				$zip->extractTo($temp_directory);
				$zip->close();
			} else {
				echo 'Zip archive failed to unzip';
				return;
			}
			
			$third_zip = $temp_directory . $this->name . '.ocmod.zip';
			$zip = new \ZipArchive();
			
			if ($zip->open($third_zip)) {
				$zip->extractTo(DIR_EXTENSION . $this->name . '/');
				$zip->close();
			} else {
				echo 'Zip archive failed to unzip';
				return;
			}
			
			@unlink($third_zip);
		}
		
		if (file_exists($temp_directory . 'instructions.txt'))						@unlink($temp_directory . 'instructions.txt');
		if (file_exists($temp_directory . 'license.txt'))							@unlink($temp_directory . 'license.txt');
		if (file_exists($temp_directory . 'releasenotes.txt'))						@unlink($temp_directory . 'releasenotes.txt');
		if (file_exists($temp_directory . 'OpenCart 1.5 Versions.zip'))				@unlink($temp_directory . 'OpenCart 1.5 Versions.zip');
		if (file_exists($temp_directory . 'OpenCart 2.0-2.2 Versions.ocmod.zip'))	@unlink($temp_directory . 'OpenCart 2.0-2.2 Versions.ocmod.zip');
		if (file_exists($temp_directory . 'OpenCart 2.3-3.0 Versions.ocmod.zip'))	@unlink($temp_directory . 'OpenCart 2.3-3.0 Versions.ocmod.zip');
		if (file_exists($temp_directory . 'OpenCart 4.0 Versions.zip'))				@unlink($temp_directory . 'OpenCart 4.0 Versions.zip');
		if (is_dir($temp_directory))												@rmdir($temp_directory);
		
		echo 'success';
	}
	
	//==============================================================================
	// Backup functions
	//==============================================================================
	public function backupSettings() {
		$data = array();
		$this->loadSettings($data);
	}
	
	public function viewBackup() {
		if (!$this->hasPermission('access')) {
			echo 'You do not have permission to view this file.';
			return;
		}
		if (!file_exists(DIR_LOGS . $this->name . $this->encryption_key . '.backup')) {
			echo 'Backup file does not exist';
			return;
		}
		
		$contents = trim(file_get_contents(DIR_LOGS . $this->name . $this->encryption_key . '.backup'));
		$lines = explode("\n", $contents);
		
		$html = '<table border="1" style="font-family: monospace" cellspacing="0" cellpadding="5">';
		foreach ($lines as $line) {
			$html .= '<tr><td>' . implode('</td><td>', explode(",", $line)) . '</td></tr>';
		}
		echo str_replace('<td></td>', '<td style="background: #DDD"></td>', $html) . '</table>';
	}
	
	public function downloadBackup() {
		$file = DIR_LOGS . $this->name . $this->encryption_key . '.backup';
		if (!$this->hasPermission('access') || !file_exists($file)) {
			return;
		}
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . $this->name . '.' . date('Y-n-d') . '.csv');
		header('Content-Length: ' . filesize($file));
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: text/plain');
		header('Expires: 0');
		header('Pragma: public');
		readfile($file);
	}
	
	public function restoreSettings() {
		$data = $this->loadLanguage($this->type . '/' . $this->name);
		$token = (version_compare(VERSION, '3.0', '<')) ? 'token' : 'user_token';
		$extension_route = 'extension/' . (version_compare(VERSION, '4.0', '<') ? '' : $this->name . '/') . $this->type . '/' . $this->name;
		
		if (!$this->hasPermission('modify')) {
			$this->session->data['error'] = $data['standard_error'];
			$this->response->redirect(str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $this->url->link($extension_route, $token . '=' . $this->session->data[$token], 'SSL')));
		}
		
		if ($this->request->post['from'] == 'auto') {
			$filepath = DIR_LOGS . $this->name . $this->encryption_key . '.autobackup';
		} elseif ($this->request->post['from'] == 'manual') {
			$filepath = DIR_LOGS . $this->name . $this->encryption_key . '.backup';
		} elseif ($this->request->post['from'] == 'file') {
			$filepath = $this->request->files['backup_file']['tmp_name'];
			if (empty($filepath)) {
				$this->session->data['error'] = 'File is empty or not present';
				$this->response->redirect(str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $this->url->link($extension_route, $token . '=' . $this->session->data[$token], 'SSL')));
			}
		}
		
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . $this->name . "`");
		$contents = str_replace("\r\n", "\n", trim(file_get_contents($filepath)));
		
		foreach (explode("\n", str_replace('"', '', $contents)) as $num => $row) {
			if (empty($row)) continue;
			
			if (!$num) {
				$columns = explode(',', strtolower($row));
				continue;
			}
			
			$sql = array();
			foreach (explode(',', $row) as $index => $col) {
				if (!$col) continue;
				$sql[] = $columns[$index] . " = '" . $this->db->escape(str_replace('‚', ',', $col)) . "'";
			}
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . $this->name . "` SET " . implode(', ', $sql));
		}
		
		$this->session->data['success'] = $data['text_settings_restored'];
		$this->response->redirect(str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $this->url->link($extension_route, $token . '=' . $this->session->data[$token], 'SSL')));
	}
	
	//==============================================================================
	// Custom functions
	//==============================================================================
	public function deleteRow() {
		$this->db->query("DELETE FROM `" . DB_PREFIX . $this->name . "` WHERE " . $this->name . "_id = " . (int)$this->request->get['id']);
	}
	
	public function delete404() {
		$this->db->query("DELETE FROM `" . DB_PREFIX . $this->name . "_404` WHERE id = " . (int)$this->request->get['id']);
	}
	
	public function redirect404() {
		if (empty($this->request->post) || empty($this->request->post['to_url'])) {
			echo 'Please fill in a valid "To URL"';
		} else {
			$post = $this->request->post;
			$this->db->query("INSERT INTO `" . DB_PREFIX . $this->name . "` SET active = 1, from_url = '" . $this->db->escape($post['from_url']) . "', to_url = '" . $this->db->escape(trim($post['to_url'])) . "', response_code = " . (int)$post['response_code']);
			$this->db->query("DELETE FROM `" . DB_PREFIX . $this->name . "_404` WHERE url = '" . $this->db->escape($post['from_url']) . "'");
		}
	}
	
	public function download404() {
		if (!$this->hasPermission('access')) {
			return;
		}
		
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . $this->name . '_404.' . date('Y-n-d') . '.txt');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: text/plain');
		header('Expires: 0');
		header('Pragma: public');
		
		$columns = array(
			'ID',
			'DATE_TIME',
			'IP',
			'URL',
			'USER_AGENT',
		);
		echo implode("\t", $columns) . "\n";
		
		$table_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->name . "_404`");
		foreach ($table_query->rows as $row) {
			echo $row['id'] . "\t" . $row['date_time'] . "\t" . $row['ip'] . "\t" . $row['url'] . "\t" . '"' . $row['user_agent'] . '"' . "\n";
		}
	}
}
?>