<?php
namespace Opencart\Catalog\Model\Codevoc;
class B2bmanagerReport extends \Opencart\System\Engine\Model {
  public function getSaleOfYear() {
    $sql = "SELECT SUM(total) as total, COUNT(order_id) as total_orders, SUM(total) / COUNT(order_id) as avg
    FROM `". DB_PREFIX ."order`
    WHERE order_status_id IN (1,4,5,7,8,9) and year(date_added) = '". date('Y') ."'";
    $query = $this->db->query($sql);
    return [
      'total' => ((float)$query->row['total']) * 0.8,
      'total_orders' => (int)$query->row['total_orders'],
      'avg' => number_format((float)$query->row['avg'] * 0.8, 2),
    ];
  }

  public function getSaleCurrentPeriod($from_date, $to_date) {
    $sql = "SELECT SUM(total) as total, COUNT(order_id) as total_orders, SUM(total) / COUNT(order_id) as avg
    FROM `". DB_PREFIX ."order`
    WHERE order_status_id IN (1,4,5,7,8,9) and (date_added BETWEEN '$from_date' AND '$to_date') ";
    $query = $this->db->query($sql);
    return [
      'total' => ((float)$query->row['total']) * 0.8,
      'total_orders' => (int)$query->row['total_orders'],
      'avg' => number_format((float)$query->row['avg'] * 0.8, 2),
    ];
  }

  public function getSalePrinted($from_date, $to_date) {
    $sql = "SELECT SUM(total) as total, COUNT(order_id) as total_orders, SUM(total) / COUNT(order_id) as avg
    FROM `". DB_PREFIX ."order`
    WHERE order_status_id IN (1,4,5,7,8,9) and (date_added BETWEEN '$from_date' AND '$to_date') and order_type = 'Quotation'";
    $query = $this->db->query($sql);

    return [
      'total' => ((float)$query->row['total']) * 0.8,
      'total_orders' => (int)$query->row['total_orders'],
      'avg' => number_format((float)$query->row['avg'] * 0.8, 2),
    ];
  }

  public function getSalePlain($from_date, $to_date) {
    $sql = "SELECT SUM(total) as total, COUNT(order_id) as total_orders, SUM(total) / COUNT(order_id) as avg
    FROM `". DB_PREFIX ."order`
    WHERE order_status_id IN (1,4,5,7,8,9) and (date_added BETWEEN '$from_date' AND '$to_date') and (order_type IS NULL or order_type = 'Webshop')";

    $query = $this->db->query($sql);

    return [
      'total' => ((float)$query->row['total']) * 0.8,
      'total_orders' => (int)$query->row['total_orders'],
      'avg' => number_format((float)$query->row['avg'] * 0.8, 2),
    ];
  }

  public function getTotalProductions() {
    $sql = "SELECT COUNT(*) total_productions FROM `". DB_PREFIX ."codevoc_production` WHERE FIELD(`status`, 'New', 'Progress')";
    $query = $this->db->query($sql);

    return [
      'total_productions' => $query->row['total_productions']
    ];
  }

  public function getTotalPWProductions() {
    $sql = "SELECT COUNT(*) total_productions FROM `". DB_PREFIX ."codevoc_production`  p
      LEFT JOIN `". DB_PREFIX ."codevoc_production_supplier` s ON s.supplier_id = p.supplier_id
      WHERE FIELD(p.status, 'New', 'Progress') AND FIELD(s.name, 'PW-Inhouse', 'PW-Stock')";
    $query = $this->db->query($sql);

    return [
      'total_productions' => $query->row['total_productions']
    ];
  }

  public function getTotalHotscreenProductions() {
    $sql = "SELECT COUNT(*) total_productions FROM `". DB_PREFIX ."codevoc_production`  p
      LEFT JOIN `". DB_PREFIX ."codevoc_production_supplier` s ON s.supplier_id = p.supplier_id
      WHERE FIELD(p.status, 'New', 'Progress') AND FIELD(s.name, 'Hotscreen')";
    $query = $this->db->query($sql);

    return [
      'total_productions' => $query->row['total_productions']
    ];
  }

  public function getTotalZamviProductions() {
    $sql = "SELECT COUNT(*) total_productions FROM `". DB_PREFIX ."codevoc_production`  p
      LEFT JOIN `". DB_PREFIX ."codevoc_production_supplier` s ON s.supplier_id = p.supplier_id
      WHERE FIELD(p.status, 'New', 'Progress') AND FIELD(s.name, 'Zamvi')";
    $query = $this->db->query($sql);

    return [
      'total_productions' => $query->row['total_productions']
    ];
  }

  public function getTotalOtherProductions() {
    $sql = "SELECT COUNT(*) total_productions FROM `". DB_PREFIX ."codevoc_production`  p
      LEFT JOIN `". DB_PREFIX ."codevoc_production_supplier` s ON s.supplier_id = p.supplier_id
      WHERE FIELD(p.status, 'New', 'Progress') AND FIELD(s.name, 'PW-Inhouse', 'PW-Stock', 'Hotscreen', 'Zamvi') = 0";
    $query = $this->db->query($sql);

    return [
      'total_productions' => $query->row['total_productions']
    ];
  }

  public function getRevenueData($from_date, $to_date) {
    /* $sql = "SELECT SUM(total) as total, COUNT(order_id) as total_orders, SUM(total) / COUNT(order_id) as avg
    FROM `". DB_PREFIX ."order`
    WHERE order_status_id IN (1,4,5,7,8,9) and (date_added BETWEEN '$from_date' AND '$to_date') ";
    */
    $db_prefix = DB_PREFIX;
    $sql = <<<SQL
        SELECT o.order_id, o.total, o.date_added,o.custom_field, o.firstname, o.lastname,
        (SELECT SUM(cost) FROM `{$db_prefix}codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Garment') as cost_garment,
        (SELECT SUM(cost) FROM `{$db_prefix}codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Print') as cost_print,
        (SELECT SUM(cost) FROM `{$db_prefix}codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Waste') as cost_waste,
        (SELECT SUM(cost) FROM `{$db_prefix}codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Extra') as cost_other,
        (SELECT h.date_added FROM `{$db_prefix}order_history` h WHERE h.order_id = o.order_id AND h.order_status_id = '5' ORDER BY h.date_added DESC LIMIT 1) as date_send
        FROM `{$db_prefix}order` o
        WHERE (date_added BETWEEN '$from_date' AND '$to_date')
        AND order_type = "Quotation"
        AND order_status_id IN (1,4,5,7,8,9)
    SQL;

    $query = $this->db->query($sql);
    $result = [];
    foreach($query->rows as $row) {
      $custom_field = json_decode($row['custom_field'], true);
      $client = implode(" ", [$row['firstname'], $row['lastname']]);
      if(isset($custom_field[1]) && !empty(trim($custom_field[1]))) {
        $client = $custom_field[1];
      }
      $total_costs = (float)$row['cost_garment'] + (float)$row['cost_print'] - (float)$row['cost_waste'] + (float)$row['cost_other'];
      $order_revenue = (float)$row['total']*0.8;
      
      $profit = ($order_revenue - $total_costs) / $order_revenue * 100;

      #$profit = $total_costs - $order_revenue / $total_costs * 100;
      #$profit = (1 - ($total_costs / (float)$row['total']*0.8)) * 100;
      $result[] = [
        'order_id' => $row['order_id'],
        'client' => $client,
        'total' => number_format($row['total']*0.8, 2, '.', ' '),
        'date_added' => $row['date_added'] ? date('d/M Y', strtotime($row['date_added'])) : 'N/A',
        'date_send' => $row['date_send'] ? date('d/M Y', strtotime($row['date_send'])) : 'N/A',
        'cost_garment' => $row['cost_garment'],
        'cost_print' => $row['cost_print'],
        'cost_waste' => $row['cost_waste'],
        'cost_other' => $row['cost_other'],
        'profit' => number_format($profit, 2)
      ];
    }

    return $result;
  }

  public function getSaleByYear($year) {
    $db_prefix = DB_PREFIX;
    $sql = <<<SQL
      SELECT
        SUM(IF(month = 'Jan', total, 0)) AS 'Jan',
        SUM(IF(month = 'Feb', total, 0)) AS 'Feb',
        SUM(IF(month = 'Mar', total, 0)) AS 'Mar',
        SUM(IF(month = 'Apr', total, 0)) AS 'Apr',
        SUM(IF(month = 'May', total, 0)) AS 'May',
        SUM(IF(month = 'Jun', total, 0)) AS 'Jun',
        SUM(IF(month = 'Jul', total, 0)) AS 'Jul',
        SUM(IF(month = 'Aug', total, 0)) AS 'Aug',
        SUM(IF(month = 'Sep', total, 0)) AS 'Sep',
        SUM(IF(month = 'Oct', total, 0)) AS 'Oct',
        SUM(IF(month = 'Nov', total, 0)) AS 'Nov',
        SUM(IF(month = 'Dec', total, 0)) AS 'Dec',
        SUM(total) AS 'Yearly'
    FROM (
      SELECT DATE_FORMAT(date_added, "%b") AS month, ROUND(SUM(total) * 0.8, 2) as total
      FROM `{$db_prefix}order`
      WHERE order_status_id IN (1,4,5,7,8,9) and (date_added <= NOW() and date_added >= Date_add(Now(),interval - 12 month)) AND year(date_added) = '$year'
      GROUP BY DATE_FORMAT(date_added, "%b")
    ) as sub

    SQL;
    $query = $this->db->query($sql);
    return $query->row;
  }

  public function getMarginByYear($year) {
    $db_prefix = DB_PREFIX;
    $sql = <<<SQL
    SELECT
      SUM(IF(month = 'Jan', profit, 0)) AS 'Jan',
      SUM(IF(month = 'Feb', profit, 0)) AS 'Feb',
      SUM(IF(month = 'Mar', profit, 0)) AS 'Mar',
      SUM(IF(month = 'Apr', profit, 0)) AS 'Apr',
      SUM(IF(month = 'May', profit, 0)) AS 'May',
      SUM(IF(month = 'Jun', profit, 0)) AS 'Jun',
      SUM(IF(month = 'Jul', profit, 0)) AS 'Jul',
      SUM(IF(month = 'Aug', profit, 0)) AS 'Aug',
      SUM(IF(month = 'Sep', profit, 0)) AS 'Sep',
      SUM(IF(month = 'Oct', profit, 0)) AS 'Oct',
      SUM(IF(month = 'Nov', profit, 0)) AS 'Nov',
      SUM(IF(month = 'Dec', profit, 0)) AS 'Dec',
      SUM(profit) AS 'Yearly'
    FROM (
      SELECT ROUND(SUM((1 - (total_costs / total)) * 100) / COUNT((1 - (total_costs / total)) * 100), 2)  as profit, DATE_FORMAT(date_added, "%b") AS month
      FROM
      (
        select date_added, total,(coalesce(cost_garment ,0) + coalesce(cost_print ,0) + coalesce(cost_waste ,0) + coalesce(cost_other ,0)) as total_costs  FROM (
          SELECT o.total as total, o.date_added as date_added,
            (SELECT SUM(cost) FROM `oc_codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Garment') as cost_garment,
            (SELECT SUM(cost) FROM `oc_codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Print') as cost_print,
            (SELECT SUM(cost) FROM `oc_codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Waste') as cost_waste,
            (SELECT SUM(cost) FROM `oc_codevoc_order_costs` WHERE order_id = o.order_id AND type = 'Extra') as cost_other
          FROM `oc_order` o
          WHERE (date_added <= NOW() and date_added >= Date_add(Now(),interval - 12 month)) AND year(date_added) = '$year'
          AND order_type = "Quotation"
        ) as t1
      ) t2
      GROUP BY DATE_FORMAT(date_added, "%b")
    ) as t3
    SQL;
    $query = $this->db->query($sql);
    return $query->row;

  }

  public function getQuotationData($transaction_type = 'quotation', $from_date, $to_date) {
    $db_prefix = DB_PREFIX;

    if($transaction_type == 'quotation') {
      $sql = <<<SQL
          SELECT ct.id,ct.transaction_type,ct.reference_id,ct.quotation_id,ct.utm_source,ct.utm_medium,ct.utm_campaign,ct.utm_term,ct.created_at,ct.updated_at, cq.firstname, cq.lastname, cq.custom_field, cq.total
          FROM `{$db_prefix}codevoc_campaigntracker` ct
          LEFT JOIN `{$db_prefix}codevoc_quotation` cq ON cq.quotation_id = ct.reference_id
          WHERE (created_at BETWEEN '$from_date' AND '$to_date')
          AND transaction_type = '$transaction_type'
      SQL;
    }
    if($transaction_type == 'order') {
      $sql = <<<SQL
          SELECT ct.id,ct.transaction_type,ct.reference_id,ct.quotation_id,ct.utm_source,ct.utm_medium,ct.utm_campaign,ct.utm_term,ct.created_at,ct.updated_at, co.firstname, co.lastname, co.custom_field, co.total, co.quotation_id as order_quotation_id
          FROM `{$db_prefix}codevoc_campaigntracker` ct
          LEFT JOIN `{$db_prefix}order` co ON co.order_id = ct.reference_id
          WHERE (created_at BETWEEN '$from_date' AND '$to_date')
          AND transaction_type = '$transaction_type'
      SQL;
    }

    $query = $this->db->query($sql);
    $result = [];
    foreach($query->rows as $row) {
      $custom_field = json_decode($row['custom_field'], true);
      $client = implode(" ", [$row['firstname'], $row['lastname']]);
      if(isset($custom_field[1]) && !empty(trim($custom_field[1]))) {
        $client = $custom_field[1];
      }
      $data = [
        'id' => $row['id'],
        'transaction_type' => $row['transaction_type'],
        'reference_id' => $row['reference_id'],
        'quotation_id' => $row['quotation_id'],
        'utm_source' => $row['utm_source'],
        'utm_medium' => $row['utm_medium'],
        'utm_campaign' => $row['utm_campaign'],
        'utm_term' => $row['utm_term'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
        'firstname' => $row['firstname'],
        'lastname' => $row['lastname'],
        'custom_field' => $row['custom_field'],
        'name' => $client,
        'total' => number_format($row['total']*0.8, 2, '.', ' '),
        
      ];

      if($transaction_type == 'order') {
        $data['order_quotation_id'] = isset($row['order_quotation_id']) && !empty($row['order_quotation_id']) ? $row['order_quotation_id'] : 'N/A';
      }

      $result[] = $data;
    }

    return $result;
  }

  public function getQuotationOverviewData($transaction_type = 'quotation',$from_date, $to_date) {
    $db_prefix = DB_PREFIX;
    $sql = <<<SQL
        SELECT SUM(CASE
            WHEN utm_source = '(direct)' THEN 1
            ELSE 0
          END) AS source_direct,
        SUM(CASE
            WHEN utm_source = 'N/A' THEN 1
            ELSE 0
          END) AS source_notset,
        SUM(CASE
            WHEN (utm_source = 'google' AND utm_campaign = 'organic') THEN 1
            ELSE 0
          END) AS source_google_organic,
        SUM(CASE
            WHEN (utm_source = 'google' AND utm_campaign = 'cpc') THEN 1
            ELSE 0
          END) AS source_google_cpc,
        SUM(CASE
            WHEN utm_source = 'blogg' THEN 1
            ELSE 0
          END) AS source_blogg,
        SUM(CASE
            WHEN utm_source = 'live' THEN 1
            ELSE 0
          END) AS source_bing
        FROM `{$db_prefix}codevoc_campaigntracker`
        WHERE (created_at BETWEEN '$from_date' AND '$to_date')  AND transaction_type = '$transaction_type'
        GROUP BY 'utm_source'
    SQL;

    $query = $this->db->query($sql);

    return [
      'source_direct' => isset($query->row['source_direct']) && !empty($query->row['source_direct']) ? $query->row['source_direct'] : 0,
      'source_notset' => isset($query->row['source_notset']) && !empty($query->row['source_notset']) ? $query->row['source_notset'] : 0,
      'source_google_organic' => isset($query->row['source_google_organic']) && !empty($query->row['source_google_organic']) ? $query->row['source_google_organic'] : 0,
      'source_google_cpc' => isset($query->row['source_google_cpc']) && !empty($query->row['source_google_cpc']) ? $query->row['source_google_cpc'] : 0,
      'source_blogg' => isset($query->row['source_blogg']) && !empty($query->row['source_blogg']) ? $query->row['source_blogg'] : 0,
      'source_bing' => isset($query->row['source_bing']) && !empty($query->row['source_bing']) ? $query->row['source_bing'] : 0,
    ];
  }
}