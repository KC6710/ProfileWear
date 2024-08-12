<?php
namespace Opencart\Catalog\Model\Codevoc;
class Quotationcart extends \Opencart\System\Engine\Model {
	
	public function checkOrderExistForQuotation($quotation_id)
	{
	
		 $quotation_id = (int)$quotation_id;

      	  $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE quotation_id = '" . $quotation_id . "'");
        
      	  return $query->num_rows > 0 ? true : false;
		
	}

}