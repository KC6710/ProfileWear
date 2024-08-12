<?php
namespace Opencart\Admin\Model\Extension\CustomShortcodes\Module;
class CustomShortcodes extends \Opencart\System\Engine\Model {
    public function addShortcode($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "custom_shortcodes` SET `admin_name` = '" . $this->db->escape($data['admin_name']) . "', `name` = '" . $this->db->escape($data['name']) . "', `type` = '" . $data['type'] . "', `code` = ''");
        $this->cache->delete('shortcodes');
        return $this->db->getLastId();
    }
    
    public function updateShortcode($id, $data) {        
        $this->db->query("UPDATE `" . DB_PREFIX . "custom_shortcodes` SET `admin_name` = '" . $this->db->escape($data['admin_name']) . "', `name` = '" . $this->db->escape($data['name']) . "', `code` = '" . $this->db->escape($data['code']) . "' WHERE id = ". (int) $id); 
        $this->cache->delete('shortcodes');
    }
    
    public function getShortcodes($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "custom_shortcodes`";

        $sort_data = array(
            'admin_name',
            'name',
            'type'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY `" . $data['sort'] . "`";
        } else {
            $sql .= " ORDER BY `admin_name`";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }
    
    public function getTotalShortcodes() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "custom_shortcodes`");

        return $query->row['total'];
    }
    
    public function getShortcode($id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_shortcodes` WHERE `id` = ".(int) $id);
        
        return $query->row;
    }
    
    public function deleteShortcode($id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "custom_shortcodes` WHERE `id` = " .(int) $id );
        $this->cache->delete('shortcodes');
    }
}