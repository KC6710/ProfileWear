<?php
namespace Opencart\Catalog\Controller\Codevoc;
class B2bmanagerReport extends \Opencart\System\Engine\Controller {

    private $token = "e11271b3ab2fd5ac09c313b0ae97f5e8";

    public function authorize() {
        $token = isset($_GET['token']) && $_GET['token'] != "" && $_GET['token'] == $this->token ? true : false;
        if(!$token){
            header('HTTP/1.0 401 Unauthorized');
            echo 'Not Authorized';
            exit;
        }
    }

    public function index(){
        $this->authorize();
        $data = [];
        $data['token'] = $this->token;
        $data['HTTPS_SERVER'] = HTTPS_SERVER;
        $this->response->setOutput($this->load->view('codevoc/b2bmanager_report', $data));
    }

    public function getData() {
        $this->authorize();
        $this->load->model('codevoc/b2bmanager_report');

        $from_date = $this->request->post['from_date'];
        $to_date = $this->request->post['to_date'];
        $year = $this->request->post['year'];

        if(!$from_date || !$to_date || !$year) {
            $json['error'] = "Invalid data provided";
        }

        if(!isset($json['error'])) {
            $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
            $to_date = date('Y-m-d 23:59:59', strtotime($to_date));
            $year = (int)$year;

            $sale_of_year = $this->model_codevoc_b2bmanager_report->getSaleOfYear();
            $sale_current_period = $this->model_codevoc_b2bmanager_report->getSaleCurrentPeriod($from_date, $to_date);
            $sale_printed = $this->model_codevoc_b2bmanager_report->getSalePrinted($from_date, $to_date);
            $sale_plain = $this->model_codevoc_b2bmanager_report->getSalePlain($from_date, $to_date);
            $productions = $this->model_codevoc_b2bmanager_report->getTotalProductions();
            $pw_productions = $this->model_codevoc_b2bmanager_report->getTotalPWProductions();
            $hotscreen_productions = $this->model_codevoc_b2bmanager_report->getTotalHotscreenProductions();
            $zamvi_productions = $this->model_codevoc_b2bmanager_report->getTotalZamviProductions();
            $other_productions = $this->model_codevoc_b2bmanager_report->getTotalOtherProductions();
            $sale_by_year = $this->model_codevoc_b2bmanager_report->getSaleByYear($year);
            $margin_by_year = $this->model_codevoc_b2bmanager_report->getMarginByYear($year);


            $json = [
                'success' => true,
                'sale_of_year' => $sale_of_year,
                'sale_current_period' => $sale_current_period,
                'sale_printed' => $sale_printed,
                'sale_plain' => $sale_plain,
                'productions' => $productions,
                'pw_productions' => $pw_productions,
                'hotscreen_productions' => $hotscreen_productions,
                'zamvi_productions' => $zamvi_productions,
                'other_productions' => $other_productions,
                'sale_by_year' => $sale_by_year,
                'margin_by_year' => $margin_by_year,
            ];
        }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

    public function getRevenueData() {
        $this->authorize();
        $this->load->model('codevoc/b2bmanager_report');

        $from_date = $this->request->post['from_date'];
        $to_date = $this->request->post['to_date'];

        if(!$from_date || !$to_date) {
            $json['error'] = "Invalid data provided";
        }

        if(!isset($json['error'])) {
            $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
            $to_date = date('Y-m-d 23:59:59', strtotime($to_date));

            $revenue_data = $this->model_codevoc_b2bmanager_report->getRevenueData($from_date, $to_date);


            $json = [
                'success' => true,
                'revenue_data' => $revenue_data,
            ];
        }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

    public function getQuotationData() {
        $this->authorize();
        $this->load->model('codevoc/b2bmanager_report');

        $from_date = $this->request->post['from_date'];
        $to_date = $this->request->post['to_date'];
        $transaction_type = isset($this->request->post['transaction_type']) && !empty($this->request->post['transaction_type']) ? $this->request->post['transaction_type'] : 'quotation';

        if(!$from_date || !$to_date) {
            $json['error'] = "Invalid data provided";
        }

        if(!isset($json['error'])) {
            $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
            $to_date = date('Y-m-d 23:59:59', strtotime($to_date));

            $quotation_data = $this->model_codevoc_b2bmanager_report->getQuotationData($transaction_type, $from_date, $to_date);
            $quotation_overview_data = $this->model_codevoc_b2bmanager_report->getQuotationOverviewData($transaction_type, $from_date, $to_date);

            $json = [
                'success' => true,
                'quotation_data' => $quotation_data,
                'quotation_overview_data' => $quotation_overview_data,
            ];
        }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

}