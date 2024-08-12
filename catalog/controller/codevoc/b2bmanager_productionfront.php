<?php
namespace Opencart\Catalog\Controller\Codevoc;
class B2bmanagerProductionfront extends \Opencart\System\Engine\Controller {

    public function authorize() {
		$token = "e007f22147@2YUT22XTRHL-44qac";

        $token = isset($_GET['token']) && $_GET['token'] != "" && $_GET['token'] == $token ? true : false;
		if(!$token){
			header('HTTP/1.0 401 Unauthorized');
			echo 'Not Authorized';
			exit;
		}
    }

    public function index(){
        $this->authorize();
        $this->response->setOutput($this->load->view('codevoc/b2bmanager_productionfront', [
            'token' => $_GET['token']
        ]));
    }

    public function getData() {
        $this->authorize();
        $this->load->model('codevoc/b2bmanager_productionfront');
        $data = $this->model_codevoc_b2bmanager_productionfront->getData();

        // Assign suppliers
        $new = [];
        foreach($data['new'] as $item) {
            $item['suppliers'] = $this->getSuppliers($item['production_id']);
            $item['methods'] = $this->getMethods($item['production_id']);
            $new[] = $item;
        }

        $progress = [];
        foreach($data['progress'] as $item) {
            $item['suppliers'] = $this->getSuppliers($item['production_id']);
            $item['methods'] = $this->getMethods($item['production_id']);
            $progress[] = $item;
        }

        $priority = [];
        foreach($data['priority'] as $item) {
            $item['suppliers'] = $this->getSuppliers($item['production_id']);
            $item['methods'] = $this->getMethods($item['production_id']);
            $priority[] = $item;
        }

        $completed = [];
        foreach($data['completed'] as $item) {
            $item['suppliers'] = $this->getSuppliers($item['production_id']);
            $item['methods'] = $this->getMethods($item['production_id']);
            $completed[] = $item;
        }

        $rest = [];
        foreach($data['rest'] as $item) {
            $item['suppliers'] = $this->getSuppliers($item['production_id']);
            $item['methods'] = $this->getMethods($item['production_id']);
            $rest[] = $item;
        }

        $result = [
            'new' => $new,
            'progress' => $progress,
            'priority' => $priority,
            'completed' => $completed,
            'rest' => $rest,
        ];
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    private function getSuppliers($production_id) {
        $this->load->model('codevoc/b2bmanager_productionfront');
        return $this->model_codevoc_b2bmanager_productionfront->getSuppliers($production_id);
    }

    private function getMethods($production_id) {
        $this->load->model('codevoc/b2bmanager_productionfront');
        return $this->model_codevoc_b2bmanager_productionfront->getMethods($production_id);
    }

    public function updateStatus() {
        $this->authorize();
        $production_id = $_POST['production_id'];
        $sort_order = $_POST['sort_order'];
        $status = $_POST['status'];
        $elements = JSON_DECODE($_POST['elements'], true);

        $this->load->model('codevoc/b2bmanager_productionfront');
        $this->model_codevoc_b2bmanager_productionfront->updateStatus([
            'production_id' => $production_id,
            'sort_order' => $sort_order,
            'status' => $status,
            'elements' => $elements,
        ]);

        // send slack message if completed
        if($status == 'Completed') {
            // retrive production
            $production = $this->model_codevoc_b2bmanager_productionfront->getProduction($production_id);
            $this->load->library('slack');
            $message = <<<SLACKMESSAGE
            :zap:Produktion avklarad :arrow_forward: {$production['name']}
            SLACKMESSAGE;
            $this->slack->sendMessage($message, 'production');
        }elseif($status == 'Priority') {
            // retrive production
            $production = $this->model_codevoc_b2bmanager_productionfront->getProduction($production_id);
            $this->load->library('slack');
            $message = <<<SLACKMESSAGE
            :zap:Proriterad uppdrag :red_circle: {$production['name']}
            SLACKMESSAGE;
            $this->slack->sendMessage($message, 'production');
        }

    }

    public function fullcalendardata() {
        $start = $this->request->post['start'];
        $end = $this->request->post['end'];
        $this->load->model('codevoc/b2bmanager_productionfront');
        $productions = $this->model_codevoc_b2bmanager_productionfront->getFullCalendarData($start, $end);

        header('Content-Type: application/json');
        echo json_encode($productions);
        die;
    }

    public function changeProductionDeliveryDate() {
        $date = isset($this->request->post['date']) && !empty($this->request->post['date']) ? $this->request->post['date'] : null;
        $id = isset($this->request->post['id']) && !empty($this->request->post['id']) ? $this->request->post['id'] : null;
        if($date && $id) {
            $this->load->model('codevoc/b2bmanager_productionfront');
            $productions = $this->model_codevoc_b2bmanager_productionfront->changeProductionDeliveryDate($id, $date);
            header('Content-Type: application/json');
            echo json_encode(true);
            die;
        }
    }
}