<?php
namespace Opencart\Catalog\Model\Codevoc;
class B2bmanagerProductiontimer extends \Opencart\System\Engine\Model {
	public function getProductions() {
		$db_prefix = DB_PREFIX;
		$sql = <<<SQL
			SELECT cp.*, FORMAT(cp.duration * 60, 0) as estimated_duration, cpt.duration as worked_duration FROM `{$db_prefix}codevoc_production` cp
			LEFT JOIN `{$db_prefix}codevoc_production_time` cpt ON cpt.production_id = cp.production_id
			WHERE cp.status IN ('Progress', 'Priority', 'Rest')
			ORDER BY cp.status DESC, cp.sort_order
		SQL;

		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function saveTime($production_id, $seconds) {
		// check if production_id available to update time
		$db_prefix = DB_PREFIX;
		$sql = <<<SQL
			SELECT * FROM `{$db_prefix}codevoc_production_time`
			WHERE production_id = '$production_id'
			LIMIT 1
		SQL;

		$query = $this->db->query($sql);
		$production_time_row = $query->row;

		// Update time
		if($production_time_row) {
			$sql = <<<SQL
				UPDATE `{$db_prefix}codevoc_production_time`
				SET duration = '$seconds'
				WHERE production_id = '$production_id'
			SQL;
			$query = $this->db->query($sql);
			$prodction_time_row_id = $production_time_row['production_time_id'];
		} else { // create new
			$sql = <<<SQL
				INSERT INTO `{$db_prefix}codevoc_production_time`
				SET duration = '$seconds', production_id = '$production_id'
			SQL;
			$query = $this->db->query($sql);
			$prodction_time_row_id = $this->db->getLastId();
		}

		// retrive updated data
		$sql = <<<SQL
			SELECT * FROM `{$db_prefix}codevoc_production_time`
			WHERE production_time_id = '$prodction_time_row_id'
		SQL;
		$query = $this->db->query($sql);
		$production_time_row = $query->row;
		return $production_time_row;
	}

	public function completeProduction($production_id) {
		// Update production status
		$db_prefix = DB_PREFIX;
		$sql = <<<SQL
			UPDATE `{$db_prefix}codevoc_production`
			SET status = 'Completed'
			WHERE production_id = '$production_id'
		SQL;
		$query = $this->db->query($sql);
	}
}