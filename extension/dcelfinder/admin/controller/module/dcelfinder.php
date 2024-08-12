<?php
namespace Opencart\Admin\Controller\Extension\Dcelfinder\Module;
use elFinderConnector;
use elFinder;
class Dcelfinder extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/dcelfinder/module/dcelfinder');

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
			'href' => $this->url->link('extension/dcelfinder/module/dcelfinder', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/dcelfinder/module/dcelfinder.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		$module_installed = $this->model_setting_setting->getSetting('module_dcelfinder');

		$data['module_dcelfinder_status'] = $this->config->get('module_dcelfinder_status');

		if ($module_installed) {
			$data['module_dcelfinder_click'] = $this->config->get('module_dcelfinder_click');
		} else {
			$data['module_dcelfinder_click'] = 2;
		}

		if ($module_installed) {
			$data['module_dcelfinder_watermark'] = $this->config->get('module_dcelfinder_watermark');
		} else {
			$data['module_dcelfinder_watermark'] = [
				'status' => 0,
				'image' => '',
				'horizontal_selection' => 'right',
				'horizontal_px' => 5,
				'vertical_selection' => 'bottom',
				'vertical_px' => 5,
				'left' => false,
				'top' => false,
				'transparency' => 70
			];
		}

		if ($this->config->get('module_dcelfinder_watermark')) {
			$data['image'] = $this->config->get('module_dcelfinder_watermark')['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if ($this->config->get('module_dcelfinder_watermark') && is_file(DIR_IMAGE . $this->config->get('module_dcelfinder_watermark')['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->config->get('module_dcelfinder_watermark')['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['elfinder_url'] = $this->url->link('extension/dcelfinder/module/dcelfinder.imagemanager', 'user_token='. $this->session->data['user_token'], true);

		$common_modified = false;

		$ckeditor_modified = false;

		$js_files = [
			'common' => 'view/javascript/common.js',
			'ckeditor' => 'view/javascript/ckeditor/config.js',
		];

		$modified_files = [];

		foreach ($js_files as $file_name => $target_file) {
			$target_file = DIR_APPLICATION . $target_file;

			if (is_file($target_file) && stristr(file_get_contents($target_file), 'elfinder')) {
				$modified_files[] = $file_name;
			}
		}

		$data['js_modified'] = in_array('common', $modified_files) && in_array('ckeditor', $modified_files);
		$data['common_js'] = $js_files['common'];
		$data['ckeditor_js'] = $js_files['ckeditor'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['view'] = 'module';

		$this->response->setOutput($this->load->view('extension/dcelfinder/module/dcelfinder', $data));
	}

	public function productform(string $route = '', array $data = [], string &$output = ''): void {
		if ($this->config->get('module_dcelfinder_status')) {
			$this->load->language('extension/dcelfinder/module/dcelfinder');

			$find = '<footer';

			$info = [];

			$info['dir_image_url'] = HTTP_CATALOG . 'image/';

			$info['view'] = 'product';

			$replace = $this->load->view('extension/dcelfinder/module/dcelfinder', $info);

			$output = str_replace($find, $replace . $find, $output);
		}
	}

	public function afterheader(string $route = '', array $data = [], string &$output = ''): void {
		if (isset($this->request->get['user_token']) && isset($this->session->data['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
			if ($this->config->get('module_dcelfinder_status')) {
				$find1 = '<script src="view/javascript/jquery/jquery-3.6.1.min.js" type="text/javascript"></script>';

				$replace1 = '
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/jquery/jquery-ui-1.12.0.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/commands.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/common.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/contextmenu.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/cwd.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/dialog.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/fonts.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/navbar.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/places.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/quicklook.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/statusbar.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/theme.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/toast.css">
					<link rel="stylesheet" href="../extension/dcelfinder/admin/view/assets/css/toolbar.css">
					<script src="../extension/dcelfinder/admin/view/assets/jquery/jquery-ui-1.12.0.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.version.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/jquery.elfinder.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.mimetypes.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.options.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.options.netmount.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.history.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.command.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/elFinder.resources.js"></script>

					<script src="../extension/dcelfinder/admin/view/assets/js/jquery.dialogelfinder.js"></script>

					<!-- elfinder default lang -->
					<script src="../extension/dcelfinder/admin/view/assets/js/i18n/elfinder.en.js"></script>

					<!-- elfinder ui -->
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/button.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/contextmenu.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/cwd.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/dialog.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/fullscreenbutton.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/navbar.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/navdock.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/overlay.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/panel.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/path.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/places.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/searchbutton.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/sortbutton.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/stat.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/toast.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/toolbar.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/tree.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/uploadButton.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/viewbutton.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/ui/workzone.js"></script>

					<!-- elfinder commands -->
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/archive.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/back.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/chmod.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/colwidth.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/copy.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/cut.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/download.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/duplicate.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/edit.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/empty.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/extract.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/forward.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/fullscreen.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/getfile.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/help.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/hidden.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/hide.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/home.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/info.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/mkdir.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/mkfile.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/netmount.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/open.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/opendir.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/opennew.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/paste.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/places.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/preference.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/quicklook.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/quicklook.plugins.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/reload.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/rename.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/resize.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/restore.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/rm.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/search.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/selectall.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/selectinvert.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/selectnone.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/sort.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/undo.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/up.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/upload.js"></script>
					<script src="../extension/dcelfinder/admin/view/assets/js/commands/view.js"></script>

					<!-- elfinder 1.x connector API support (OPTIONAL) -->
					<script src="../extension/dcelfinder/admin/view/assets/js/proxy/elFinderSupportVer1.js"></script>

					<!-- Extra contents editors (OPTIONAL) -->
					<script src="../extension/dcelfinder/admin/view/assets/js/extras/editors.default.js"></script>

					<!-- GoogleDocs Quicklook plugin for GoogleDrive Volume (OPTIONAL) -->
					<script src="../extension/dcelfinder/admin/view/assets/js/extras/quicklook.googledocs.js"></script>
				';

				$find2 = '</head>';

				$info = [];

				$info['elfinder_url'] = $this->url->link('extension/dcelfinder/module/dcelfinder.imagemanager', 'user_token=' . $this->session->data['user_token'], true);

				$info['dir_image_url'] = HTTP_CATALOG . 'image/';

				$info['elfinder_click'] = $this->config->get('module_elfinder_click');

				$info['view'] = 'head';

				$replace2 = $this->load->view('extension/dcelfinder/module/dcelfinder', $info);

				$output = str_replace(
					[
						$find1,
						$find2,
					],
					[
						$find1 . $replace1,
						$replace2 . $find2,
					],
					$output
				);
			}
		}
	}

	public function imagemanager(): void {
        $elfinder_autoload = DIR_EXTENSION . 'dcelfinder/library/elfinder/php/autoload.php';

        if (is_file($elfinder_autoload)) {
			$dir_image_url = HTTP_CATALOG . 'image/';

			require_once($elfinder_autoload);

			function access($attr, $path, $data, $volume, $isDir, $relpath) {
				$basename = basename($path);
				return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
					&& strlen($relpath) !== 1           // but with out volume root
					? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
					:  null;                                 // else elFinder decide it itself
			}

			$opts = [
				'debug' => true,
				'roots' => [
					// Items volume
					[
						'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
						'path'          => DIR_IMAGE . 'catalog/',              	    // path to files (REQUIRED)
						'URL'           => $dir_image_url . 'catalog/', 					// URL to files (REQUIRED)
						'tmbURL'        => $dir_image_url . 'catalog/.tmb/',
						'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
						'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
						'uploadDeny'    => ['all'],                // All Mimetypes not allowed to upload
						'uploadAllow'   => ['image'],// Mimetype `image` and `text/plain` allowed to upload
						'uploadOrder'   => ['deny', 'allow'],      // allowed Mimetype `image` and `text/plain` only
						'accessControl' => 'access',                    // disable and hide dot starting files (OPTIONAL)
					],
					// Trash volume
					[
						'id'            => '1',
						'driver'        => 'Trash',
						'path'          => DIR_IMAGE . 'catalog/.trash/',
						'tmbURL'        => $dir_image_url . 'catalog/.trash/.tmb/',
						'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
						'uploadDeny'    => ['all'],                // Recomend the same settings as the original volume that uses the trash
						'uploadAllow'   => ['image'],// Same as above
						'uploadOrder'   => ['deny', 'allow'],      // Same as above
						'accessControl' => 'access',                    // Same as above
					]
				]
			];

			$watermark = $this->config->get('module_dcelfinder_watermark');

			if (!empty($watermark['status']) && !empty($watermark['image'])) {
				if ($watermark['horizontal_selection'] == 'right') {
					$right = $watermark['horizontal_px'];
					$left = false;
				} else {
					$right = false;
					$left = $watermark['horizontal_px'];
				}

				if ($watermark['vertical_selection'] == 'bottom') {
					$bottom = $watermark['vertical_px'];
					$top = false;
				} else {
					$bottom = false;
					$top = $watermark['vertical_px'];
				}

				$opts['bind'] = [
					'upload.presave' => [
						'Plugin.Watermark.onUpLoadPreSave'
					]
				];

				$opts['plugin']['Watermark'] = [
					'enable'         	=> true,       // For control by volume driver
					'source'         	=> DIR_IMAGE . $watermark['image'], // Path to Water mark image
					'right'    			=> $right,          // Margin right pixel
					'left'     			=> $left,          // Margin left pixel
					'bottom'   			=> $bottom,          // Margin bottom pixel
					'top'				=> $top,          // Margin top pixel
					'quality'        	=> 95,         // JPEG image save quality
					'transparency'   	=> $watermark['transparency'],         // Water mark image transparency ( other than PNG )
					'targetType'     	=> IMG_GIF|IMG_JPG|IMG_PNG|IMG_WBMP, // Target image formats ( bit-field )
					'targetMinPixel'	=> 200,        // Target image minimum pixel size
					'offDropWith'		=> null        // To disable it if it is dropped with pressing the meta key
													// Alt: 8, Ctrl: 4, Meta: 2, Shift: 1 - sum of each value
													// In case of using any key, specify it as an array
				];
			}

			$elfinder = new elFinderConnector(new elFinder($opts));
			$elfinder->run();
        }
    }

	public function modifyfiles(): void {
		$this->load->language('extension/dcelfinder/module/dcelfinder');

		$json = [];

		if (isset($this->request->post['js_files'])) {
			foreach ($this->request->post['js_files'] as $file_name => $target_file) {
				$target_file = DIR_APPLICATION . $target_file;

				if ($file_name == 'common') {
					if (is_file($target_file)) {
						$original_contents = file_get_contents($target_file);

						if (!stristr($original_contents, 'elfinder')) {
							$copy_operation = copy($target_file, $target_file . '_elfinder_backup');

							if ($copy_operation) {
								$json['common']['info'][] = $this->language->get('text_backup_created');

								$find = "('#modal-image').remove();";

								$replace ="if (typeof(dcElfinder) != 'undefined') {dcElfinder(\$(element));return false;}";

								$modified_contents = str_replace($find, $find . "\n" . $replace, $original_contents
								);

								if (file_put_contents($target_file, $modified_contents)) {
									$json['common']['success'][] = $this->language->get('text_file_success');
								} else {
									$json['common']['error'][] = $this->language->get('text_an_error');
								}
							} else {
								$json['common']['error'][] = $this->language->get('text_error_backup');
							}
						} else {
							$json['common']['info'][] = $this->language->get('text_file_already');
						}
					} else {
						$json['common']['error'][] = $this->language->get('text_file_not_found');
					}
				}

				if ($file_name == 'ckeditor') {
					if (is_file($target_file)) {
						$original_contents = file_get_contents($target_file);

						if (!stristr($original_contents, 'elfinder')) {
							$copy_operation = copy($target_file, $target_file . '_elfinder_backup');

							if ($copy_operation) {
								$json['ckeditor']['info'][] = $this->language->get('text_backup_created');

								$modified_contents = str_replace(
									["'opencart,codemirror'", "'OpenCart',"],
									["'opencart,codemirror,elfinder'", "'OpenCart','elFinder',"],
									$original_contents
								);

								if (file_put_contents($target_file, $modified_contents)) {
									$json['ckeditor']['success'][] = $this->language->get('text_file_success');
								} else {
									$json['ckeditor']['error'][] = $this->language->get('text_an_error');
								}
							} else {
								$json['ckeditor']['error'][] = $this->language->get('text_error_backup');
							}
						} else {
							$json['ckeditor']['info'][] = $this->language->get('text_file_already');
						}
					} else {
						$json['ckeditor']['error'][] = $this->language->get('text_file_not_found');
					}
				}
			}
		}

		$folder = DIR_APPLICATION . 'view/javascript/ckeditor/plugins/elfinder';

		if (!is_dir($folder)) {
			mkdir($folder, 0777);

			chmod($folder, 0777);

			@touch($folder . '/index.html');

			copy(DIR_OPENCART . 'extension/dcelfinder/admin/view/assets/ckeditor/plugins/elfinder/plugin.js', $folder . '/plugin.js');

			copy(DIR_OPENCART . 'extension/dcelfinder/admin/view/assets/ckeditor/plugins/elfinder/icon.png', $folder . '/icon.png');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function adminmenu(string $route = '', array &$data = []): void {
		if ($this->user->hasPermission('access', 'extension/dcelfinder/module/dcelfinder')) {
			$this->load->language('extension/dcelfinder/module/dcelfinder');

			$data['menus'][] = [
				'id'       => 'menu-elfinder',
				'icon'	   => 'fa fa-images',
				'name'	   => $this->language->get('heading_menu'),
				'href'     => $this->url->link('extension/dcelfinder/module/dcelfinder', 'user_token=' . $this->session->data['user_token'], true),
				'children' => []
			];
		}
	}

	public function install(): void {
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('dcelfinder');

		$this->model_setting_event->addEvent([
			'code' => 'dcelfinder',
			'description' => 'Elfinder - Pro Image Manager',
			'trigger' => 'admin/view/common/column_left/before',
			'action' => 'extension/dcelfinder/module/dcelfinder.adminmenu',
			'status' => true,
			'sort_order' => 0
		]);

		$this->model_setting_event->addEvent([
			'code' => 'dcelfinder',
			'description' => 'Elfinder - Pro Image Manager',
			'trigger' => 'admin/controller/common/header/after',
			'action' => 'extension/dcelfinder/module/dcelfinder.afterheader',
			'status' => true,
			'sort_order' => 0
		]);

		$this->model_setting_event->addEvent([
			'code' => 'dcelfinder',
			'description' => 'Elfinder - Pro Image Manager',
			'trigger' => 'admin/view/catalog/product_form/after',
			'action' => 'extension/dcelfinder/module/dcelfinder.productform',
			'status' => true,
			'sort_order' => 0
		]);

		$folder = DIR_IMAGE . 'catalog/.trash/';

		if (!is_dir($folder)) {
			mkdir($folder);
		}
	}

	public function uninstall(): void {
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('dcelfinder');
	}

	public function save(): void {
		$this->load->language('extension/dcelfinder/module/dcelfinder');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dcelfinder/module/dcelfinder')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('module_dcelfinder', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}