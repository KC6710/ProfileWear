<?php namespace Ocme\OpenCart\Admin\Traits;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

use Ocme\Model\Language;

trait OcmeMfp {	
	
	/**
	 * @var bool
	 */
	protected static $initialized;
	
	/**
	 * @var bool
	 */
	protected static $startup_initialized;
	
	protected function checkInstallation() {
		if( ocme()->ocRegistry()->get('config')->get('ocme_mfp_installation_error') ) {
			/* @var $errors array */
			$errors = array();
			
			/* @var $ocme_mfp_db_changes array */
			if( null != ( $ocme_mfp_db_changes = (array) ocme()->ocRegistry()->get('config')->get('ocme_mfp_db_changes') ) ) {
				/* @var $changes array */
				foreach( $ocme_mfp_db_changes as $changes ) {
					$errors = array_merge( $errors, ocme()->arr()->get( $changes, 'errors', array() ) );
				}
			}
			
			/* @var $msg string */
			$msg = 'Installation error occurred. Please contact with our support team.';
			
			if( $errors ) {
				$msg .= sprintf(' Errors:<br /><br />%s', implode('<br />', $errors) );
			}
			
			ocme()->msg()->error( $msg );
			
			if( ocme()->request()->query('route') != ocme_extension_path($this->path.'/about') ) {
				$this->response->redirect(ocme()->url()->adminLink($this->path.'/about'));
			}
			
			return;
		}
		
		/* @var $version string */
		if(  ocme()->ocRegistry()->get('config')->get('ocme_mfp_installed_at') && null == ( $version = ocme()->variable()->get('app.version') ) ) {
			$version = '3.0.0.0';
			
			\Ocme\Model\OcmeVariable::firstOrNew(array(
				'type' => 'app',
				'name' => 'version',
			))->fill(array(
				'value' => $version,
			))->save();
		}
		
		if( $this->isInstalledModule( 'ocme_mfp_filter') && ! ocme()->ocRegistry()->get('config')->get('ocme_mfp_installed_at') ) {
			$this->checkCacheDirs( false );
			
			$this->response->redirect(ocme()->url()->adminLink($this->path.'_filter/indexation', '&autostart=1'));
		}
		
		if( version_compare( $version, ocme()->version(), '<' ) ) {
			$this->response->redirect(ocme()->url()->adminLink($this->path.'/update'));
		}
	}
	
	protected function getCacheDirs() {
		/* @var $dirs array */
		$dirs = array(
			( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'ocme/admin/' : DIR_APPLICATION ) . $this->cache_path_js,
			( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'ocme/admin/' : DIR_APPLICATION ) . $this->cache_path_css,
			( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'ocme/catalog/' : DIR_CATALOG ) . $this->cache_path_js,
		);
		
		if( version_compare( VERSION, '4', '>=' ) ) {
			$dirs[] = DIR_EXTENSION . 'ocme/catalog/view/ocme/stylesheet/cache';
		} else {
			$dirs[] = DIR_CATALOG . 'view/ocme/stylesheet/cache';
		}
		
		return $dirs;
	}
	
	protected function flushCacheDirs() {
		/* @var $dirs array */
		$dirs = $this->getCacheDirs();
		
		/* @var $dir string */
		foreach( $dirs as $dir ) {
			if( is_dir( $dir ) && is_writable( $dir ) ) {
				/* @var $files array */
				if( null != ( $files = glob( $dir . '/*.{js,css,JS,CSS}', GLOB_BRACE ) ) ) {
					/* @var $file string */
					foreach( $files as $file ) {
						unlink( $file );
					}
				}
			}
		}
	}
	
	protected function checkCacheDirs( $with_messages = true ) {
		/* @var $dirs array */
		$dirs = $this->getCacheDirs();
		
		/* @var $not_exists array */
		$not_exists = array();
		
		/* @var $is_not_writable array */
		$is_not_writable = array();
		
		/* @var $dir string */
		foreach( $dirs as $dir ) {			
			if( ! is_dir( $dir ) ) {
				// try to create missing dir
				$parent_path = implode( DIRECTORY_SEPARATOR, array_slice( explode( DIRECTORY_SEPARATOR, str_replace( '/', DIRECTORY_SEPARATOR, $dir ) ), 0, -1 ) );
				
				if( is_writable( $parent_path ) ) {
					@ mkdir( $dir );
				}
				
				if( ! is_dir( $dir ) ) {
					$not_exists[] = $dir;
				}
			} else if( ! is_writable( $dir ) ) {
				$is_not_writable[] = $dir;
			}
		}
		
		if( $with_messages ) {
			if( $not_exists ) {
				ocme()->msg()->info('module::global.text_folder_not_exist', array( 'dirs' => '<ul><li>' . implode('</li><li>', $not_exists) . '</li></ul>'));
			} else if( $is_not_writable ) {
				ocme()->msg()->info('module::global.text_folder_not_writable', array( 'dirs' => '<ul><li>' . implode('</li><li>', $is_not_writable) . '</li></ul>'));
			}
		}
		
		return $this;
	}
	
	protected function js( array $options = array() ) {
		/* @var $js array */
		$js = array();

		$this->load->model('tool/image');
		
		$js[] = "if( typeof ocmeFramework != 'undefined' && ocmeFramework.extension('config') ) {";
		$js[] = "ocmeFramework.extension('config').set(" . json_encode(array(
			'app_version' => ocme()->version(),
			'oc_version' => VERSION,
			'url' => array_map(function($v){
				return str_replace( '&amp;', '&', $v );
			}, array(
				'attributes' => ocme()->url()->adminLink($this->name.'/attributes'),
				'attribute_value' => ocme()->url()->adminLink($this->name.'/attribute_value'),
				'attribute_values' => ocme()->url()->adminLink($this->name.'/attribute_values'),
				'multiple_attribute_values' => ocme()->url()->adminLink($this->name.'/multiple_attribute_values'),
				'attribute_value_add' => ocme()->url()->adminLink($this->name.'/attribute_value_add'),
				'attribute_groups' => ocme()->url()->adminLink($this->name.'/attribute_groups'),
				'product_attributes' => ocme()->url()->adminLink($this->name.'/product_attributes'),
			)),
			'languages' => Language::query()->get()->map(function( $language ){
				return [
					'language_id' => $language->language_id,
					'name' => $language->name,
					'image_path' => $language->image_path,
				];
			}),
			'no_image' => $this->model_tool_image->resize('no_image.png', 100, 100),
		)) . ");";
		$js[] = '}';
		
		/* @var $with_trans string */
		if( null != ( $with_trans = ocme()->arr()->get($options, 'with_trans') ) ) {
			/* @var $parts array */
			$parts = explode( ';', $with_trans );
			
			$js[] = "if( typeof ocmeFramework != 'undefined' && ocmeFramework.extension('trans') ) {";
			
			foreach( $parts as $part ) {
				/* @var $values array */
				$values = ocme()->trans($part);
					
				if( $part != 'module::global' ) {
					$values = array( $part => $values );
				}
				
				$js[] = "ocmeFramework.extension('trans').set(" . json_encode( $values ) . ");";
			}
			
			$js[] = "}";
		}
		
		$this->initialize();
			
		if( isset( $this->data['ocme_module_trans'] ) ) {
			$js[] = "if( typeof ocmeFramework != 'undefined' && ocmeFramework.extension('trans') ) {";
			$js[] = "ocmeFramework.extension('trans').set(" . $this->data['ocme_module_trans'] . ");";
			$js[] = "}";
		}
		
		$js = implode("\n", $js);
		
		/* @var $file string */
		$file = md5($js) . '.js';
		
		/* @var $dir string */
		$dir = ( defined( 'DIR_EXTENSION' ) ? DIR_EXTENSION . 'ocme/admin/' : DIR_APPLICATION ) . $this->cache_path_js;
		
		if( is_dir( $dir ) && is_writable( $dir ) && ! file_exists( $dir . '/' . $file ) ) {
			$this->refreshCacheFolder( $this->cache_path_js, 'js' );
			
			file_put_contents( $dir . '/' . $file, $js );
		}
		
		return $this->cache_path_js . '/' . $file;
	}
	
	/**
	 * @return $this
	 */
	protected function initialize() {
		if( self::$initialized ) return;
		
		self::$initialized = true;
		
		$this->data['ocme_module_trans'] = json_encode( ocme()->trans('module::global') );
		
		return $this->withBreadcrumbs(array(
			array(
				'text' => ocme()->trans('module::global.text_extension'),
				'href' => ocme()->url()->adminLink('marketplace/extension', 'type=module')
			),
		));
	}
	
	protected function validateAccess( $key = 'modify', $msg = true ) {
		if( ! $this->user->hasPermission($key, ocme_extension_path('extension/module/ocme_mfp') ) ) {
			if( $msg ) {
				ocme()->msg()->error('module::global.error_permission');
			}
			
			return false;
		}
		
		return true;
	}
	
}