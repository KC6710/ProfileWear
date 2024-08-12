<?php
if (VERSION == '4.0.0.0') {
    $autoload = new \Opencart\System\Engine\Autoloader();
    $autoload->register('Opencart\System\Library\Extension\Googleshopping', DIR_EXTENSION . 'googleshopping/system/library/');
}