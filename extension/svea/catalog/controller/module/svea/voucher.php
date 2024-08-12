<?php
namespace Opencart\Catalog\Controller\Extension\Svea\Module\Svea;
class Voucher extends \Opencart\System\Engine\Controller
{
    public function index()
    {
        $this->load->language('extension/svea/module/svea/checkout');

        $data['text_voucher_code'] = $this->language->get('text_voucher_code');
        $data['item_voucher'] = $this->language->get('item_voucher');
        $data['voucher'] = null;

        if ((isset($this->session->data['voucher'])) and (!empty($this->session->data['voucher']))) {
            $this->load->model('extension/total/voucher');
            $data['voucher'] = $this->model_extension_total_voucher->getVoucher($this->session->data['voucher']);
        }

        $this->response->setOutput($this->load->view('extension/svea/module/svea/voucher', $data));
    }

    /**
     * Remove voucher from order
     */
    public function remove()
    {
        $json = array();

        $this->session->data['voucher'] = null;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Add voucher from order
     */
    public function add()
    {
        $json = array();

        $this->load->language('extension/svea/module/svea/checkout');
        $this->load->model('extension/opencart/total/voucher');

        $voucher = (isset($this->request->post['voucher'])) ? trim($this->request->post['voucher']) : null;

        $result = $this->model_extension_opencart_total_voucher->getVoucher($voucher);

        if (empty($voucher)) {
            $json['error'] = $this->language->get('error_no_voucher');
            unset($this->session->data['voucher']);
        } elseif ($result) {
            $json['success'] = $this->language->get('success_add_voucher');
            $this->session->data['voucher'] = $this->request->post['voucher'];
        } else {
            $json['error'] = $this->language->get('error_unknown_voucher');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
