<?php
namespace Opencart\catalog\Controller\Extension\CustomShortcodes\Event;
class CustomShortcodes extends \Opencart\System\Engine\Controller {
    
    const SHORTOCODE_REGEXP = "/(?P<shortcode>(?:(?:\\s?\\[))(?P<name>[\\w\\-]{3,})(?:\\s(?P<attrs>[\\w\\d,\\s=\\\"\\'\\-\\+\\#\\%\\!\\~\\`\\&\\.\\s\\:\\/\\?\\|]+))?(?:\\])(?:(?P<content>[\\w\\d\\,\\!\\@\\#\\$\\%\\^\\&\\*\\(\\\\)\\s\\=\\\"\\'\\-\\+\\&\\.\\s\\:\\/\\?\\|\\<\\>]+)(?:\\[\\/[\\w\\-\\_]+\\]))?)/u";
    const ATTRIBUTE_REGEXP = "/(?<name>\\S+)=[\"']?(?P<value>(?:.(?![\"']?\\s+(?:\\S+)=|[>\"']))+.)[\"']?/u";
    
    private static $shortcodes = array();

    public function index(&$route, &$data, &$output) {
        $this->load->library('customShortcodes');
        if(!self::$shortcodes) {
            $this->load->model('extension/custom_shortcodes/module/custom_shortcodes');
            self::$shortcodes = $this->model_extension_module_custom_shortcodes->getShortcodes();            
            \CustomShortcodes::setRegistryAndShortcodes($this->registry, self::$shortcodes);
        }
        
        $shortcodes = self::$shortcodes;
        $cs = new \CustomShortcodes();
            
            $shortcodes = $this->parse_shortcodes($output);
            
            
            foreach ($shortcodes as $shortcode) {
                $attrs = isset($shortcode['attrs']) ? $shortcode['attrs'] : array();
                $result = $cs->doShortcodeByName($shortcode['name'], $data, $attrs);  
                if($result !== false) {
                    $output = str_replace($shortcode['shortcode'], $result, $output);
                }
                                      
            }
        return false;        
    }
    
   

    private function parse_shortcodes($text) {
        preg_match_all(self::SHORTOCODE_REGEXP, $text, $matches, PREG_SET_ORDER);
        $shortcodes = array();
        foreach ($matches as $i => $value) {
            $shortcodes[$i]['shortcode'] = $value['shortcode'];
            $shortcodes[$i]['name'] = $value['name'];
            if (isset($value['attrs'])) {
                $attrs = $this->parse_attrs($value['attrs']);
                $shortcodes[$i]['attrs'] = $attrs;
            }
            if (isset($value['content'])) {
                $shortcodes[$i]['content'] = $value['content'];
            }
        }

        return $shortcodes;
    }

    private function parse_attrs($attrs) {
        preg_match_all(self::ATTRIBUTE_REGEXP, $attrs, $matches, PREG_SET_ORDER);
        $attributes = array();
        foreach ($matches as $i => $value) {
            $key = $value['name'];
            $attributes[$i][$key] = $value['value'];
        }
        return $attributes;
    }
        
}