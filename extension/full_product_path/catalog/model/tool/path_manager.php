<?php
namespace Opencart\Catalog\Model\Extension\FullProductPath\Tool;

class PathManager extends \Opencart\System\Engine\Model {
  public $cachedPath = array();
  
	public function oc4QqueryHook($urlQuery, $breadcrumbs_mode = false) {
    $parts = explode('&', $urlQuery);
    
    $path_mode = 'full_product_path_mode';
    
		if ($breadcrumbs_mode) {
			$path_mode = 'full_product_path_bc_mode';
		}
		
    parse_str($urlQuery, $query);
    
    if ($this->config->get('full_product_path_homelink') && isset($query['route']) && $query['route'] == 'common/home') {
      //return str_replace('route=common/home&', '', $urlQuery);
    }
    
    $newParts = [];
    $toSkip = [];
    
    foreach ($parts as $part) {
      $param = '';
      $value = '';
      
      if (strpos($part, '=')) {
        list($param, $value) = explode('=', $part);
      }
      
      if ($part == 'route=product/product') {
        if (!$this->config->get('full_product_path_bypasscat') && isset($query['path'])) {
          $newParts[] = $part;
          continue;
        }
        
        if (isset($query['product_id'])) {
          $product_id = $query['product_id'];
        } else {
          $newParts[] = $part;
          continue;
        }
        
        if (!$this->config->get($path_mode)) {
          $toSkip[] = 'path';
        } else if ($this->config->get($path_mode) == '3') {
          if (isset($query['manufacturer_id'])) {
            continue;
          }
          
          if (!empty($product_id)) {
            $man_id = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'")->row;
            
            if (!empty($man_id['manufacturer_id'])) {
              $insertBeforeLast = 'manufacturer_id='.$man_id['manufacturer_id'];
              $toSkip[] = 'manufacturer_id';
              $toSkip[] = 'path';
            }
          }
        } else if ($this->config->get($path_mode) == '4') {
          if (isset($query['path'])) {
            continue;
          }
          
          if (!empty($product_id)) {
            $category = $this->db->query("SELECT p2c.category_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category_path cp ON (p2c.category_id = cp.category_id) WHERE p2c.product_id = '" . (int)$product_id . "' ORDER BY cp.level DESC LIMIT 1")->row;
            
            if (!empty($category['category_id'])) {
              $insertBeforeLast = 'path='.$category['category_id'];
              $toSkip[] = 'path';
              $toSkip[] = 'manufacturer_id';
            }
          }
        } else if ($this->config->get($path_mode)) {
          if (!empty($product_id)) {
            $path = array();
            $categories = $this->db->query("SELECT c.category_id, c.parent_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category c ON (p2c.category_id = c.category_id) WHERE product_id = '" . (int)$product_id . "'")->rows;
            
            foreach($categories as $key => $category) {
              $path[$key] = '';
              if (!$category) continue;
              $path[$key] = $category['category_id'];
              
              while (!empty($category['parent_id'])) {
                $path[$key] = $category['parent_id'] . '_' . $path[$key];
                $category = $this->db->query("SELECT category_id, parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . $category['parent_id']. "'")->row;
              }
              
              $path[$key] = $path[$key];
              $banned_cats = $this->config->get('full_product_path_categories') ? $this->config->get('full_product_path_categories') : array();
              
              if (is_array($banned_cats) && count($banned_cats) && (count($categories) > 1)) {
                if (in_array($path[$key], $banned_cats)) {
                    unset($path[$key]);
                } else if (preg_match('#[_=](\d+)$#', $path[$key], $cat)) {
                  if (in_array($cat[1], $banned_cats)) {
                    unset($path[$key]);
                  }
                }
              }
            }
            
            if (!count($path)) return array();

            // wich one is the largest ?
            $whichone = array_map('strlen', $path);
            asort($whichone);
            $whichone = array_keys($whichone);
            
            if ($this->config->get($path_mode) == '2') {
              $whichone = array_pop($whichone);
            } else {
              $whichone = array_shift($whichone);
            }
            
            $path = $path[$whichone];
            
            if ((int) $this->config->get('full_product_path_depth')) {
              $path_parts  = explode('_', $path);
              while (count($path_parts) > (int) $this->config->get('full_product_path_depth')) {
                array_pop($path_parts);
              }
              $path = implode('_', $path_parts);
            }
            
            $insertBeforeLast = 'path='.$path;
            $toSkip[] = 'path';
            $toSkip[] = 'manufacturer_id';
          }
        }
      }
      
      if (substr($part, 0, 10) == 'product_id' && !empty($insertBeforeLast)) {
        $newParts[] = $insertBeforeLast;
        $insertBeforeLast = false;
      }
      
      if (!empty($toSkip) && in_array($param, $toSkip)) {
        continue;
      }
      
      $newParts[] = $part;
    }
    
    foreach ($newParts as $k => $newPart) {
      if ($this->config->get('full_product_path_nolang') && substr($newPart, 0, 9) == 'language=') {
        unset($newParts[$k]);
      }
    }
    
    foreach ($newParts as $k => $newPart) {
      if ($this->config->get('full_product_path_noroute') && in_array($newPart, ['route=product/product', 'route=product/category', 'route=information/information'])) {
        unset($newParts[$k]);
      }
    }
    
    return implode('&', $newParts);
  }
  
  public function seoProcessEnd() {
    // detect route
    if (empty($this->request->get['route'])) {
      if (!empty($this->request->get['product_id'])) {
        $this->request->get['route'] = 'product/product';
      } else if (!empty($this->request->get['manufacturer_id'])) {
        $this->request->get['route'] = 'product/manufacturer|info';
      } else if (!empty($this->request->get['information_id'])) {
        $this->request->get['route'] = 'information/information';
      } else if (!empty($this->request->get['path'])) {
        $this->request->get['route'] = 'product/category';
      }
    }
  }
  
	public function getFullProductPath($product_id, $breadcrumbs_mode = false) {
		$path_mode = 'full_product_path_mode';

		if ($breadcrumbs_mode) {
			$path_mode = 'full_product_path_bc_mode';
		}
		
		if (!$this->config->get($path_mode)) {
			return array();
    }
    
		if ($this->config->get($path_mode) == '3') {
			$man_id = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'")->row;
			
			if (!empty($man_id['manufacturer_id'])) {
				return array('manufacturer_id' => $man_id['manufacturer_id']);
			}
      
      return array();
		} else if ($this->config->get($path_mode) == '4') {
			$category = $this->db->query("SELECT p2c.category_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category_path cp ON (p2c.category_id = cp.category_id) WHERE p2c.product_id = '" . (int)$product_id . "' ORDER BY cp.level DESC LIMIT 1")->row;
    
			if (!empty($category['category_id'])) {
				return array('path' => $category['category_id']);
			}
      
      return array();
		}
		
		$path = array();
		$categories = $this->db->query("SELECT c.category_id, c.parent_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category c ON (p2c.category_id = c.category_id) WHERE product_id = '" . (int)$product_id . "'")->rows;
		
		foreach($categories as $key => $category) {
			$path[$key] = '';
			if (!$category) continue;
			$path[$key] = $category['category_id'];
			
			while (!empty($category['parent_id'])) {
				$path[$key] = $category['parent_id'] . '_' . $path[$key];
				$category = $this->db->query("SELECT category_id, parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . $category['parent_id']. "'")->row;
			}
			
			$path[$key] = $path[$key];
      $banned_cats = $this->config->get('full_product_path_categories') ? $this->config->get('full_product_path_categories') : array();
			
			if (is_array($banned_cats) && count($banned_cats) && (count($categories) > 1)) {
        if (in_array($path[$key], $banned_cats)) {
						unset($path[$key]);
				} else if (preg_match('#[_=](\d+)$#', $path[$key], $cat)) {
					if (in_array($cat[1], $banned_cats)) {
						unset($path[$key]);
          }
				}
			}
		}
		
		if (!count($path)) return array();

		// wich one is the largest ?
		$whichone = array_map('strlen', $path);
		asort($whichone);
		$whichone = array_keys($whichone);
		
		if ($this->config->get($path_mode) == '2') {
			$whichone = array_pop($whichone);
    } else {
      $whichone = array_shift($whichone);
    }
		
		$path = $path[$whichone];
		
		if ((int) $this->config->get('full_product_path_depth')) {
			$path_parts  = explode('_', $path);
			while (count($path_parts) > (int) $this->config->get('full_product_path_depth')) {
				array_pop($path_parts);
			}
			$path = implode('_', $path_parts);
		}
		
		return array('path' => $path);
	}

  public function getFullCategoryPath($category_id) {
    $path = '';
    $category = $this->db->query("SELECT category_id, parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'")->row;
    
    if (!$category) {
      return '';
    }
    
    $path = $category['category_id'];
    
    while ($category['parent_id']) {
      $path = $category['parent_id'] . '_' . $path;
      $category = $this->db->query("SELECT category_id, parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . $category['parent_id']. "'")->row;
    }
    
    return $path;
  }

  public function getManufacturerKeyword() {
    if ($this->config->get('mlseo_ml_mode')) {
      $ml_mode = "AND (`language_id` = '" . (int)$this->config->get('config_language_id') . "' OR `language_id` = 0)";
    } else {
      $ml_mode = '';
    }
    
    if (version_compare(VERSION, '3', '>=')) {
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'route=product/manufacturer'". $ml_mode ." LIMIT 1")->row;
    } else {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE `query` = 'route=product/manufacturer'". $ml_mode ." LIMIT 1")->row;
    }
    
    if (!empty($query['keyword'])) {
      return '/' . $query['keyword'];
    }
    
    return '';
  }
}