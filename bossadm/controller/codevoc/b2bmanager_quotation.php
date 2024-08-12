<?php
namespace Opencart\Admin\Controller\Codevoc;

require_once(DIR_SYSTEM . 'library/dompdf/autoload.inc.php');
require_once(DIR_SYSTEM . 'library/jqueryFileUploadHandler.php');

use Dompdf\Dompdf;
use JqueryFileUploadHandler;

class B2bmanagerQuotation extends \Opencart\System\Engine\Controller {
	private $error = array();

    public function  index()  {
        
        $this->load->language('codevoc/b2bmanager_quotation');
        
		$this->document->setTitle($this->language->get('heading_title'));
        
		$this->load->model('codevoc/b2bmanager_quotation');
        
		$this->getList();
    }
	public function add(){
        $this->load->language('codevoc/b2bmanager_quotation');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('view/javascript/jquery-fileuploader/css/jquery.fileupload.css');
		$this->document->addStyle('view/stylesheet/pw4_b2bmanager.css');
        $this->document->addStyle('view/javascript/jquery-fileuploader/css/jquery.fileupload-ui.css');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/vendor/jquery.ui.widget.js');
		$this->document->addScript('//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js');
        $this->document->addScript('//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.iframe-transport.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-process.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-image.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-validate.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/spw-fileuploader.js');
        $this->load->model('codevoc/b2bmanager_quotation');
        $this->getForm();
    }

	public function edit() {
		$this->load->language('codevoc/b2bmanager_quotation');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/pw4_b2bmanager.css');
		$this->document->addStyle('view/javascript/jquery-fileuploader/css/jquery.fileupload.css');
        $this->document->addStyle('view/javascript/jquery-fileuploader/css/jquery.fileupload-ui.css');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/vendor/jquery.ui.widget.js');
		$this->document->addScript('//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js');
        $this->document->addScript('//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.iframe-transport.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-process.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-image.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/jquery.fileupload-validate.js');
        $this->document->addScript('view/javascript/jquery-fileuploader/js/spw-fileuploader.js');
		$this->load->model('codevoc/b2bmanager_quotation');
		$this->getForm();
	}

    protected function getList() {
		$this->document->addStyle('view/stylesheet/pw4_b2bmanager.css');
		// The quotation based filter i.e. All, Sent, Draft...etc
		if (isset($this->request->get['filter_quotation_status_id'])) {
            $filter_quotation_status_id = $this->request->get['filter_quotation_status_id'];
        } else {
            $filter_quotation_status_id = 'all';
        }

		// When user search for the specific quotation
		if (isset($this->request->get['filter_search'])) {
			$filter_search = $this->request->get['filter_search'];
		} else {
			$filter_search = '';
		}

		// When user sort the result based on the particular column
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'q.quotation_id';
		}

		// The ascending or descending order of the search result
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		// limit filter
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_pagination_admin');
		}

		// assignee filter
		if (isset($this->request->get['assignee_filter']) && $this->request->get['assignee_filter'] != 0) {
			$assigneeFilter = $this->request->get['assignee_filter'];
		} else {
			$assigneeFilter = '';
		}

		// The page number of the results
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		// This variable will store the url of the page, as we need to include all the get parameters to the URL
        $url = '';

		// Add the filter_quotation_status_id to the URL get parameters
		if (isset($this->request->get['filter_quotation_status_id'])) {
            $url .= '&filter_quotation_status_id=' . $this->request->get['filter_quotation_status_id'];
        }

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		if (isset($this->request->get['assignee_filter']) && $this->request->get['assignee_filter'] != 0) {
			$url .= '&assignee_filter=' . $this->request->get['assignee_filter'];
		}

		$data['add'] = $this->url->link('codevoc/b2bmanager_quotation.add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('codevoc/b2bmanager_quotation.delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['quotations'] = array();

		// The database will be queried based on the below conditions.
		$filter_data = array(
			'filter_quotation_status_id'=> $filter_quotation_status_id,
			'filter_search'				=> $filter_search,
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $limit,
			'limit'                  => $limit,
			'assignee_filter'	 	 => $assigneeFilter
		);
        
        // Get the count of all the quotations
        $quotation_total = $this->model_codevoc_b2bmanager_quotation->getTotalQuotations($filter_data);

        // Get all the data related to all the quotations
        $results = $this->model_codevoc_b2bmanager_quotation->getQuotations($filter_data);
        // Getting additional details related to the quotations
        foreach ($results as $result) {
            $companyname='';
            $assignee='';
            $create_date='';

            // Get the other details of the quotations
            $quotation_other_details = $this->model_codevoc_b2bmanager_quotation->getQuotationOtherdetails($result['quotation_id']);


            if($quotation_other_details)
			{
				$assignee=$quotation_other_details['assignee'];
				$create_date=$quotation_other_details['create_date'];
			}

            $custom_field=json_decode($result['custom_field'], true);

			if($custom_field != null && is_array($custom_field) && !empty(array_key_exists('1',$custom_field)))
			{
				$companyname=$custom_field[1];
			}
            
            $edit_order='';
			$orderid='';
			$order_check=$this->db->query("select * from `" . DB_PREFIX . "order` where quotation_id='".$result['quotation_id']."' and order_type='Quotation' order by order_id DESC LIMIT 1");

			if($order_check->num_rows>0)
			{
				$edit_order=$this->url->link('codevoc/b2bmanager_order.edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_check->row['order_id'] . $url, true);
				$orderid=$order_check->row['order_id'];
			}

            $data['productions']='';
            $data['productionurl']='';
            $check_production = $this->db->query("SELECT  * FROM " . DB_PREFIX . "codevoc_production where order_id = '".$orderid."' order by production_id DESC limit 1 ");
            
            if($check_production->num_rows>0)
            {
                $production_id=$check_production->row['production_id'];
                $production_url=$this->url->link('codevoc/b2bmanager_production/edit', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $check_production->row['production_id'], true);
            }else{
                $production_id=0;
                $production_url=0;
            }

            // get production
			$check_quotation_attchments = $this->db->query("SELECT filename FROM " . DB_PREFIX . "codevoc_quotation_files WHERE  quotation_id = '".$result['quotation_id']."'");

			if($check_quotation_attchments->num_rows>0)
			{
				$quotationAttachments=$check_quotation_attchments->row['filename'];
			}else{
				$quotationAttachments=0;
			}

            // get reminders of quotation
			$reminders = $this->model_codevoc_b2bmanager_quotation->getQuotationReminders($result['quotation_id']);

			$last_reminder = $this->model_codevoc_b2bmanager_quotation->getLastQuotationReminder($result['quotation_id']);

            $data['quotations'][] = array(
				'quotation_id'  => $result['quotation_id'],
				'customer'      => $result['customer'],
				'companyname'	=>str_replace("&amp;","&",$result['vatnr']),
				'production'      => $production_id,
				'production_url'      => $production_url,
				'create_date'    =>date($this->language->get('date_format_short'), strtotime($create_date)),
				'total'         => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'assignee'  => $result['assignee'],
				'quotation_status_id'  => $result['quotation_status_id'],
				'attachments' => $quotationAttachments,
				'edit'          => $this->url->link('codevoc/b2bmanager_quotation.edit', 'user_token=' . $this->session->data['user_token'] . '&quotation_id=' . $result['quotation_id'] . $url, true),
				'copy'          => $this->url->link('codevoc/b2bmanager_quotation.copy', 'user_token=' . $this->session->data['user_token'] . '&quotation_id=' . $result['quotation_id'] . $url, true),
				'pdf'          => $this->url->link('codevoc/b2bmanager_quotation.exportpdf', 'user_token=' . $this->session->data['user_token'] . '&quotation_id=' . $result['quotation_id'] . $url, true),

				// //addon point 6
				'order'=>$edit_order,
				'orderid'=>$orderid,
				'reminders' => $reminders,
				'reminders_count' => count($reminders),
				'last_reminder' => $last_reminder,
				//addon point 6
			);
        }

        $data['user_token'] = $this->session->data['user_token'];

		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$data['limit'] = $limit;
		$data['assigneeFilter'] = $assigneeFilter;
		

		$url = '';

		if (isset($this->request->get['filter_quotation_status_id'])) {
            $url .= '&filter_quotation_status_id=' . $this->request->get['filter_quotation_status_id'];
        }

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($order) && $order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['assignee_filter']) && $this->request->get['assignee_filter'] != 0) {
			$url .= '&assignee_filter=' . $this->request->get['assignee_filter'];
		}

		$data['sort_quotation_nr'] = $this->url->link('codevoc/b2bmanager_quotation', 'user_token=' . $this->session->data['user_token'] . '&sort=q.quotation_id' . $url, true);

		$data['sort_total'] = $this->url->link('codevoc/b2bmanager_quotation', 'user_token=' . $this->session->data['user_token'] . '&sort=q.total' . $url, true);

		$url = '';
		

		if (isset($this->request->get['filter_quotation_status_id'])) {
            $url .= '&filter_quotation_status_id=' . $this->request->get['filter_quotation_status_id'];
        }

		if (isset($this->request->get['filter_search'])) {
			$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['assignee_filter'])) {
            $url .= '&assignee_filter=' . $this->request->get['assignee_filter'];
        }
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}


        $data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $quotation_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('codevoc/b2bmanager_quotation', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true)
		]);
		

        // $data['results'] = sprintf($this->language->get('text_pagination'), ($quotation_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($quotation_total - $this->config->get('config_limit_admin'))) ? $quotation_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $quotation_total, ceil($quotation_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;

		$data['order'] = $order;

		$data['filter_quotation_status_id'] = $filter_quotation_status_id;

		$data['filter_search'] = $filter_search;
        
		$data['quotation_statuses'] = $this->model_codevoc_b2bmanager_quotation->getQuotationStatuses();

		$this->load->model('user/user');

		$filter_data = array(
        'sort'  => 'username',
        'order' => 'ASC'
        );


		$data['system_users'] = $this->model_user_user->getUsers($filter_data);

		// System Users

		// API login

		// $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

		// API login
		// $this->load->model('user/api');

		// $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

		// if ($api_info && $this->user->hasPermission('modify', 'codevoc/b2bmanager_quotation')) {

		// 	$session = new Session($this->config->get('session_engine'), $this->registry);

		// 	$session->start();

		// 	$this->model_user_api->deleteApiSessionBySessonId($session->getId());

		// 	$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

		// 	$session->data['api_id'] = $api_info['api_id'];

		// 	$data['api_token'] = $session->getId();
		// } else {
		// 	$data['api_token'] = '';
		// }

		$data['cancelled_statuses'] = [
			['key' => '10','value' => 'Dubblett/Avbryten'],
			['key' => '20','value' => 'Kund har ej budget'],
			['key' => '30','value' => 'För lång leveranstid'],
			['key' => '40','value' => 'Kund valde konkurrent'],
			['key' => '50','value' => 'Kund återkom aldrig'],
			['key' => '60','value' => 'Kund vill återkomma senare'],
			['key' => '70','value' => 'För få antal'],
			['key' => '99','value' => 'Annan anledning'],
		];

		$data['header'] = $this->load->controller('common/header');

		$data['column_left'] = $this->load->controller('common/column_left');

		$data['footer'] = $this->load->controller('common/footer');		
		$this->response->setOutput($this->load->view('codevoc/b2bmanager_quotationlist', $data));
    }

	public function updateQuotationStatus() {
		if(isset($this->request->get['quotation_id'])){

			$quotation_id = $this->request->get['quotation_id'];
			$status = $this->request->post['status'];

			$this->load->model('codevoc/b2bmanager_quotation');
			$this->model_codevoc_b2bmanager_quotation->updateQuotationStatus($quotation_id, $status);
		}
	}

	public function updateAssigneeStatus() {
		if(isset($this->request->get['quotation_id'])){

			$quotation_id = $this->request->get['quotation_id'];
			$status = $this->request->post['status'];

			$this->load->model('codevoc/b2bmanager_quotation');
			$this->model_codevoc_b2bmanager_quotation->updateAssigneeStatus($quotation_id, $status);
		}
	}

	public function saveCancelReason() {

		$json = array();

		$quotation_id = isset($this->request->get['id']) && !empty($this->request->get['id']) ? $this->request->get['id'] : '';
		$reason = isset($this->request->post['reason']) && !empty($this->request->post['reason']) ? $this->request->post['reason'] : '';
		$comment = isset($this->request->post['comment']) && !empty($this->request->post['comment']) ? $this->request->post['comment'] : '';

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
				$json['error']['common'] = "Invalid request type";
		}

		if($quotation_id == ''){
				$json['error']['id'] = "Quotation id not provided";
		}

		if($reason == ''){
				$json['error']['reason'] = "Reason not provided";
		}

		if(!isset($json['error'])) {
			$this->load->model('codevoc/b2bmanager_quotation');

			$this->model_codevoc_b2bmanager_quotation->saveCancelReason($quotation_id, [
					'reason' => $reason,
					'comment' => $comment,
					'user_id' => $this->session->data['user_id'],
			]);

			$json['success'] = [
					'success' => true,
					'message' => "Status changed to cancelled.",
			];
		}


		if (isset($this->request->server['HTTP_ORIGIN'])) {
				$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
				$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				$this->response->addHeader('Access-Control-Max-Age: 1000');
				$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function copy()
    {
        $this->load->language('codevoc/b2bmanager_quotation');
		$this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('codevoc/b2bmanager_quotation');

        if (isset($this->request->get['quotation_id'])) {

            $quotation_id = $this->request->get['quotation_id'];
            $quotation_id = $this->model_codevoc_b2bmanager_quotation->copyQuotation($quotation_id);
            $this->session->data['success'] = $this->language->get('text_success');
			$url = '';

			if (isset($this->request->get['filter_quotation_status_id']))

			{
				$url .= '&filter_quotation_status_id=' . $this->request->get['filter_quotation_status_id'];
			}

			if (isset($this->request->get['filter_search'])) {
				$url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

			if (isset($this->request->get['assignee_filter']) && $this->request->get['assignee_filter'] != 0) {
                $url .= '&assignee_filter=' . $this->request->get['assignee_filter'];
            }

           	$this->response->redirect($this->url->link('codevoc/b2bmanager_quotation', 'user_token=' . $this->session->data['user_token'] . $url, true));

        }

        $this->getList();

    }

	function createOrder()
	{
		$json=array();

		if (isset($this->request->get['quotation_id']))
		{
        	 $quotation_id = $this->request->get['quotation_id'];
			 $quotation=$this->db->query("select * from `" . DB_PREFIX . "codevoc_quotation` where quotation_id='".$quotation_id."'");
			 $quotation_totals=$this->db->query("select * from `" . DB_PREFIX . "codevoc_quotation_order_total` where quotation_id='".$quotation_id."'");
			 $quotation_order_products=$this->db->query("select * from `" . DB_PREFIX . "codevoc_quotation_order_product` where quotation_id='".$quotation_id."'");
			 $quotation_other_details=$this->db->query("select * from `" . DB_PREFIX . "codevoc_quotation_other_details` where quotation_id='".$quotation_id."'");
			if($quotation->num_rows>0)
			{
			  $data=$quotation->row;
			 //create order
			 if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$data['accept_language'] = '';
			}

			 $this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET
			 invoice_prefix = 'INV-OC-',
			 store_id = '" . (int)$data['store_id'] . "',
			 store_name = '" . $this->db->escape($data['store_name']) . "',
			 store_url = '" . $this->db->escape($data['store_url']) . "',
			 customer_id = '" . (int)$data['customer_id'] . "',
			 customer_group_id = '" . (int)$data['customer_group_id'] . "',
			 firstname = '" . $this->db->escape($data['firstname']) . "',
			 lastname = '" . $this->db->escape($data['lastname']) . "',
			 email = '" . $this->db->escape($data['email']) . "',
			 telephone = '" . $this->db->escape($data['telephone']) . "',
			 custom_field = '" . $this->db->escape(isset($data['custom_field']) ? $data['custom_field'] : '') . "',
			 payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "',
			 payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "',
			 payment_company = '" . $this->db->escape($data['payment_company']) . "',
			 payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "',
			 payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "',
			 payment_city = '" . $this->db->escape($data['payment_city']) . "',
			 payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "',
			 payment_country = '" . $this->db->escape($data['payment_country']) . "',
			 payment_country_id = '" . (int)$data['payment_country_id'] . "',
			 payment_zone = '" . $this->db->escape($data['payment_zone']) . "',
			 payment_zone_id = '" . (int)$data['payment_zone_id'] . "',
			 payment_address_format = '',
			 payment_custom_field = '" . $this->db->escape(isset($data['payment_custom_field']) ? $data['payment_custom_field'] : '') . "',
			 payment_method = '" . $this->db->escape($data['payment_method']) . "',
			 payment_code = '" . $this->db->escape($data['payment_code']) . "',
			 shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "',
			 shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "',
			 shipping_company = '" . $this->db->escape($data['shipping_company']) . "',
			 shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "',
			 shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "',
			 shipping_city = '" . $this->db->escape($data['shipping_city']) . "',
			 shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "',
			 shipping_country = '" . $this->db->escape($data['shipping_country']) . "',
			 shipping_country_id = '" . (int)$data['shipping_country_id'] . "',
			 shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "',
			 shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "',
			 shipping_address_format = '',
			 shipping_custom_field = '" . $this->db->escape(isset($data['shipping_custom_field']) ? $data['shipping_custom_field'] : '') . "',
			 shipping_method = '" . $this->db->escape($data['shipping_method']) . "',
			 shipping_code = '" . $this->db->escape($data['shipping_code']) . "',
			 comment = '" . $this->db->escape($data['comment']) . "',
			 total = '" . (float)$data['total'] . "',
			 affiliate_id = '" . (int)$data['affiliate_id'] . "',
			 commission = '" . (float)$data['commission'] . "',
			 marketing_id = '" . (int)$data['marketing_id'] . "',
			 tracking = '" . $this->db->escape($data['tracking']) . "',
			 language_id = '" . (int)$data['language_id'] . "',
			 currency_id = '" . (int)$data['currency_id'] . "',
			 currency_code = '" . $this->db->escape($data['currency_code']) . "',
			 currency_value = '" . (float)$data['currency_value'] . "',
			 ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "',
			 forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "',
			 user_agent = '" . $this->db->escape($data['user_agent']) . "',
			 accept_language = '" . $this->db->escape($data['accept_language']) . "',
			 date_added = NOW(),
			 quotation_id = '" . (int)($quotation_id) . "',
			 order_type = 'Quotation',
			 order_status_id='1',
			 date_modified = NOW()");
			 $order_id = $this->db->getLastId();

			 // Products
		if ($quotation_order_products) {
			foreach ($quotation_order_products->rows as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "'");

				//B2B Manager
				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_b2b_order_product SET 
				order_id = '" . (int)$order_id . "', 
				product_id = '" . (int)$product['product_id'] . "',
				name = '" . $this->db->escape($product['name']) . "', 
				model = '" . $this->db->escape($product['model']) . "', 
				quantity = '" . (int)$product['quantity'] . "', 
				price = '" . (float)$product['price'] . "', 
				total = '" . (float)$product['total'] . "',				
				tax_class_id = '" . $this->db->escape($product['tax_class_id']) . "',
				sort = '" . $this->db->escape($product['sort']) . "',
				discount = '" . (float)$product['discount'] . "', 
				tax = '" . (float)$product['tax'] . "'");
				//B2B Manager
				$order_product_id = $this->db->getLastId();

				$quotation_order_products_options=$this->db->query("select * from `" . DB_PREFIX . "codevoc_quotation_order_option` where quotation_id='".$quotation_id."' and quotation_product_id='".$product['quotation_product_id']."'");

				foreach ($quotation_order_products_options->rows as $option) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
				}
			}
		}
		//quotation other detail
		if($quotation_other_details) {
			foreach($quotation_other_details->rows as $other_details) {
				$shipping_company=$data['shipping_custom_field'];
				$payment_company=$data['payment_custom_field'];

				$this->db->query("INSERT INTO " . DB_PREFIX . "codevoc_b2b_order SET
				order_id = '" . (int)$order_id . "',
				assignee = '" . $this->db->escape($other_details['assignee']) . "',
				vatnr = '" . $this->db->escape($other_details['vatnr']) . "',
				payment_company = '" . $this->db->escape($payment_company) . "',
				shipping_company = '" . $this->db->escape($shipping_company) . "'");
				$this->db->query("update `" . DB_PREFIX . "order` set delivery_date = '".$this->db->escape($other_details['delivery_date'])."' where order_id='".$order_id."'");
			}
		}
		//quotation other detail

		if ($quotation_totals) {
			foreach ($quotation_totals->rows as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}
		//create order
			$this->db->query("update " . DB_PREFIX . "codevoc_quotation set quotation_status_id='3' WHERE quotation_id = '" . (int)$quotation_id . "'");

			// Slack intigration
			$s_total = number_format($data['total'], 2, '.', ',');
			$order_firstname = $data['firstname'];
			$order_lastname = $data['lastname'];
			$order_email = $data['email'];
			if(!empty($data['custom_field'])) {
				$the_companyname = json_decode($data['custom_field'], true);
				$order_company_name = $the_companyname["1"].' > ';
			}else{$order_company_name = '';}
			$sales_person = $other_details['assignee'];
			// $this->load->library('slack');
			// $message = <<<SLACKMESSAGE
			// :zap:Ny order :arrow_right: {$order_id} \n @{$order_company_name} {$order_firstname} {$order_lastname} {$order_email}\n Order skapad från Offertnr {$quotation_id} \n :moneybag: {$s_total} SEK\n
			// SLACKMESSAGE;
			// $this->slack->sendMessage($message, 'sales');


			}
			else
			{
				$json['error'] = 'Quotation ID is not provided.';
			}
		}
		else
		{
			$json['error'] = 'Quotation ID is not provided.';
    	}
		$this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
	}

	public function getForm()
    {
        $url = '';
        if (isset($this->request->get['filter_quotation_status_id'])) {
            $url .= '&filter_quotation_status_id=' . $this->request->get['filter_quotation_status_id'];
        }
        //v7
        if (isset($this->request->get['filter_search'])) {
            $url .= '&filter_search=' . urlencode(html_entity_decode($this->request->get['filter_search'], ENT_QUOTES, 'UTF-8'));
        }
        //v7
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        $data['user_token'] = $this->session->data['user_token'];
        $add_customer_link = 'customer/customer.form&user_token=' . $this->session->data['user_token'];
        $data['button_add_customer'] = $this->url->link($add_customer_link, '', '');
        $upload_link = 'codevoc/b2bmanager_quotation.handleUpload&user_token=' . $this->session->data['user_token'];
        $data['uploadUrl'] = $this->url->link($upload_link, '', '');
        $delete_link = 'codevoc/b2bmanager_quotation.handleDelete&user_token=' . $this->session->data['user_token'];
        $data['deleteUrl'] = $this->url->link($delete_link, '', '');
        $data['no_image_url'] = HTTP_SERVER . 'view/image/no_image.jpg';
        $data['custom_cart_url'] = '';
        $data['exportpdf_url'] = '';
        $quotation_id = isset($_GET['quotation_id']) && !empty($_GET['quotation_id']) ? $_GET['quotation_id'] : '';
        if (isset($quotation_id) && !empty($quotation_id)) {
            $data['custom_cart_url'] = HTTP_CATALOG . 'offert/' . $quotation_id;
            $data['exportpdf_url'] = $this->url->link('codevoc/b2bmanager_quotation.exportpdf', 'user_token=' . $this->session->data['user_token'] . '&quotation_id=' . $quotation_id);
        }
        if (!isset($this->request->get['quotation_id'])) {
            $data['action'] = $this->url->link('codevoc/b2bmanager_quotation.add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('codevoc/b2bmanager_quotation.edit', 'user_token=' . $this->session->data['user_token'] . '&quotation_id=' . $this->request->get['quotation_id'] . $url, true);
        }
        $data['cancel'] = str_replace('&amp;', '&', $this->url->link('codevoc/b2bmanager_quotation&user_token=' . $this->session->data['user_token'].'&filter_quotation_status_id=1&limit=10', true)); 

        if (isset($this->request->get['quotation_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {

            $quotation_info = $this->model_codevoc_b2bmanager_quotation->getQuotation($this->request->get['quotation_id']);

            $quotation_other_details = $this->model_codevoc_b2bmanager_quotation->getQuotationOtherdetails($this->request->get['quotation_id']);
        }
        if (!empty($quotation_info)) {
            $data['quotation_id'] = $this->request->get['quotation_id'];
            $data['store_id'] = $quotation_info['store_id'];
            $data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
            $data['store_name'] = $quotation_info['store_name'];
            $data['customer'] = $quotation_info['customer'];
            $data['customer_id'] = $quotation_info['customer_id'];
            $data['customer_group_id'] = $quotation_info['customer_group_id'];
            $data['firstname'] = $quotation_info['firstname'];
            $data['lastname'] = $quotation_info['lastname'];
            $data['email'] = $quotation_info['email'];
            $data['telephone'] = $quotation_info['telephone'];
            $data['account_custom_field'] = $quotation_info['custom_field'];
			$data['payment_address_id'] = $quotation_info['payment_address_id'];
			$data['shipping_address_id'] = $quotation_info['shipping_address_id'];



            // Bring order into Quotation if order exists
            $button_order = '';
            $orderid = '';
            $order_check = $this->db->query("select order_id from `" . DB_PREFIX . "order` where quotation_id='" . $this->request->get['quotation_id'] . "' and order_type='Quotation' order by order_id DESC LIMIT 1");
            if ($order_check->num_rows > 0) {
                $button_order = $this->url->link('codevoc/b2bmanager_order.edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_check->row['order_id'] . $url, true);
                $orderid = $order_check->row['order_id'];
            }
            $data['button_order'] = $button_order;
            $data['order_id'] = $orderid;

            // Bring production into Quotation if production exists
            if ($order_check->num_rows > 0) {
                $data['production_id'] = '';
                $data['button_prodution'] = '';
                $check_production = $this->db->query("SELECT production_id FROM " . DB_PREFIX . "codevoc_production where order_id = '" . $orderid . "' order by production_id DESC limit 1 ");
                if ($check_production->num_rows > 0) {
                    $data['production_id'] = $check_production->row['production_id'];
                    $data['button_prodution'] = $this->url->link('codevoc/b2bmanager_production.edit', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $check_production->row['production_id'], true);
                }
            }
            if ($quotation_info['customer_id']) {
                $data['customerurl'] = $this->url->link('customer/customer.form&customer_id=' . $quotation_info['customer_id'], 'user_token=' . $this->session->data['user_token'], true);
            } else {
                $data['customerurl'] = '';
            }
            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($quotation_info['date_added']));
            $this->load->model('customer/customer');
            $data['addresses'] = $this->model_customer_customer->getAddresses($quotation_info['customer_id']);
            $data['payment_firstname'] = $quotation_info['payment_firstname'];
            $data['payment_lastname'] = $quotation_info['payment_lastname'];
            $data['payment_company'] = $quotation_info['payment_company'];
            $data['payment_address_1'] = $quotation_info['payment_address_1'];
            $data['payment_address_2'] = $quotation_info['payment_address_2'];
            $data['payment_city'] = $quotation_info['payment_city'];
            $data['payment_postcode'] = $quotation_info['payment_postcode'];
            $data['payment_country_id'] = $quotation_info['payment_country_id'];
            $data['payment_zone_id'] = $quotation_info['payment_zone_id'];
            $data['payment_custom_field'] = $quotation_info['payment_custom_field'];
            $data['payment_method'] = $quotation_info['payment_method'];
			$data['payment_methods'] = $this->getPaymentMethods();
            $data['payment_code'] = $quotation_info['payment_code'];
            $data['shipping_firstname'] = $quotation_info['shipping_firstname'];
            $data['shipping_lastname'] = $quotation_info['shipping_lastname'];
            $data['shipping_company'] = $quotation_info['shipping_company'];
            $data['shipping_address_1'] = $quotation_info['shipping_address_1'];
            $data['shipping_address_2'] = $quotation_info['shipping_address_2'];
            $data['shipping_city'] = $quotation_info['shipping_city'];
            $data['shipping_postcode'] = $quotation_info['shipping_postcode'];
            $data['shipping_country_id'] = $quotation_info['shipping_country_id'];
            $data['shipping_zone_id'] = $quotation_info['shipping_zone_id'];
            $data['shipping_custom_field'] = $quotation_info['shipping_custom_field'];
            $data['shipping_method'] = $quotation_info['shipping_method'];
			$data['shipping_methods'] = $this->getShippingMethods();
            $data['shipping_code'] = $quotation_info['shipping_code'];
			$data['total'] = $quotation_info['total'];

			foreach($data['payment_methods'] as $method){
				if($method['code'] == $quotation_info['payment_method']){
					$data['payment_method_name'] = $method['name'];
				}
			}
			if(isset($data['payment_method_name'])){
				$data['payment_method_name']= '';
			}

			foreach($data['shipping_methods'] as $method){
				if($data['shipping_method'] == $method['code']){
					$data['shipping_method_name'] = $method['name'];
				}
			}

			if(isset($data['shipping_method_name'])){
				$data['shipping_method_name'] = '';
			}


            //codevoc quotation other detail table data
            $data['quotation_other_detail'] = array();
            $data['quotation_other_detail']['assignee'] = $quotation_other_details['assignee'];
            $data['quotation_other_detail']['vatnr'] = $quotation_other_details['vatnr'];
            $data['quotation_other_detail']['payment_company'] = $quotation_other_details['payment_company'];
            $data['quotation_other_detail']['shipping_company'] = $quotation_other_details['shipping_company'];
            $data['quotation_other_detail']['create_date'] = $quotation_other_details['create_date'];
            $data['quotation_other_detail']['delivery_date'] = $quotation_other_details['delivery_date'];
            $data['quotation_other_detail']['expiration_date'] = $quotation_other_details['expiration_date'];
            $data['quotation_other_detail']['shippment_terms'] = $quotation_other_details['shippment_terms'];
            $data['quotation_other_detail']['rate_delay'] = $quotation_other_details['rate_delay'];
            $data['quotation_other_detail']['custom_ordernr'] = $quotation_other_details['custom_ordernr'];

            //codevoc quotation other detail
            // Products
            $this->load->model('catalog/product');
			$this->load->model('localisation/tax_rate');
            $data['order_products'] = array();
            $products = $this->model_codevoc_b2bmanager_quotation->getQuotationOrderProducts($this->request->get['quotation_id']);
			$sub_total = 0;
            foreach ($products as $product) {
                $price_discount_percentage = $product['discount'];
                $sort_order = $product['sort'];
                $tax_class_id = $product['tax_class_id'];
				$this->load->model('localisation/tax_class');
				$tax_rules = $this->model_localisation_tax_class->getTaxRules($tax_class_id);
                $product_info = $this->model_catalog_product->getProduct($product['product_id']);
                $ooption = array();
                $order_options = $this->model_codevoc_b2bmanager_quotation->getQuotationOrderOptions($this->request->get['quotation_id'], $product['quotation_product_id']);
                foreach ($order_options as $order_option) {
                    $ooption[$order_option['product_option_id']] = $order_option['product_option_value_id'];
                }
                $product_options = $this->model_codevoc_b2bmanager_quotation->getProductOptionsSel($product['product_id'], $ooption);

                //v6
                $hiddenprice = $product['price'];
                //v6

                //discount price calculation
				if(array_key_exists('price',$product_info)){
					$org_price = $product_info['price'];
					$org_price_base = $product_info['price'];
				}else{
					$org_price = $product['price'];
					$org_price_base = $product['price'];
				}
                //discount price calculation

                //v5
                $productoptions = array();
                foreach ($product_options as $poption) {
                    $opprice = '';
                    foreach ($poption['product_option_value'] as $opt) {

                        if ($opt['selected'] == 'selected') {
                            $opprice = $opt['price_prefix'] . '_' . $opt['price'];
                            //v6
                            if ($opt['price_prefix'] == '+') {
                                $hiddenprice -= $opt['price'];
                                //discount price calculation
                                $org_price += $opt['price'];
                                //discount price calculation									
                            } elseif ($opt['price_prefix'] == '-') {
                                $hiddenprice += $opt['price'];
                                //discount price calculation
                                $org_price -= $opt['price'];
                                //discount price calculation										
                            }

                            //v6
                            break;
                        }

                    }
                    $productoptions[] = array(
                        'product_option_id' => $poption['product_option_id'],
                        'product_option_value' => $poption['product_option_value'],
                        'option_id' => $poption['option_id'],
                        'name' => $poption['name'],
                        'opprice' => $opprice,
                        'type' => $poption['type'],
                        'value' => $poption['value'],
                        'required' => $poption['required']
                    );

                }
                $product_options = $productoptions;
                //v5
				$total_tax = 0;	
				foreach($tax_rules as $tax_rule){
					$tax_rates = $this->model_localisation_tax_rate->getTaxRate($tax_rule['tax_rate_id']);
					$total_tax = $total_tax + $tax_rates['rate'];
				}

                $data['order_products'][] = array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'option' => $product_options,
                    'quantity' => $product['quantity'],
                    'price' => number_format($product['price'], 2, '.', ''),
                    'hiddenprice' => number_format($hiddenprice, 2, '.', ''),
                    'org_price' => number_format($org_price, 2, '.', ''), //discount price calculation
                    'org_price_base' => number_format($org_price_base, 2, '.', ''), //discount price calculation
                    'price_discount_percentage' => number_format($price_discount_percentage, 2, '.', ''),
                    'sort_order' => $sort_order,
                    'tax_class_id' => $tax_class_id,
					'tax' => $product['tax'],
					'total_tax_rate' => $total_tax,
                    'total' => number_format($product['total'], 2, '.', ''),
                    'reward' => $product['reward']
                );
				$sub_total = $sub_total +$product['price']*$product['quantity'];
            }
			$data['subtotal'] = $sub_total; 
            $data['coupon'] = '';
            $data['voucher'] = '';
            $data['reward'] = '';
            $data['order_totals'] = array();
            $quotation_totals = $this->model_codevoc_b2bmanager_quotation->getQuotationOrderTotals($this->request->get['quotation_id']);
            foreach ($quotation_totals as $total) {

                  $data['quotation_totals'][] = array(
                    'title' => $total['title'],
                    'text' => number_format($total['value'],2),
					'code' => $total['code'],
                );

           }
            $data['quotation_status_id'] = $quotation_info['quotation_status_id'];
            $data['comment'] = $quotation_info['comment'];
            $data['file_final_sketch'] = $quotation_info['file_final_sketch'];
            $data['affiliate_id'] = $quotation_info['affiliate_id'];
            $data['affiliate'] = $quotation_info['affiliate_firstname'] . ' ' . $quotation_info['affiliate_lastname'];
            $data['currency_code'] = $quotation_info['currency_code'];

        } else {
            $data['quotation_id'] = 0;
            $data['store_id'] = 0;
            $data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
            $data['store_name'] = '';
            $data['customer'] = '';
            $data['customer_id'] = '';
            $data['customer_group_id'] = $this->config->get('config_customer_group_id');
            $data['firstname'] = '';
            $data['lastname'] = '';
            $data['email'] = '';
            $data['telephone'] = '';
            $data['customer_custom_field'] = array();
            $data['customerurl'] = '';
            $data['addresses'] = array();

            //codevoc quotation other detail table data
            $data['quotation_other_detail'] = array();
            //addon point 3
           $data['quotation_other_detail']['assignee'] = $this->user->getId();
           //addon point 3
            $data['quotation_other_detail']['vatnr'] = '';
            $data['quotation_other_detail']['payment_company'] = array();
            $data['quotation_other_detail']['shipping_company'] = array();
            $data['quotation_other_detail']['create_date'] = '';
            $data['quotation_other_detail']['delivery_date'] = '';
            $data['quotation_other_detail']['expiration_date'] = '';
            $data['quotation_other_detail']['shippment_terms'] = '';
            $data['quotation_other_detail']['rate_delay'] = '';
            $data['quotation_other_detail']['custom_ordernr'] = '';
            //codevoc quotation other detail table data

            $data['payment_firstname'] = '';
            $data['payment_lastname'] = '';
            $data['payment_company'] = '';
            $data['payment_address_1'] = '';
            $data['payment_address_2'] = '';
            $data['payment_city'] = '';
            $data['payment_postcode'] = '';
            $data['payment_country_id'] = '';
            $data['payment_zone_id'] = '';
            $data['payment_custom_field'] = array();
            $data['payment_methods'] = $this->getPaymentMethods();
            $data['payment_code'] = '';
            $data['shipping_firstname'] = '';
            $data['shipping_lastname'] = '';
            $data['shipping_company'] = '';
            $data['shipping_address_1'] = '';
            $data['shipping_address_2'] = '';
            $data['shipping_city'] = '';
            $data['shipping_postcode'] = '';
            $data['shipping_country_id'] = '';
            $data['shipping_zone_id'] = '';
            $data['shipping_custom_field'] = array();
            $data['shipping_methods'] = $this->getShippingMethods();
            $data['shipping_code'] = '';
			$data['quotation_status'] = $this->model_codevoc_b2bmanager_quotation->getQuotationStatuses();
            $data['order_products'] = array();
            $data['order_totals'] = array();
            $data['order_status_id'] = $this->config->get('config_order_status_id');
            //addon point 3
            $data['quotation_status_id'] = '1';
            //addon point 3
            $data['comment'] = '';
            $data['file_final_sketch'] = '';
            $data['affiliate_id'] = '';
            $data['affiliate'] = '';
            $data['currency_code'] = $this->config->get('config_currency');
            $data['coupon'] = '';
            $data['voucher'] = '';
            $data['reward'] = '';
        }


        // Stores
           $this->load->model('setting/store');

        $data['stores'] = array();
        $data['stores'][] = array(
            'store_id' => 0,
            'name' => $this->language->get('text_default')
        );

        $results = $this->model_setting_store->getStores();
        foreach ($results as $result) {
            $data['stores'][] = array(
                'store_id' => $result['store_id'],
                'name' => $result['name']
            );
        }
        // Tax Classes
		$this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        // Customer Groups
        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
        // Custom Fields
        $this->load->model('customer/custom_field');
        $data['custom_fields'] = array();
        $filter_data = array(
            'sort' => 'cf.sort_order',
            'order' => 'ASC'
        );
        $custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);
        foreach ($custom_fields as $custom_field) {
            $data['custom_fields'][] = array(
               'custom_field_id' => $custom_field['custom_field_id'],
                'custom_field_value' => $this->model_customer_custom_field->getValue($custom_field['custom_field_id']),
                'name' => $custom_field['name'],
                'value' => $custom_field['value'],
               'type' => $custom_field['type'],
              'location' => $custom_field['location'],
                'sort_order' => $custom_field['sort_order']
            );

        }
        // System Users
        $this->load->model('user/user');
        $filter_data = array(
            'sort' => 'username',
            'order' => 'ASC'
        );
        $data['system_users'] = $this->model_user_user->getUsers($filter_data);

        // System Users
        $data['quotation_statuses'] = $this->model_codevoc_b2bmanager_quotation->getQuotationStatuses();
        //attachment
        // controller manage file
        if (isset($this->request->get['quotation_id'])) {
            $files = $this->model_codevoc_b2bmanager_quotation->getFilesDetail($this->request->get['quotation_id']);
            if ($files) {
                $file_name_array = array();
                $file_array = array();
                foreach ($files as $file) {
                    $file_name_array[] = $file['filename'];
                    $path = DIR_OPENCART . "uploads/temp/" . $file['filename'];
                    $file_url =  HTTP_CATALOG . "uploads/temp/"  . rawurlencode($file['filename']);
                    $url = $this->url->link('codevoc/b2bmanager_quotation.downloadfile', 'user_token=' . $this->session->data['user_token'] . "&file_id=" . $file['id'] . "&quotation_id=" . $this->request->get['quotation_id'], true);
                    $file_array[] = array(
                        'name' => $file['filename'],
                        'original_name' => $file['original_filename'],
                        'size' => @filesize($path),
                        'type' => @mime_content_type($path),
                        'url' => $url,
                       // download url
                        'fileurl' => $file_url,
                        'deleteUrl' => '',
                        'deleteType' => ''
                    );
                }
                $file_data = implode('__|__', $file_name_array);
                $data['files_data'] = $file_data;
                $data['files'] = json_encode($file_array);
            } else {
                $data['files_data'] = '';
                $data['files'] = json_encode(array());
            }
        } else {
            $data['files_data'] = '';
            $data['files'] = json_encode(array());
        }

        //attachment

        $this->load->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        $this->load->model('localisation/currency');

        $data['currencies'] = $this->model_localisation_currency->getCurrencies();

        $data['voucher_min'] = $this->config->get('config_voucher_min');

        $this->load->model('sale/voucher_theme');

        $data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

        // API login
        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

        // API login
        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'codevoc/b2bmanager_quotation')) {
            $session = new \Opencart\System\Library\Session($this->config->get('session_engine'), $this->registry);

            $session->start();

            $this->model_user_api->deleteSessionBySessionId($session->getId());

            $this->model_user_api->addSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

            $session->data['api_id'] = $api_info['api_id'];

            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
		// echo "<pre>"; print_r($data['custom_fields']); die;
        $data['link_customproduct'] = 'index.php?route=codevoc/b2bmanager_customproduct&user_token=' . $this->session->data['user_token'];
        $this->response->setOutput($this->load->view('codevoc/b2bmanager_quotationform', $data));
    } 

	public function productsautocomplete() 
    {
		$json = array();
		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/manufacturer');
			$this->load->model('codevoc/b2bmanager_quotation');
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}
			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 100;
			}
			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);
			$results = $this->model_codevoc_b2bmanager_quotation->getFindproducts($filter_data);
			foreach ($results as $result)
			{
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);
				if ($manufacturer_info)
				{
					$manufacturer = $manufacturer_info['name'];
				}
				else
				{
					$manufacturer = '';
				}
				$productstr='';
				if($manufacturer!='')
                {
					$productstr='['.$result['model'].'] > '.' ['.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).'] - ['.$manufacturer.']';
					// $productstr='<span class="autocomplete-dropdown model">'.$result['model'].'</span> '.' <span class="autocomplete-dropdown name">'.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).'</span> <span class="autocomplete-dropdown price">'.number_format($result['price'], 2).' </span> '.'<span class="autocomplete-dropdown manufactor">'.$manufacturer.'</span>';
				}
				else
				{
					// $productstr='<span class="autocomplete-dropdown model">'.$result['model'].'</span> '.' <span class="autocomplete-dropdown name">'.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).'</span> <span class="autocomplete-dropdown price">'.number_format($result['price'], 2).'</span> ';
					$productstr='['.$result['model'].'] > '.' ['.strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')).']';
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name' =>$productstr,
					'model'      => $result['model'],
					'product_name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'price' => $result['price']
				);
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function loadproduct()
	{
		$json=array();

		$this->load->model('catalog/product');
		$this->load->model('codevoc/b2bmanager_quotation');
		//v4
		$this->load->model('customer/customer');
		$discounts = $this->model_catalog_product->getDiscounts($this->request->get['product_id']);
		$customer_id = (isset($this->request->get['customer_id']) && !empty($this->request->get['customer_id'])) ? $this->request->get['customer_id'] : '';
		//v4
		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		$options = $this->model_codevoc_b2bmanager_quotation->getProductOptions($this->request->get['product_id']);
		$json['options'] = $options;
		if (!empty($product_info))
		{
			//$json['price']  = $product_info['price']; discount price calculation OLD CODE
			
			//discount price calculation
			$query_price = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$this->request->get['product_id'] . "'");
			$json['price']  = $query_price->row['price'];	
			$discount_price=0.00;
			$original_price  = $query_price->row['price'];	
			$json['oprice']  = $query_price->row['price'];
			$founddiscount=0;		
		  	//discount price calculation
			//v4
					 $discount_val = 0.0;
					 if($customer_id != "")
					 {
					  	$customer_info = $this->model_customer_customer->getCustomer($customer_id);
						foreach ($discounts as $discount)
						{
                    		if (intval($this->request->get['quantity']) >= intval($discount['quantity'])&& $discount['customer_group_id'] == $customer_info['customer_group_id'])
							{
                      		 //$json['price'] = $discount['price']; discount price calculation OLD CODE
							   //discount price calculation	
							   $founddiscount=1;
							   $discount_price=$discount['price'];
							   //discount price calculation
							}
                		}
					}
			//v4

			//discount price calculation
			if($discount_price>0 && $founddiscount>0)
			{
				$json['price_discount_percentage']=($original_price-$discount_price)/$original_price*100;
			}
			else
			{
				$json['price_discount_percentage'] =$discount_val;
			}
			
			$this->load->model('localisation/tax_class');
			$tax_rules = $this->model_localisation_tax_class->getTaxRules($product_info['tax_class_id']);
			$this->load->model('localisation/tax_rate');
			$total_tax = 0;	
			foreach($tax_rules as $tax_rule){
				$tax_rates = $this->model_localisation_tax_rate->getTaxRate($tax_rule['tax_rate_id']);
				$tax_rate_info[] = array(
					'name'  => $tax_rates['name'],
					'rate'  => $tax_rates['rate'],
					'based' => $tax_rule['based']
				);
				$total_tax = $total_tax + $tax_rates['rate'];
			}
			$json['total_tax'] = $total_tax;
			//discount price calculation
			$json['tax_class_id']  = $product_info['tax_class_id'];
		}
		if($product_info){$json['success']=1;}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getpoprice()
	{
		$json=array();
		//discount price calculation	
		$this->load->model('catalog/product');
		$this->load->model('codevoc/b2bmanager_quotation');
		$this->load->model('customer/customer');
		$discounts = $this->model_catalog_product->getDiscounts($this->request->get['product_id']);
		$customer_id = (isset($this->request->get['customer_id']) && !empty($this->request->get['customer_id'])) ? $this->request->get['customer_id'] : '';
		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		$discount_price=0.00;
		$foundiscount=0;
		$query_price = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$this->request->get['product_id'] . "'");
		$original_price  = $query_price->row['price'];		
		 			 $discount_val = 0.0;
					 if($customer_id != "")
					 {
					  	$customer_info = $this->model_customer_customer->getCustomer($customer_id);
						foreach ($discounts as $discount)
						{
                    		if (intval($this->request->get['quantity']) >= intval($discount['quantity'])&& $discount['customer_group_id'] == $customer_info['customer_group_id'])
							{
                      		 $discount_price=$discount['price'];
							 $foundiscount=1;
							}
                		}
					}
		
		if(isset($this->request->post['order_products']))
		{
				if(array_key_exists($this->request->get['rowcount'],$this->request->post['order_products']))
				{		
					if(array_key_exists('option',$this->request->post['order_products'][$this->request->get['rowcount']]))
					{		
						foreach($this->request->post['order_products'][$this->request->get['rowcount']]['option'] as $option_s)
						{
							$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "product_option_value where product_option_value_id = '".$option_s."'");
							if($query->num_rows>0)
			 				{
								
									if ($query->row['price_prefix'] == '+') {
										$original_price +=$query->row['price'];
										$discount_price +=$query->row['price'];
									} elseif ($query->row['price_prefix'] == '-') {
										$original_price -=$query->row['price'];
										$discount_price -=$query->row['price'];
									}
							
							}	
														
						}	
					}	
				}		
		}						
		//discount price calculation		
		if(isset($this->request->get['product_option_value_id']))
		{
			 $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "product_option_value where product_option_value_id = '".$this->request->get['product_option_value_id']."'");
			 if($query->num_rows>0)
			 {

									if ($query->row['price_prefix'] == '+') {
										$option_price = '+_'.$query->row['price'];
										$json['success']=$option_price;
									} elseif ($query->row['price_prefix'] == '-') {
										$option_price = '-_'.$query->row['price'];
										$json['success']=$option_price;
									}
			}
			else
			{
				$json['success']='';
			}
		}
		else
		{
			$json['success']='';
		}
		//discount price calculation	
		if($discount_price>0 && $foundiscount>0)
		{
			$json['price_discount_percentage']=($original_price-$discount_price)/$original_price*100;
		}
		else
		{
			$json['price_discount_percentage'] =0;
		}
		//discount price calculation		
		$this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
	}

	public function getShippingMethods(){

		$this->load->language('extension/shipping');
		$this->load->model('setting/extension');
		$results = $this->model_setting_extension->getPaths('%/admin/controller/shipping/%.php');

		foreach ($results as $result) {
			$available[] = basename($result['path'], '.php');
		}

		$extensions = $this->model_setting_extension->getExtensionsByType('shipping');

		foreach ($extensions as $extension) {
			if (in_array($extension['code'], $available)) {
				$installed[] = $extension['code'];
			} else {
				$this->model_setting_extension->uninstall('shipping', $extension['code']);
			}
		}
		$data['extensions'] = [];

		if ($results) {
			$this->load->model('setting/setting');
			foreach ($results as $result) {
				$extension = substr($result['path'], 0, strpos($result['path'], '/'));

				$code = basename($result['path'], '.php');

				$this->load->language('extension/' . $extension . '/shipping/' . $code, $code);

				$shipping_cost = $this->model_setting_setting->getSetting('shipping_'.$code);
				if(array_key_exists('shipping_'.$code.'_cost',$shipping_cost)){
					$cost = $shipping_cost['shipping_'.$code.'_cost'];
					$tax_class_id = $shipping_cost['shipping_'.$code.'_tax_class_id'];
				}else{
					$cost = '';
					$tax_class_id = '';
				}

				$this->load->model('localisation/tax_class');
				$tax_rules = $this->model_localisation_tax_class->getTaxRules((int)$tax_class_id);
				$this->load->model('localisation/tax_rate');
				$total_tax = 0;	
				foreach($tax_rules as $tax_rule){
					$tax_rates = $this->model_localisation_tax_rate->getTaxRate($tax_rule['tax_rate_id']);
					$total_tax = $total_tax + $tax_rates['rate'];
				}

				$cost_with_tax = (float)$cost + ((float)$cost*(float)$total_tax/100); 

				$data['extensions'][] = [
					'name'       => $this->language->get($code . '_heading_title'),
					'status'     => $this->config->get('shipping_' . $code . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('shipping_' . $code . '_sort_order'),
					'install'    => $this->url->link('extension/shipping.install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'uninstall'  => $this->url->link('extension/shipping.uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'installed'  => in_array($code, $installed),
					'code'		 => $code,
					'cost'		 => $cost,
					'total_cost' => $cost_with_tax,
					'tax_rate'	 => $total_tax,	
					'edit'       => $this->url->link('extension/' . $extension . '/shipping/' . $code, 'user_token=' . $this->session->data['user_token'])
				];
			}
		}
        return $data['extensions'];

	}

	public function getPaymentMethods() {
		$this->load->language('extension/payment');

		$available = [];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getPaths('%/admin/controller/payment/%.php');

		foreach ($results as $result) {
			$available[] = basename($result['path'], '.php');
		}

		$installed = [];

		$extensions = $this->model_setting_extension->getExtensionsByType('payment');

		foreach ($extensions as $extension) {
			if (in_array($extension['code'], $available)) {
				$installed[] = $extension['code'];
			} else {
				$this->model_setting_extension->uninstall('payment', $extension['code']);
			}
		}

		$data['extensions'] = [];

		if ($results) {
			foreach ($results as $result) {
				$extension = substr($result['path'], 0, strpos($result['path'], '/'));

				$code = basename($result['path'], '.php');

				$this->load->language('extension/' . $extension . '/payment/' . $code, $code);

				$text_link = $this->language->get($code . '_text_' . $code);

				if ($text_link != $code . '_text_' . $code) {
					$link = $text_link;
				} else {
					$link = '';
				}

				$data['extensions'][] = [
					'name'       => $this->language->get($code . '_heading_title'),
					'link'       => $link,
					'status'     => $this->config->get('payment_' . $code . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('payment_' . $code . '_sort_order'),
					'install'    => $this->url->link('extension/payment.install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'uninstall'  => $this->url->link('extension/payment.uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
					'installed'  => in_array($code, $installed),
					'code'		 => $code,
					'edit'       => $this->url->link('extension/' . $extension . '/payment/' . $code, 'user_token=' . $this->session->data['user_token'])
				];
			}
		}

		return $data['extensions'];
	}

	public function addQuotation(){
		//custom data
		$order_data = array();
		if(isset($this->request->post['assignee'])){$order_data['assignee']=$this->request->post['assignee'];}else{$order_data['assignee']='';}
		if(isset($this->request->post['vatnr'])){$order_data['vatnr']=$this->request->post['vatnr'];}else{$order_data['vatnr']='';}
		if(isset($this->request->post['payment_company'])){$order_data['payment_company']=$this->request->post['payment_company'];}else{$order_data['payment_company']='';}
		if(isset($this->request->post['shipping_company'])){$order_data['shipping_company']=$this->request->post['shipping_company'];}else{$order_data['shipping_company']='';}
		if(isset($this->request->post['create_date'])&& $this->request->post['create_date'] != null){$order_data['create_date']=date("Y-m-d",strtotime($this->request->post['create_date']));}else{$order_data['create_date']=date("Y-m-d");}
		if(isset($this->request->post['expiration_date'])&& $this->request->post['expiration_date'] != null){$order_data['expiration_date']=date("Y-m-d",strtotime($this->request->post['expiration_date']));}else{$order_data['expiration_date']=date("Y-m-d");}
		if(isset($this->request->post['shippment_terms'])){$order_data['shippment_terms']=$this->request->post['shippment_terms'];}else{$order_data['shippment_terms']='';}
		if(isset($this->request->post['rate_delay'])){$order_data['rate_delay']=$this->request->post['rate_delay'];}else{$order_data['rate_delay']='';}
		if(isset($this->request->post['custom_ordernr'])){$order_data['custom_ordernr']=$this->request->post['custom_ordernr'];}else{$order_data['custom_ordernr']='';}

		$order_data['customer_id'] = $this->request->post['customer_id'];
		$order_data['vatnr'] = isset($this->request->post['vatnr']) ? $this->request->post['vatnr'] : '' ;
		$order_data['firstname'] = $this->request->post['input_firstname'];
		$order_data['lastname'] = $this->request->post['input_lastname'];
		$order_data['email'] = $this->request->post['email'];
		$order_data['telephone'] = $this->request->post['telephone'];
		$order_data['custom_field'] = $this->request->post['custom_field'];
		if(array_key_exists('payment_address',$this->request->post) && $this->request->post['payment_address'] != 0){
			$order_data['payment_address_id'] = $this->request->post['payment_address'];
			$this->load->model('customer/customer');
			$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			$order_data['payment_lastname'] = $this->request->post['input_payment_lastname'];
			$order_data['payment_company'] = $this->request->post['input_payment_company'];
			$order_data['payment_address_1'] = $this->request->post['input_payment_address_1'];
			$order_data['payment_address_2'] = $this->request->post['input_payment_address_2'];
			$order_data['payment_city'] = $this->request->post['input_payment_city'];
			$order_data['payment_postcode'] = $this->request->post['input_payment_postcode'];
			$order_data['payment_custom_field'] = $this->request->post['payment_custom_field'];
			$this->load->model('localisation/zone');
			$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
			$order_data['payment_zone'] = $payment_zone['name'];
			$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
			$this->load->model('localisation/country');
			$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
			$order_data['payment_country'] = $payment_country['name'];
			$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
		}else{
			$order_data['payment_address_id'] = '';
			if(isset($this->request->post['input_payment_firstname'])){
				$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			}else{
				$order_data['payment_firstname'] = '';
			}
			if(isset($this->request->post['input_payment_firstname'])){
				$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			}else{
				$order_data['payment_firstname'] = '';
			}
			if(isset($this->request->post['input_payment_lastname'])){
				$order_data['payment_lastname'] = $this->request->post['input_payment_lastname'];
			}else{
				$order_data['payment_lastname'] = '';
			}
			if(isset($this->request->post['input_payment_company'])){
				$order_data['payment_company'] = $this->request->post['input_payment_company'];
			}else{
				$order_data['payment_company'] = '';
			}
			if(isset($this->request->post['input_payment_address_1'])){
				$order_data['payment_address_1'] = $this->request->post['input_payment_address_1'];
			}else{
				$order_data['payment_address_1'] = '';
			}
			if(isset($this->request->post['input_payment_address_2'])){
				$order_data['payment_address_2'] = $this->request->post['input_payment_address_2'];
			}else{
				$order_data['payment_address_2'] = '';
			}
			if(isset($this->request->post['input_payment_city'])){
				$order_data['payment_city'] = $this->request->post['input_payment_city'];
			}else{
				$order_data['payment_city'] = '';
			}
			if(isset($this->request->post['input_payment_postcode'])){
				$order_data['payment_postcode'] = $this->request->post['input_payment_postcode'];
			}else{
				$order_data['payment_postcode'] = '';
			}
			if(array_key_exists("input_payment_zone_id",$this->request->post)){
				$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
				$this->load->model('localisation/zone');
				$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
				$order_data['payment_zone'] = $payment_zone['name'];
			}else{
				$order_data['payment_zone_id'] = '';
				$order_data['payment_zone'] = '';
			}
			if($this->request->post['input_payment_country_id'] != ''){
				$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
				$this->load->model('localisation/country');
				$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
				$order_data['payment_country'] = $payment_country['name'];
			}else{
				$order_data['payment_country_id'] = '';
				$order_data['payment_country'] = '';
			}
			if(isset($this->request->post['payment_custom_field'])){
				$order_data['payment_custom_field'] = $this->request->post['payment_custom_field'];
			}else{
				$order_data['payment_custom_field'] = '';
			}
		}
		if(array_key_exists('shipping_address',$this->request->post) && $this->request->post['shipping_address']!= 0){
			$order_data['shipping_address_id'] = $this->request->post['shipping_address'];
			$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			$order_data['shipping_lastname'] = $this->request->post['input_shipping_lastname'];
			$order_data['shipping_company'] = $this->request->post['input_shipping_company'];
			$order_data['shipping_address_1'] = $this->request->post['input_shipping_address_1'];
			$order_data['shipping_address_2'] = $this->request->post['input_shipping_address_2'];
			$order_data['shipping_city'] = $this->request->post['input_shipping_city'];
			$order_data['shipping_postcode'] = $this->request->post['input_shipping_postcode'];
			$order_data['shipping_custom_field'] = $this->request->post['shipping_custom_field'];
			$this->load->model('localisation/zone');
			$shipping_zone = $this->model_localisation_zone->getZone($this->request->post['input_shipping_country_id']);
			$order_data['shipping_zone'] = $shipping_zone['name'];
			$order_data['shipping_zone_id'] = $this->request->post['input_shipping_zone_id'];
			$this->load->model('localisation/country');
			$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_shipping_country_id']);
			$order_data['shipping_country'] = $payment_country['name'];
			$order_data['shipping_country_id'] = $this->request->post['input_shipping_country_id'];
		}else{
			$order_data['shipping_address_id'] = '';
			if(isset($this->request->post['input_shipping_firstname'])){
				$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			}else{
				$order_data['shipping_firstname'] = '';
			}
			if(isset($this->request->post['input_shipping_firstname'])){
				$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			}else{
				$order_data['shipping_firstname'] = '';
			}
			if(isset($this->request->post['input_shipping_lastname'])){
				$order_data['shipping_lastname'] = $this->request->post['input_shipping_lastname'];
			}else{
				$order_data['shipping_lastname'] = '';
			}
			if(isset($this->request->post['input_shipping_company'])){
				$order_data['shipping_company'] = $this->request->post['input_shipping_company'];
			}else{
				$order_data['shipping_company'] = '';
			}
			if(isset($this->request->post['input_shipping_address_1'])){
				$order_data['shipping_address_1'] = $this->request->post['input_shipping_address_1'];
			}else{
				$order_data['shipping_address_1'] = '';
			}
			if(isset($this->request->post['input_shipping_address_2'])){
				$order_data['shipping_address_2'] = $this->request->post['input_shipping_address_2'];
			}else{
				$order_data['shipping_address_2'] = '';
			}
			if(isset($this->request->post['input_shipping_city'])){
				$order_data['shipping_city'] = $this->request->post['input_shipping_city'];
			}else{
				$order_data['shipping_city'] = '';
			}
			if(isset($this->request->post['input_shipping_postcode'])){
				$order_data['shipping_postcode'] = $this->request->post['input_shipping_postcode'];
			}else{
				$order_data['shipping_postcode'] = '';
			}
			if(array_key_exists("input_shipping_zone_id",$this->request->post)){
				$order_data['shipping_zone_id'] = $this->request->post['input_shipping_zone_id'];
				$this->load->model('localisation/zone');
				$shipping_zone = $this->model_localisation_zone->getZone($this->request->post['input_shipping_zone_id']);
				$order_data['shipping_zone'] = $shipping_zone['name'];
			}else{
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_zone'] = '';
			}
			if($this->request->post['input_shipping_country_id'] != ''){
				$order_data['shipping_country_id'] = $this->request->post['input_shipping_country_id'];
				$this->load->model('localisation/country');
				$shipping_country = $this->model_localisation_country->getCountry($this->request->post['input_shipping_country_id']);
				$order_data['shipping_country'] = $shipping_country['name'];
			}else{
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_country'] = '';
			}
			if(isset($this->request->post['shipping_custom_field'])){
				$order_data['shipping_custom_field'] = $this->request->post['shipping_custom_field'];
			}else{
				$order_data['shipping_custom_field'] = '';
			}
		}
		if (isset($this->request->post['shipping_method'])) {
			$order_data['shipping_method'] = $this->request->post['shipping_method'];
		} else {	
			$order_data['shipping_method'] = '';
		}
		$order_data['quotation_status_id'] = $this->request->post['quotation_status_id'];
		$order_data['comment'] = $this->request->post['comment'];
		$order_data['total'] = str_replace(',','',$this->request->post['total']);
		$order_data['file_final_sketch'] = $this->request->post['file_final_sketch'];
		if (isset($this->request->post['payment_method'])) {
			$order_data['payment_method'] = $this->request->post['payment_method'];
		} else {	
			$order_data['payment_method'] = '';
		}

		$order_data['products'] = array();

		$this->load->model('codevoc/b2bmanager_quotation');
		foreach ($this->request->post['order_products'] as $product) {
			$option_data = array();

			$product_info = $this->model_codevoc_b2bmanager_quotation->getProductOptions($product['product_id']);
			
			foreach ($product_info as $option) {
				if(array_key_exists($option['product_option_id'],$product['option'])){	
					foreach($option['product_option_value'] as $product_option_value){
						if($product['option'][$option['product_option_id']] != '' && $product_option_value['product_option_value_id'] != '' && $product['option'][$option['product_option_id']] == $product_option_value['product_option_value_id'] ){
							$product_option_value_id = $product_option_value['product_option_value_id'];
							$option_value_id = $product_option_value['option_value_id'];
							$option_value = $product_option_value['option_value'];
						}else{
							$product_option_value_id = $product['option'][$option['product_option_id']];
							$option_value_id = $product_option_value['option_value_id'];
							$option_value = $product_option_value['option_value'];
						}
					}
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $product_option_value_id,
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option_value_id,
						'name'                    => $option['name'],
						'value'                   => $option_value,
						'type'                    => $option['type']
					);
				}
			}
			if($product['name']){
				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					// 'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					// 'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'discount'=> $product['price_discount_percentage'],//B2B Manager
					// 'b2b_product_tax_class_id'=> $product['b2b_product_tax_class_id'],//B2B Manager
					'b2b_product_sort_order'=> $product['sort_order'],//B2B Manager
					'total'      => $product['total'],
					'tax'        => $product['total_tax_to_prod'],
					'tax_class_id'=> $product['tax_class_id']
					// 'reward'     => $product['reward']
				);
			}
		}

		//attachment
		if (isset($this->request->post['files_data'])) {
			$files_data = $this->request->post['files_data'];
			if ($files_data != "") {
				$file_name_array = explode('__|__', $files_data);
				$file_array = array();
				foreach ($file_name_array as $file) {
					$file_name_array[] = $file;
					$path = DIR_OPENCART . "uploads/temp/" . $file;
					
					$file_url = DIR_OPENCART . "uploads/temp/" . rawurlencode($file);
					$url = $this->url->link('codevoc/b2bmanager_quotation.downloadfile', 'user_token=' . $this->session->data['user_token'] . "&file_id=" . $file, true);
					$file_array[] = array(
						'name' => $file,
						'original_name' => $file,
						'language_id' => $this->config->get('config_language_id'),
						'size' => @filesize($path),
						'type' => @mime_content_type($path),
						'url' => $url, // download url
						'fileurl' => $file_url,
						'deleteUrl' => '',
						'deleteType' => ''
					);
				}
				$file_data = implode('__|__', $file_name_array);
				$order_data['files_data'] = $file_data;
				$order_data['files'] =$file_array;
			}
		} else {
			$order_data['files_data'] = '';
			$order_data['files'] = json_encode(array());
		}
		// Order Totals
		$order_data['totals']['sub_total'] = array(
			'code'  => 'sub_total',
			'title' => 'Sub-Total',
			'value' => isset($this->request->post['sub_total']) ? str_replace(",","",$this->request->post['sub_total']): '',
			'sort_order' => 1
		); 

		$order_data['totals']['shipping'] = array(
			'code'  => 'shipping_cost',
			'title' => 'Shipping',
			'value' =>  isset($this->request->post['shipping_cost']) ? str_replace(",","",$this->request->post['shipping_cost']): '',
			'sort_order' => 3
		);

		$order_data['totals']['tax'] = array(
			'code'  => 'tax',
			'title' => 'Tax',
			'value' => isset($this->request->post['tax']) ? str_replace(",","",$this->request->post['tax']): '',
			'sort_order' => 5
		); 

		$order_data['totals']['total'] = array(
			'code'  => 'total',
			'title' => 'Total',
			'value' => isset($this->request->post['total']) ? str_replace(",","",$this->request->post['total']): '',
			'sort_order' => 9
		); 

		$order_data['language_id'] = $this->config->get('config_language_id');

		$json['quotation_id'] = $this->model_codevoc_b2bmanager_quotation->addQuotation($order_data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function editQuotation(){
		//custom data
		// print_r($this->request->post); die;
		$order_data = array();
		if (isset($this->request->get['quotation_id'])) {
			$quotation_id = $this->request->get['quotation_id'];
		} else {
			$quotation_id = 0;
		}

		if(isset($this->request->post['assignee'])){$order_data['assignee']=$this->request->post['assignee'];}else{$order_data['assignee']='';}
		if(isset($this->request->post['vatnr'])){$order_data['vatnr']=$this->request->post['vatnr'];}else{$order_data['vatnr']='';}
		if(isset($this->request->post['payment_company'])){$order_data['payment_company']=$this->request->post['payment_company'];}else{$order_data['payment_company']='';}
		if(isset($this->request->post['shipping_company'])){$order_data['shipping_company']=$this->request->post['shipping_company'];}else{$order_data['shipping_company']='';}
		if(isset($this->request->post['create_date'])&& $this->request->post['create_date'] != null){$order_data['create_date']=date("Y-m-d",strtotime($this->request->post['create_date']));}else{$order_data['create_date']=date("Y-m-d");}
		if(isset($this->request->post['expiration_date'])&& $this->request->post['expiration_date'] != null){$order_data['expiration_date']=date("Y-m-d",strtotime($this->request->post['expiration_date']));}else{$order_data['expiration_date']=date("Y-m-d");}
		if(isset($this->request->post['shippment_terms'])){$order_data['shippment_terms']=$this->request->post['shippment_terms'];}else{$order_data['shippment_terms']='';}
		if(isset($this->request->post['rate_delay'])){$order_data['rate_delay']=$this->request->post['rate_delay'];}else{$order_data['rate_delay']='';}
		if(isset($this->request->post['custom_ordernr'])){$order_data['custom_ordernr']=$this->request->post['custom_ordernr'];}else{$order_data['custom_ordernr']='';}

		$order_data['customer_id'] = $this->request->post['customer_id'];
		$order_data['vatnr'] = isset($this->request->post['vatnr']) ? $this->request->post['vatnr'] : '' ;
		$order_data['firstname'] = $this->request->post['input_firstname'];
		$order_data['lastname'] = $this->request->post['input_lastname'];
		$order_data['email'] = $this->request->post['email'];
		$order_data['telephone'] = $this->request->post['telephone'];
		$order_data['custom_field'] = $this->request->post['custom_field'];
		if(array_key_exists('payment_address',$this->request->post) && $this->request->post['payment_address'] != 0){
			$order_data['payment_address_id'] = $this->request->post['payment_address'];
			$this->load->model('customer/customer');
			$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			$order_data['payment_lastname'] = $this->request->post['input_payment_lastname'];
			$order_data['payment_company'] = $this->request->post['input_payment_company'];
			$order_data['payment_address_1'] = $this->request->post['input_payment_address_1'];
			$order_data['payment_address_2'] = $this->request->post['input_payment_address_2'];
			$order_data['payment_city'] = $this->request->post['input_payment_city'];
			$order_data['payment_postcode'] = $this->request->post['input_payment_postcode'];
			$order_data['payment_custom_field'] = $this->request->post['payment_custom_field'];
			$this->load->model('localisation/zone');
			$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
			$order_data['payment_zone'] = $payment_zone['name'];
			$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
			$this->load->model('localisation/country');
			$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
			$order_data['payment_country'] = $payment_country['name'];
			$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
		}else{
			$order_data['payment_address_id'] = '';
			if(isset($this->request->post['input_payment_firstname'])){
				$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			}else{
				$order_data['payment_firstname'] = '';
			}
			if(isset($this->request->post['input_payment_firstname'])){
				$order_data['payment_firstname'] = $this->request->post['input_payment_firstname'];
			}else{
				$order_data['payment_firstname'] = '';
			}
			if(isset($this->request->post['input_payment_lastname'])){
				$order_data['payment_lastname'] = $this->request->post['input_payment_lastname'];
			}else{
				$order_data['payment_lastname'] = '';
			}
			if(isset($this->request->post['input_payment_company'])){
				$order_data['payment_company'] = $this->request->post['input_payment_company'];
			}else{
				$order_data['payment_company'] = '';
			}
			if(isset($this->request->post['input_payment_address_1'])){
				$order_data['payment_address_1'] = $this->request->post['input_payment_address_1'];
			}else{
				$order_data['payment_address_1'] = '';
			}
			if(isset($this->request->post['input_payment_address_2'])){
				$order_data['payment_address_2'] = $this->request->post['input_payment_address_2'];
			}else{
				$order_data['payment_address_2'] = '';
			}
			if(isset($this->request->post['input_payment_city'])){
				$order_data['payment_city'] = $this->request->post['input_payment_city'];
			}else{
				$order_data['payment_city'] = '';
			}
			if(isset($this->request->post['input_payment_postcode'])){
				$order_data['payment_postcode'] = $this->request->post['input_payment_postcode'];
			}else{
				$order_data['payment_postcode'] = '';
			}
			if( array_key_exists('input_payment_zone_id', $this->request->post) && $this->request->post['input_payment_zone_id'] != 0){
				$order_data['payment_zone_id'] = $this->request->post['input_payment_zone_id'];
				$this->load->model('localisation/zone');
				$payment_zone = $this->model_localisation_zone->getZone($this->request->post['input_payment_zone_id']);
				$order_data['payment_zone'] = $payment_zone['name'];
			}else{
				$order_data['payment_zone_id'] = '';
				$order_data['payment_zone'] = '';
			}
			if($this->request->post['input_payment_country_id'] != ''){
				$order_data['payment_country_id'] = $this->request->post['input_payment_country_id'];
				$this->load->model('localisation/country');
				$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_payment_country_id']);
				$order_data['payment_country'] = $payment_country['name'];
			}else{
				$order_data['payment_country_id'] = '';
				$order_data['payment_country'] = '';
			}
			if(isset($this->request->post['payment_custom_field'])){
				$order_data['payment_custom_field'] = $this->request->post['payment_custom_field'];
			}else{
				$order_data['payment_custom_field'] = '';
			}
		}
		if(array_key_exists('shipping_address',$this->request->post) && $this->request->post['shipping_address'] != 0){
			$order_data['shipping_address_id'] = $this->request->post['shipping_address'];
			$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			$order_data['shipping_lastname'] = $this->request->post['input_shipping_lastname'];
			$order_data['shipping_company'] = $this->request->post['input_shipping_company'];
			$order_data['shipping_address_1'] = $this->request->post['input_shipping_address_1'];
			$order_data['shipping_address_2'] = $this->request->post['input_shipping_address_2'];
			$order_data['shipping_city'] = $this->request->post['input_shipping_city'];
			$order_data['shipping_postcode'] = $this->request->post['input_shipping_postcode'];
			$order_data['shipping_custom_field'] = $this->request->post['shipping_custom_field'];
			$this->load->model('localisation/zone');
			$shipping_zone = $this->model_localisation_zone->getZone($this->request->post['input_shipping_country_id']);
			$order_data['shipping_zone'] = $shipping_zone['name'];
			$order_data['shipping_zone_id'] = array_key_exists('input_shipping_zone_id',$this->request->post) ? $this->request->post['input_shipping_zone_id'] : 0;
			$this->load->model('localisation/country');
			$payment_country = $this->model_localisation_country->getCountry($this->request->post['input_shipping_country_id']);
			$order_data['shipping_country'] = $payment_country['name'];
			$order_data['shipping_country_id'] = $this->request->post['input_shipping_country_id'];
		}else{
			$order_data['shipping_address_id'] = '';
			if(isset($this->request->post['input_shipping_firstname'])){
				$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			}else{
				$order_data['shipping_firstname'] = '';
			}
			if(isset($this->request->post['input_shipping_firstname'])){
				$order_data['shipping_firstname'] = $this->request->post['input_shipping_firstname'];
			}else{
				$order_data['shipping_firstname'] = '';
			}
			if(isset($this->request->post['input_shipping_lastname'])){
				$order_data['shipping_lastname'] = $this->request->post['input_shipping_lastname'];
			}else{
				$order_data['shipping_lastname'] = '';
			}
			if(isset($this->request->post['input_shipping_company'])){
				$order_data['shipping_company'] = $this->request->post['input_shipping_company'];
			}else{
				$order_data['shipping_company'] = '';
			}
			if(isset($this->request->post['input_shipping_address_1'])){
				$order_data['shipping_address_1'] = $this->request->post['input_shipping_address_1'];
			}else{
				$order_data['shipping_address_1'] = '';
			}
			if(isset($this->request->post['input_shipping_address_2'])){
				$order_data['shipping_address_2'] = $this->request->post['input_shipping_address_2'];
			}else{
				$order_data['shipping_address_2'] = '';
			}
			if(isset($this->request->post['input_shipping_city'])){
				$order_data['shipping_city'] = $this->request->post['input_shipping_city'];
			}else{
				$order_data['shipping_city'] = '';
			}
			if(isset($this->request->post['input_shipping_postcode'])){
				$order_data['shipping_postcode'] = $this->request->post['input_shipping_postcode'];
			}else{
				$order_data['shipping_postcode'] = '';
			}
			if(array_key_exists('input_shipping_zone_id', $this->request->post) && $this->request->post['input_shipping_zone_id'] != 0){
				$order_data['shipping_zone_id'] = $this->request->post['input_shipping_zone_id'];
				$this->load->model('localisation/zone');
				$shipping_zone = $this->model_localisation_zone->getZone($this->request->post['input_shipping_zone_id']);
				$order_data['shipping_zone'] = $shipping_zone['name'];
			}else{
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_zone'] = '';
			}
			if($this->request->post['input_shipping_country_id'] != ''){
				$order_data['shipping_country_id'] = $this->request->post['input_shipping_country_id'];
				$this->load->model('localisation/country');
				$shipping_country = $this->model_localisation_country->getCountry($this->request->post['input_shipping_country_id']);
				$order_data['shipping_country'] = $shipping_country['name'];
			}else{
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_country'] = '';
			}
			if(isset($this->request->post['shipping_custom_field'])){
				$order_data['shipping_custom_field'] = $this->request->post['shipping_custom_field'];
			}else{
				$order_data['shipping_custom_field'] = '';
			}
		}
		if (isset($this->request->post['shipping_method'])) {
			$order_data['shipping_method'] = $this->request->post['shipping_method'];
		} else {	
			$order_data['shipping_method'] = '';
		}
		$order_data['quotation_status_id'] = $this->request->post['quotation_status_id'];
		$order_data['comment'] = $this->request->post['comment'];
		$order_data['file_final_sketch'] = $this->request->post['file_final_sketch'];
		$order_data['total'] = str_replace(',','',$this->request->post['total']);
		if (isset($this->request->post['payment_method'])) {
			$order_data['payment_method'] = $this->request->post['payment_method'];
		} else {	
			$order_data['payment_method'] = '';
		}
		$order_data['products'] = array();
		$this->load->model('codevoc/b2bmanager_quotation');
		foreach ($this->request->post['order_products'] as $product) {
			$option_data = array();
			
			$product_info = $this->model_codevoc_b2bmanager_quotation->getProductOptions($product['product_id']);
			
			foreach ($product_info as $option) {
				if(array_key_exists($option['product_option_id'],$product['option'])){	
					foreach($option['product_option_value'] as $product_option_value){
						if($product['option'][$option['product_option_id']] != '' && $product_option_value['product_option_value_id'] != '' && $product['option'][$option['product_option_id']] == $product_option_value['product_option_value_id'] ){
							$product_option_value_id = $product_option_value['product_option_value_id'];
							$option_value_id = $product_option_value['option_value_id'];
							$option_value = $product_option_value['option_value'];
						}else{
							$product_option_value_id = $product['option'][$option['product_option_id']];
							$option_value_id = $product_option_value['option_value_id'];
							$option_value = $product_option_value['option_value'];
						}
					}
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => isset($product_option_value_id) ? $product_option_value_id :  $product['option'][$option['product_option_id']],
						'option_id'               => $option['option_id'],
						'option_value_id'         => isset($option_value_id) ? $option_value_id : $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => isset($option_value) ? $option_value : $option['option_value'],
						'type'                    => $option['type']
					);
				}
			}
			
			if($product['name']){
				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					// 'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					// 'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'discount'=> $product['price_discount_percentage'],//B2B Manager
					// 'b2b_product_tax_class_id'=> $product['b2b_product_tax_class_id'],//B2B Manager
					'b2b_product_sort_order'=> $product['sort_order'],//B2B Manager
					'total'      => $product['total'],
					'tax'        => $product['total_tax_to_prod'],
					'tax_class_id'=> $product['tax_class_id']
					// 'reward'     => $product['reward']
				);
			}
		}

		//attachment
		if (isset($this->request->post['files_data'])) {
			$files_data = $this->request->post['files_data'];
			if ($files_data != "") {
				$file_name_array = explode('__|__', $files_data);
				$file_array = array();
				foreach ($file_name_array as $file) {
					$db_files = $this->db->query("SELECT *  from " . DB_PREFIX . "codevoc_quotation_files where quotation_id = '" . (int)$quotation_id . "'");
					$db_files = $db_files->rows;
					$db_files_array = array();
					if(count($db_files) > 0){
						foreach($db_files as $file_item){
							$db_files_array[$file_item['filename']] = $file_item['original_filename'];
						}
					}
					if(array_key_exists($file,$db_files_array)){
						$file_name_array[] = $file;
						$path = DIR_OPENCART . "uploads/temp/" . $file;
						$file_url = HTTP_CATALOG . "uploads/temp/" . rawurlencode($file);
						$url = $this->url->link('codevoc/b2bmanager_quotation.downloadfile', 'user_token=' . $this->session->data['user_token'] . "&file_id=" . $file . "&quotation_id=" . $this->request->get['quotation_id'], true);
						$file_array[] = array(
							'name' => $file,
							'original_name' => $db_files_array[$file],
							'language_id' => $this->config->get('config_language_id'),
							'size' => @filesize($path),
							'type' => @mime_content_type($path),
							'url' => $url, // download url
							'fileurl' => $file_url,
							'deleteUrl' => '',
							'deleteType' => ''
						);
					}else{
						$file_name_array[] = $file;
						$path = DIR_OPENCART . "uploads/temp/" . $file;
						$file_extension = explode('.', $file);
						// Creating a unique file name
						$file_name = uniqid().".".$file_extension[1];
						$file_url = HTTP_CATALOG . "uploads/temp/" . rawurlencode($file);
						$url = $this->url->link('codevoc/b2bmanager_quotation.downloadfile', 'user_token=' . $this->session->data['user_token'] . "&file_id=" . $file . "&quotation_id=" . $this->request->get['quotation_id'], true);
						$file_array[] = array(
							'name' => $file_name,
							'original_name' => $file,
							'language_id' => $this->config->get('config_language_id'),
							'size' => @filesize($path),
							'type' => @mime_content_type($path),
							'url' => $url, // download url
							'fileurl' => $file_url,
							'deleteUrl' => '',
							'deleteType' => ''
						);
					}
				}
				$file_data = implode('__|__', $file_name_array);
				$order_data['files_data'] = $file_data;
				$order_data['files'] = $file_array;
			}
		} else {
			$order_data['files_data'] = '';
			$order_data['files'] = json_encode(array());
		}
		// Order Totals
		$order_data['totals']['sub_total'] = array(
			'code'  => 'sub_total',
			'title' => 'Sub-Total',
			'value' => isset($this->request->post['sub_total']) ? str_replace(",","",$this->request->post['sub_total']): '',
			'sort_order' => 1
		); 

		$order_data['totals']['shipping'] = array(
			'code'  => 'shipping_cost',
			'title' => 'Shipping',
			'value' =>  isset($this->request->post['shipping_cost']) ? str_replace(",","",$this->request->post['shipping_cost']): '',
			'sort_order' => 3
		);

		$order_data['totals']['tax'] = array(
			'code'  => 'tax',
			'title' => 'Tax',
			'value' => isset($this->request->post['tax']) ? str_replace(",","",$this->request->post['tax']): '',
			'sort_order' => 5
		); 

		$order_data['totals']['total'] = array(
			'code'  => 'total',
			'title' => 'Total',
			'value' => isset($this->request->post['total']) ? str_replace(",","",$this->request->post['total']): '',
			'sort_order' => 9
		); 
		
		$json['quotation_id'] = $this->model_codevoc_b2bmanager_quotation->editQuotation($quotation_id, $order_data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

		/*

     * Function to handle file upload

     */

	 public function handleUpload(){
		error_reporting(0);
		 $upload_dir = str_replace("/bossadm", "", DIR_APPLICATION) . "uploads/temp/";
		 $upload_url = HTTP_CATALOG;
		 if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
			 $upload_url = HTTPS_CATALOG;
		 }
		 $upload_url .= "uploads/temp/";
		 $options = array(
			 'upload_dir' => $upload_dir,
			 'upload_url' => $upload_url
		 );
		 $upload_handler = new JqueryFileUploadHandler($options);
	 }

	public function exportpdf(){
		$this->load->model('codevoc/b2bmanager_quotation');
        if (isset($this->request->get['quotation_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {

            $quotation_info = $this->model_codevoc_b2bmanager_quotation->getQuotation($this->request->get['quotation_id']);

            $quotation_other_details = $this->model_codevoc_b2bmanager_quotation->getQuotationOtherdetails($this->request->get['quotation_id']);
        }
        if (!empty($quotation_info)) {
			$this->load->model('localisation/country');
            $data['quotation_id'] = $this->request->get['quotation_id'];
            $data['store_id'] = $quotation_info['store_id'];
            $data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
            $data['store_name'] = $quotation_info['store_name'];
            $data['customer'] = $quotation_info['customer'];
            $data['customer_id'] = $quotation_info['customer_id'];
            $data['customer_group_id'] = $quotation_info['customer_group_id'];
            $data['firstname'] = $quotation_info['firstname'];
            $data['lastname'] = $quotation_info['lastname'];
            $data['email'] = $quotation_info['email'];
            $data['telephone'] = $quotation_info['telephone'];
            $data['account_custom_field'] = $quotation_info['custom_field'];
			$data['payment_address_id'] = $quotation_info['payment_address_id'];
			$data['shipping_address_id'] = $quotation_info['shipping_address_id'];
			



            // Bring order into Quotation if order exists
            $button_order = '';
            $orderid = '';
            $order_check = $this->db->query("select order_id from `" . DB_PREFIX . "order` where quotation_id='" . $this->request->get['quotation_id'] . "' and order_type='Quotation' order by order_id DESC LIMIT 1");
            if ($order_check->num_rows > 0) {
                $button_order = $this->url->link('codevoc/b2bmanager_order.edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_check->row['order_id'] , true);
                $orderid = $order_check->row['order_id'];
            }
            $data['button_order'] = $button_order;
            $data['order_id'] = $orderid;

            // Bring production into Quotation if production exists
            if ($order_check->num_rows > 0) {
                $data['production_id'] = '';
                $data['button_prodution'] = '';
                $check_production = $this->db->query("SELECT production_id FROM " . DB_PREFIX . "codevoc_production where order_id = '" . $orderid . "' order by production_id DESC limit 1 ");
                if ($check_production->num_rows > 0) {
                    $data['production_id'] = $check_production->row['production_id'];
                    $data['button_prodution'] = $this->url->link('codevoc/b2bmanager_production.edit', 'user_token=' . $this->session->data['user_token'] . '&production_id=' . $check_production->row['production_id'], true);
                }
            }
            if ($quotation_info['customer_id']) {
                $data['customerurl'] = $this->url->link('customer/customer.form&customer_id=' . $quotation_info['customer_id'], 'user_token=' . $this->session->data['user_token'], true);
            } else {
                $data['customerurl'] = '';
            }
            $data['date_added'] = date("Y-m-d", strtotime($quotation_info['date_added']));
            $this->load->model('customer/customer');
            $data['addresses'] = $this->model_customer_customer->getAddresses($quotation_info['customer_id']);
            $data['payment_firstname'] = $quotation_info['payment_firstname'];
            $data['payment_lastname'] = $quotation_info['payment_lastname'];
            $data['payment_company'] = $quotation_info['payment_company'];
            $data['payment_address_1'] = $quotation_info['payment_address_1'];
            $data['payment_address_2'] = $quotation_info['payment_address_2'];
            $data['payment_city'] = $quotation_info['payment_city'];
            $data['payment_postcode'] = $quotation_info['payment_postcode'];
            $data['payment_country_id'] = $quotation_info['payment_country_id'];
			if($data['payment_country_id']){
				$data['payment_country'] = $this->model_localisation_country->getCountry($data['payment_country_id'])['name'];
			}else{
				$data['payment_country'] = '';
			}
            $data['payment_zone_id'] = $quotation_info['payment_zone_id'];
            $data['payment_custom_field'] = $quotation_info['payment_custom_field'];
            $data['payment_method'] = $quotation_info['payment_method'];
			$data['payment_methods'] = $this->getPaymentMethods();
            $data['payment_code'] = $quotation_info['payment_code'];
            $data['shipping_firstname'] = $quotation_info['shipping_firstname'];
            $data['shipping_lastname'] = $quotation_info['shipping_lastname'];
            $data['shipping_company'] = $quotation_info['shipping_company'];
            $data['shipping_address_1'] = $quotation_info['shipping_address_1'];
            $data['shipping_address_2'] = $quotation_info['shipping_address_2'];
            $data['shipping_city'] = $quotation_info['shipping_city'];
            $data['shipping_postcode'] = $quotation_info['shipping_postcode'];
            $data['shipping_country_id'] = $quotation_info['shipping_country_id'];
			if($data['shipping_country_id']){
				$data['shipping_country'] = $this->model_localisation_country->getCountry($data['shipping_country_id'])['name'];
			}else{
				$data['shipping_country'] = '';
			}
            $data['shipping_zone_id'] = $quotation_info['shipping_zone_id'];
            $data['shipping_custom_field'] = $quotation_info['shipping_custom_field'];
            $data['shipping_method'] = $quotation_info['shipping_method'];
			$data['shipping_methods'] = $this->getShippingMethods();
            $data['shipping_code'] = $quotation_info['shipping_code'];
			$data['total'] = $quotation_info['total'];


			foreach($data['payment_methods'] as $method){
				if($method['code'] == $quotation_info['payment_method']){
					$data['payment_method_name'] = $method['name'];
				}
			}
			if(isset($data['payment_method_name'])){
				$data['payment_method_name']= '';
			}

			foreach($data['shipping_methods'] as $method){
				if($data['shipping_method'] == $method['code']){
					$data['shipping_method_name'] = $method['name'];
				}
			}

			if(isset($data['shipping_method_name'])){
				$data['shipping_method_name'] = '';
			}


            //codevoc quotation other detail table data
            $data['quotation_other_detail'] = array();
            $data['quotation_other_detail']['assignee'] = $quotation_other_details['assignee'];
            $data['quotation_other_detail']['vatnr'] = $quotation_other_details['vatnr'];
            $data['quotation_other_detail']['payment_company'] = $quotation_other_details['payment_company'];
            $data['quotation_other_detail']['shipping_company'] = $quotation_other_details['shipping_company'];
            $data['quotation_other_detail']['create_date'] = $quotation_other_details['create_date'];
            $data['quotation_other_detail']['delivery_date'] = $quotation_other_details['delivery_date'];
            $data['quotation_other_detail']['expiration_date'] = $quotation_other_details['expiration_date'];
            $data['quotation_other_detail']['shippment_terms'] = $quotation_other_details['shippment_terms'];
            $data['quotation_other_detail']['rate_delay'] = $quotation_other_details['rate_delay'];
            $data['quotation_other_detail']['custom_ordernr'] = $quotation_other_details['custom_ordernr'];

            //codevoc quotation other detail
            // Products
            $this->load->model('catalog/product');
			$this->load->model('localisation/tax_rate');
            $data['order_products'] = array();
            $products = $this->model_codevoc_b2bmanager_quotation->getQuotationOrderProducts($this->request->get['quotation_id']);
			$sub_total = 0;
            foreach ($products as $product) {
                $price_discount_percentage = $product['discount'];
                $sort_order = $product['sort'];
                $tax_class_id = $product['tax_class_id'];
				$this->load->model('localisation/tax_class');
				$tax_rules = $this->model_localisation_tax_class->getTaxRules($tax_class_id);
                $product_info = $this->model_catalog_product->getProduct($product['product_id']);
                $ooption = array();
                $order_options = $this->model_codevoc_b2bmanager_quotation->getQuotationOrderOptions($this->request->get['quotation_id'], $product['quotation_product_id']);
                foreach ($order_options as $order_option) {
                    $ooption[$order_option['product_option_id']] = $order_option['product_option_value_id'];
                }
                $product_options = $this->model_codevoc_b2bmanager_quotation->getProductOptionsSel($product['product_id'], $ooption);

                //v6
                $hiddenprice = $product['price'];
                //v6

                //discount price calculation
				if(array_key_exists('price',$product_info)){
					$org_price = $product_info['price'];
					$org_price_base = $product_info['price'];
				}else{
					$org_price = $product['price'];
					$org_price_base = $product['price'];
				}
                //discount price calculation

                //v5
                $productoptions = array();
                foreach ($product_options as $poption) {
                    $opprice = '';
                    foreach ($poption['product_option_value'] as $opt) {

                        if ($opt['selected'] == 'selected') {
                            $opprice = $opt['price_prefix'] . '_' . $opt['price'];
                            //v6
                            if ($opt['price_prefix'] == '+') {
                                $hiddenprice -= $opt['price'];
                                //discount price calculation
                                $org_price += $opt['price'];
                                //discount price calculation									
                            } elseif ($opt['price_prefix'] == '-') {
                                $hiddenprice += $opt['price'];
                                //discount price calculation
                                $org_price -= $opt['price'];
                                //discount price calculation										
                            }

                            //v6
                            break;
                        }

                    }
                    $productoptions[] = array(
                        'product_option_id' => $poption['product_option_id'],
                        'product_option_value' => $poption['product_option_value'],
                        'option_id' => $poption['option_id'],
                        'name' => $poption['name'],
                        'opprice' => $opprice,
                        'type' => $poption['type'],
                        'value' => $poption['value'],
                        'required' => $poption['required']
                    );

                }
                $product_options = $productoptions;
                //v5
				$total_tax = 0;	
				foreach($tax_rules as $tax_rule){
					$tax_rates = $this->model_localisation_tax_rate->getTaxRate($tax_rule['tax_rate_id']);
					$total_tax = $total_tax + $tax_rates['rate'];
				}

                $data['order_products'][] = array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'option' => $product_options,
                    'quantity' => $product['quantity'],
                    'price' => number_format($product['price'], 2, '.', ''),
                    'hiddenprice' => number_format($hiddenprice, 2, '.', ''),
                    'org_price' => number_format($org_price, 2, '.', ''), //discount price calculation
                    'org_price_base' => number_format($org_price_base, 2, '.', ''), //discount price calculation
                    'price_discount_percentage' => number_format($price_discount_percentage, 2, '.', ''),
                    'sort_order' => $sort_order,
                    'tax_class_id' => $tax_class_id,
					'tax' => $product['tax'],
					'total_tax_rate' => $total_tax,
                    'total' => number_format($product['total'], 2, '.', ''),
                    'reward' => $product['reward']
                );
				$sub_total = $sub_total +$product['price']*$product['quantity'];
            }
			$data['subtotal'] = $sub_total; 
            $data['coupon'] = '';
            $data['voucher'] = '';
            $data['reward'] = '';
            $data['order_totals'] = array();
            $quotation_totals = $this->model_codevoc_b2bmanager_quotation->getQuotationOrderTotals($this->request->get['quotation_id']);
            foreach ($quotation_totals as $total) {

                  $data['quotation_totals'][] = array(
                    'title' => $total['title'],
                    'text' => number_format($total['value'],2),
					'code' => $total['code'],
                );

           }
            $data['quotation_status_id'] = $quotation_info['quotation_status_id'];
            $data['comment'] = $quotation_info['comment'];
            $data['file_final_sketch'] = $quotation_info['file_final_sketch'];
            $data['affiliate_id'] = $quotation_info['affiliate_id'];
            $data['affiliate'] = $quotation_info['affiliate_firstname'] . ' ' . $quotation_info['affiliate_lastname'];
            $data['currency_code'] = $quotation_info['currency_code'];

        }
		$html = $this->load->view('codevoc/b2bmanager_quotation_pdf', $data);
		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);

		// Render the PDF
		$dompdf->render();

		// Output the PDF content to the browser or save it to a file
		$dompdf->stream('Quotation-'.$quotation_info['quotation_id'].'.pdf');
	}

	public function downloadfile()
    {
				$quotation_id = (isset($_GET['quotation_id']) && $_GET['quotation_id'] != "") ? $_GET['quotation_id'] : '';
				$file_id = (isset($_GET['file_id']) && $_GET['file_id'] != "") ? $_GET['file_id'] : '';

        if (!empty($quotation_id) && !empty($file_id)) {
					$this->load->model('codevoc/b2bmanager_quotation');
					$fileinfo = $this->model_codevoc_b2bmanager_quotation->getFileDetail($quotation_id, $file_id);

					if($fileinfo) {
						$file_dir = str_replace("/bossadm", "", DIR_APPLICATION) . "uploads/temp/";
						header('Content-Type: application/octet-stream');

            header("Content-Transfer-Encoding: Binary");

            header("Content-disposition: attachment; filename=\"" . $fileinfo['original_filename'] . "\"");

            readfile($file_dir. $fileinfo['filename']);

            exit;
					}

        }

    }
	public function addAddress(){
		$this->load->model('codevoc/b2bmanager_quotation');
		$address_id = $this->model_codevoc_b2bmanager_quotation->addAddress($this->request->post);
		$this->load->model('customer/customer');
    	$address = $this->model_customer_customer->getAddress($address_id);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($address));
	}
	public function handleDelete(){

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $data = array(

                'error' => 'Invalid Method'

            );

            http_response_code(500);

            echo json_encode($data);

            exit;

        }



        $filename = (isset($_POST['filename']) && $_POST['filename'] != "") ? $_POST['filename'] : "";

        $type = (isset($_POST['type']) && $_POST['type'] != "") ? $_POST['type'] : "";



        if ($type == "temp") {

            if ($filename == "") {

                $data = array(

                    'error' => 'Invalid Filename'

                );

                http_response_code(500);

                echo json_encode($data);

                exit;

            }



            $file_path = str_replace("/bossadm", "", DIR_APPLICATION) . "uploads/temp/" . $filename;



            if (!file_exists($file_path)) {

                $data = array(

                    'error' => 'File not found'

                );

                http_response_code(500);

                echo json_encode($data);

                exit;

            }



            if (unlink($file_path)) {

                $data = array(

                    'message' => 'File deleted'

                );

                http_response_code(200);

                echo json_encode($data);

                exit;

            } else {

                $data = array(

                    'error' => 'Unable to delete file'

                );

                http_response_code(500);

                echo json_encode($data);

                exit;

            }

        }

    }
			#PW: Header fast action
			public function gotoQuote() {
				$url =  '&quotation_id=' . $this->request->post['id'];
				$this->response->redirect($this->url->link('codevoc/b2bmanager_quotation.edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
			public function gotoOrder() {
				$url =  '&order_id=' . $this->request->post['id'];
				$this->response->redirect($this->url->link('codevoc/b2bmanager_order.edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}			
			#PW > End: header fast action

	public function import(){
		$this->load->model('codevoc/b2bmanager_quotation');
		$this->model_codevoc_b2bmanager_quotation->import();
	}
}
