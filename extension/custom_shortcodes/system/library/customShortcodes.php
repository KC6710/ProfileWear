<?php

class CustomShortcodes {
        
    protected static $registry;
    protected static $shortcodes = array();

    public static function setRegistryAndShortcodes($registry, $shortcodes) {
        self::$shortcodes = $shortcodes;
        self::$registry = $registry;
    }
    
    public function __get($key) {
        return self::$registry->get($key);
    }

    public function __set($key, $value) {
        self::$registry->set($key, $value);
    }
    
    public function doShortcodeByName($name, $data = array(), $attrs = array()) {        
        $shortcode = self::findShortcode($name);
        if(!$shortcode) {
            return false;
        }
        
        return $this->doShortcode($shortcode, $data, $attrs);
    }    
    
    public function doShortcode($shortcode, $data = array(), $attrs = array()) {
        switch ($shortcode['type']) {
            case 'js':
                return $this->js($shortcode);                
            case 'php':
                return $this->php($data, $shortcode, $attrs);                        
            default:
                return html_entity_decode($shortcode['code'], ENT_QUOTES, 'UTF-8');                
        } 
    }

    protected  function js($shortcode) { 
        return '<script>'.html_entity_decode($shortcode['code'], ENT_QUOTES, 'UTF-8').'</script>';
    }    
    
    protected  function php(&$data, $shortcode, $attrs) {        
        return $this->php_process($data, html_entity_decode($shortcode['code'], ENT_QUOTES, 'UTF-8'), $attrs);
    }
    
    protected  function php_process($data, $php_code_execute, $attrs) { 
        if(isset($attrs[0]) && is_array($attrs[0])) {
            foreach ($attrs as $attr) {
                extract($attr);
            }  
        } else {
            extract($attrs);
        }
        
              
        return eval($php_code_execute);  
    }
    
    protected  function findShortcode($name) {
        foreach (self::$shortcodes as $shortcode) {
            if($name == $shortcode['name']) {
                return $shortcode;
            }
        }
        return false;
    } 
    
}
