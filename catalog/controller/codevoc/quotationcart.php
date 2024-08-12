<?php
namespace Opencart\Catalog\Controller\Codevoc;
require(DIR_OPENCART.'bossadm/model/codevoc/b2bmanager_quotation.php');
class Quotationcart extends \Opencart\System\Engine\Controller {
    public function index() {    
        // Show all php errors
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // load necessary models
        $this->load->model('codevoc/quotationcart');
		$this->load->model('catalog/product');

        // $adminCustomerModel = new ModelCustomerCustomer( $this->registry );
        $adminQuotationModel = new \Opencart\Admin\Model\Codevoc\B2bmanagerQuotation( $this->registry );

        $json = array();
        $quotation_id = $this->request->get['quotation_id'];
        if(array_key_exists('checkout',$this->request->get)){
            $fastcheckout = $this->request->get['checkout'];
        }         
        // echo "<pre>"; print_r($fastcheckout); die; 
        // Check valid quotation id
        if(!is_numeric($quotation_id) || !$quotation_id) {
            $json['error'] = 'Invalid Quotation ID Provided';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Retrive products for that quotation
        $products = $adminQuotationModel->getQuotationOrderProducts($quotation_id);
        if (empty($products)) {
            $json['error'] = 'No products found for this quotation';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        /* check if order already created for this quotation id */
        $isExist = $this->model_codevoc_quotationcart->checkOrderExistForQuotation($quotation_id);
        if ($isExist) {
            $data = [];
            $data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
            return $this->response->setOutput($this->load->view('error/not_found', $data));
        }

        // Clear cart
        $this->cart->clear();
		
        // Add products to cart
        

		
		
		$hasProductError = array();
        foreach ($products as $product) {
		
				/* retrive product options */
            
		
				$quantity = $product['quantity'];
				
				$b2b_product_name=$product['name'];
				
				$b2b_product_tax_class_id=$product['tax_class_id'];
				
				$b2b_product_sort_order=$product['sort'];
				//discount
				//$b2b_product_price=$product['price'].'_'.$product['discount'];
					/* discount price calculation OLD CODE
					$discountval=$product['price']*$product['discount']/100;
					$b2b_tprice=$product['price']-$discountval;
					$b2b_product_price=$b2b_tprice.'_0';
					discount price calculation OLD CODE */
					// discount price calculation
					$query_price = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");
					$product_info= $query_price->row;	
					$b2b_product_price=$product_info['price'].'_'.$product['discount'];
					// discount price calculation
				//discount		
				$option=array();

				$order_options=$adminQuotationModel->getQuotationOrderOptions($quotation_id, $product['quotation_product_id']);

				foreach($order_options as $order_option)

				{

						$option[$order_option['product_option_id']]=$order_option['product_option_value_id'];

				}
				$this->cart->B2bCartAdd($product['product_id'], $quantity, $option,'',$b2b_product_price,$b2b_product_name,$b2b_product_tax_class_id,$b2b_product_sort_order);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
				
            if(!empty($result)) {
                $json[] = $result;
            }
        }
        if(!empty($json)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // set custom cart session variable
        $this->session->data['custom_cart'] = true;
        $this->session->data['custom_cart_quotation_id'] = $quotation_id;

        // Redirect user to cart page
        if(isset($fastcheckout)){
            $this->response->redirect($this->url->link('extension/svea.checkout'));
        }else{
            $this->response->redirect($this->url->link('checkout/cart.B2bCart'));
        }
    }

}