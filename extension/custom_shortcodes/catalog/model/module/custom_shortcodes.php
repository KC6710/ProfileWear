<?php
namespace Opencart\catalog\model\Extension\CustomShortcodes\module;
class CustomShortcodes extends \Opencart\System\Engine\Model {
        
    public function getShortcodes() {
        $shortcodes_data = $this->cache->get('shortcodes');

        if(!$shortcodes_data) {                
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_shortcodes`");
            $shortcodes_data = $query->rows;
            $this->cache->set('shortcodes', $shortcodes_data);
        }

        return $shortcodes_data;            
    }
    
    public function getShortcode($name) {
        
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_shortcodes` where `name` = '".$name."'" );

        return $query->row;            
    }

}
