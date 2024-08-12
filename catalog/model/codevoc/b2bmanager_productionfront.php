<?php
namespace Opencart\Catalog\Model\Codevoc;
class B2bmanagerProductionfront extends \Opencart\System\Engine\Model {
    public function getData() {
        // Retrive all new productions
        $new_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production WHERE status = 'New' ORDER BY sort_order, created_at");

        // Retrive all in progress productions
        $progress_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production WHERE status = 'Progress' ORDER BY sort_order");

        // Retrive all priority productions
        $priority_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production WHERE status = 'Priority' ORDER BY sort_order");

        // Retrive completed priority productions
        $completed_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production WHERE status = 'Completed' ORDER BY sort_order limit 10");

        // Retrive rest productions
        $rest_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "codevoc_production WHERE status = 'Rest' ORDER BY sort_order limit 10");

        return [
            'new' => $new_query->rows,
            'progress' => $progress_query->rows,
            'priority' => $priority_query->rows,
            'completed' => $completed_query->rows,
            'rest' => $rest_query->rows,
        ];
    }

    public function getSuppliers($production_id) {
        $query = $this->db->query("SELECT *, ps.name FROM " . DB_PREFIX . "codevoc_production_to_suppliers pts LEFT JOIN ". DB_PREFIX ."codevoc_production_supplier ps ON ps.supplier_id = pts.supplier_id WHERE production_id = '". (int)$production_id ."'");

        return $query->rows;
    }

    public function getMethods($production_id) {
        $query = $this->db->query("SELECT *, pm.name FROM " . DB_PREFIX . "codevoc_production_to_methods ptm LEFT JOIN ". DB_PREFIX ."codevoc_production_method pm ON pm.method_id = ptm.method_id WHERE production_id = '". (int)$production_id ."'");

        return $query->rows;
    }

    public function updateStatus($data) {
        $this->db->query("UPDATE " . DB_PREFIX . "codevoc_production SET status = '" . $this->db->escape($data['status']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE production_id = '" . (int)$data['production_id'] . "'");

        // update sort order
        if($data['elements']) {
            $production_ids = [];
            foreach($data['elements'] as $element) {
                $production_ids[] = $element['production_id'];
            }

            $sql = "UPDATE " . DB_PREFIX . "codevoc_production SET sort_order=CASE";
            foreach($data['elements'] as $element) {
                $sql .= " WHEN production_id = ". $element['production_id'] . " THEN ". $element['sort_index'];
            }
            $sql .= " ELSE sort_order END WHERE production_id IN (". implode(", ", $production_ids) .")";

            $this->db->query($sql);
        }
    }

    public function getProduction($production_id) {
        $sql = "SELECT * FROM `". DB_PREFIX ."codevoc_production` WHERE production_id = ". (int)$production_id;
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getFullCalendarData($start, $end) {
        $startDate = date('Y-m-d 00:00:00', $start); // Start of provided date
		$endDate = date('Y-m-d 23:59:59', $end);

        $sql = "SELECT * FROM `".DB_PREFIX."codevoc_production` where production_date >= '". $startDate ."' and production_date <= '". $endDate ."'";

        $query = $this->db->query($sql);

		$rows = $query->rows;

		$result = [];

		foreach($rows as $row) {
			$result[] = [
				'id' => $row['production_id'],
				'title' => $row['name'],
				'start' => date('Y-m-d', strtotime($row['production_date'])),
                'duration' => $row['duration']
			];
		}

		return $result;
    }

    public function changeProductionDeliveryDate($id, $date) {
        $sql = "UPDATE `". DB_PREFIX ."codevoc_production` SET production_date = '". date('Y-m-d', strtotime($date))  ."' WHERE production_id = ".$id;

		$query = $this->db->query($sql);
    }
}