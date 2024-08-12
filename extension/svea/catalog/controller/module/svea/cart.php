<?php
namespace Opencart\Catalog\Controller\Extension\Svea\Module\Svea;
class Cart extends \Opencart\System\Engine\Controller
{
    private $moduleString = "module_";
    private $paymentString = "payment_";
    private $extensionString = "setting/extension";
    private $totalString = "total_";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->paymentString = "";
            $this->moduleString = "";
            $this->extensionString = "extension/extension";
            $this->totalString = "";
        }
    }

    public function index()
    {
        $this->setVersionStrings();
        $this->load->model('checkout/cart');

		$products = $this->model_checkout_cart->getProducts();

        $this->load->language('extension/svea/module/svea/checkout');
        $this->load->model($this->extensionString);

        $data['cart'] = $this->url->link('checkout/cart');
        $data['text_change_cart'] = $this->language->get('text_change_cart');
        $data['heading_cart'] = $this->language->get('heading_cart');

        // Products
        $data['products'] = array();

        foreach ($products as $product) {
            $product['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

            $data['products'][] = array(
                'product_id' => $product['product_id'],
                'model'      => $product['model'],
                'name'       => $product['name'],
                'quantity'   => $product['quantity'],
                'price'      => $product['price'],
                'option'     => $product['option'],
            );
        }

        // Vouchers
        $data['vouchers'] = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $voucher['amount'] = $this->currency->format($voucher['amount'], $this->session->data['currency']);

                $data['vouchers'][] = array(
                    'key' => $key,
                    'description' => $this->language->get('item_voucher') . " (" . $voucher['to_name'] . ")",
                    'amount' => $voucher['amount'],
                );
            }
        }
        // Totals
        $this->load->model($this->extensionString);

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );


        $results = $this->model_setting_extension->getExtensionsByType('total');

        $sort_order = array();

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($this->totalString . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($this->totalString . $result['code'] . '_status')) {
                $this->load->model('extension/opencart/total/' . $result['code']);
                ($this->{'model_extension_opencart_total_' . $result['code']}->getTotal)($totals, $taxes, $total);
            }
        }

        $sort_order = array();

        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $totals);

        $data['totals'] = array();

        foreach ($totals as $total) {
            $total['value'] = $this->currency->format($total['value'], $this->session->data['currency']);
            $data['totals'][] = array(
                'title' => $total['title'],
                'text'  => $total['value']
            );
        }
        // echo "<pre>"; print_r($data); die;
        $this->response->setOutput($this->load->view('extension/svea/module/svea/cart', $data));
    }

    private function removeTrail($price)
    {
        return str_replace($this->language->get('decimal_point') . '00', '', $price);
    }
}
