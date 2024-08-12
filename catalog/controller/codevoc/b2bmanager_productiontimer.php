<?php
namespace Opencart\Catalog\Controller\Codevoc;
class B2bmanagerProductiontimer extends \Opencart\System\Engine\Controller {

    private $token = "EJRiQ_LxY6YwOdekyNysHwF4_WIOJOAx8W55fRXEXv$@";

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
        $this->response->setOutput($this->load->view('codevoc/b2bmanager_productiontimer', $data));
    }

    public function getProductions() {
        $this->authorize();
        $this->load->model('codevoc/b2bmanager_productiontimer');
        $productions = $this->model_codevoc_b2bmanager_productiontimer->getProductions();
        $data = [];
        $data['productions'] = $productions;
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
    }

    public function saveTime() {
        $this->authorize();
        $this->load->model('codevoc/b2bmanager_report');

        $production_id = $this->request->post['production_id'];
        $seconds = $this->request->post['seconds'];

        if(!$production_id || !$seconds) {
            $json['error'] = "Invalid data provided";
        }

        if(!isset($json['error'])) {
            $this->load->model('codevoc/b2bmanager_productiontimer');
            $production_time_row = $this->model_codevoc_b2bmanager_productiontimer->saveTime((int)$production_id, (int)$seconds);
            $json['succcess'] = true;
            $json['production_time_row'] = $production_time_row;
        }

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

    public function completeProduction() {
        $this->authorize();

        $production_id = $this->request->post['production_id'];
        $production_name = $this->request->post['name'];

        if(!$production_id) {
            $json['error'] = "Invalid data provided";
        }

        if(!isset($json['error'])) {
            $this->load->model('codevoc/b2bmanager_productiontimer');
            $production_time_row = $this->model_codevoc_b2bmanager_productiontimer->completeProduction((int)$production_id);
            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

        $this->load->library('slack');
        $message = <<<SLACKMESSAGE
        :zap:Produktion avklarad :arrow_forward: {$production_name}
        SLACKMESSAGE;
        $this->slack->sendMessage($message, 'production');        
        
    }


}