<?php

namespace Opencart\Admin\Model\Extension\Googleshopping\Proxy;

class Helper extends \Opencart\System\Engine\Model { 
    function strpos(string $string, string $needle, int $offset = 0) {
        if (VERSION > '4.0.1.1') {
            return oc_strpos($string, $needle, $offset);
        } else if (VERSION > '4.0.0.0') {
            return \Opencart\System\Helper\Utf8\strpos($string, $needle, $offset);
        } else {
            return utf8_strpos($string, $needle, $offset);
        }
    }
    
    function strrpos(string $string, string $needle, int $offset = 0) {
        if (VERSION > '4.0.1.1') {
            return oc_strrpos($string, $needle, $offset);
        } else if (VERSION > '4.0.0.0') {
            return \Opencart\System\Helper\Utf8\strrpos($string, $needle, $offset);
        } else {
            return utf8_strrpos($string, $needle, $offset);
        }
    }
    
    function substr(string $string, int $offset, ?int $length = null) {
        if (VERSION > '4.0.1.1') {
            return oc_substr($string, $offset, $length);
        } else if (VERSION > '4.0.0.0') {
            return \Opencart\System\Helper\Utf8\substr($string, $offset, $length);
        } else {
            return utf8_substr($string, $offset, $length);
        }
    }
}