<?php
namespace Opencart\Catalog\Model\Extension\Svea\Module\Svea;
class Order extends \Opencart\System\Engine\Model
{
    public function getOrderHistoryComment($orderId)
    {
        return $this->db->query("SELECT comment FROM " . DB_PREFIX . "order_history WHERE order_id = " . $orderId);
    }
}
