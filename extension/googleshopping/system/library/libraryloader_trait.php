<?php

namespace Opencart\System\Library\Extension\Googleshopping;

trait LibraryloaderTrait {
    protected function loadLibrary($store_id) {
        $this->registry->set('googleshopping', new \Opencart\System\Library\Extension\Googleshopping\Googleshopping($this->registry, $store_id));
    }
}