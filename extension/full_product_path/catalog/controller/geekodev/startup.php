<?php
namespace Opencart\Catalog\Controller\Extension\FullProductPath\Geekodev;

class Startup extends \Opencart\System\Engine\Controller {
  
	public function seoProcessEnd() {
    require_once(DIR_EXTENSION.'full_product_path/catalog/model/tool/path_manager.php');
    $modelPathManager = new \Opencart\Catalog\Model\Extension\FullProductPath\Tool\PathManager($this->registry);
    
    $modelPathManager->seoProcessEnd();
  }
}