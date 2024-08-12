<?php namespace Ocme;

/**
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

class Msg {
	
	// TYPES ///////////////////////////////////////////////////////////////////
	
	const INFO = 'info';
	const ERROR = 'danger';
	const SUCCESS = 'success';
	const WARNING = 'warning';
	
	// NAMESPACES //////////////////////////////////////////////////////////////	
	
	/**
	 * @var string
	 */
	const NAMESPACE_DEFAULT		= 'default';
	
	// PARAMS //////////////////////////////////////////////////////////////////
	
	/**
	 * @var string
	 */
	private static $key = '__ocme_flash_messages';
	
	/**
	 * List of current messages
	 * 
	 * @var array
	 */
	private $currentMessages = array();
	
	/**
	 * List of messages
	 * 
	 * @var array
	 */
	private $messages = array();
	
	/**
	 * Current namespace
	 * 
	 * @var string
	 */
	private $namespace = self::NAMESPACE_DEFAULT;
	
	/**
	 * Create a new msg notifier instance
	 * 
	 * @param \Illuminate\Session\Store $session
	 */
	public function __construct() {		
		if( ocme()->arr()->has( ocme()->ocRegistry()->get('session')->data, self::$key ) ) {
			$this->messages = ocme()->arr()->get( ocme()->ocRegistry()->get('session')->data, self::$key );
			
			ocme()->arr()->forget( ocme()->ocRegistry()->get('session')->data, self::$key );
		}
	}
	
	/**
	 * Add an information message
	 * 
	 * @param string $message
	 * @param array $replace
	 * @param string $namespace
	 * @return \self
	 */
	public function info( $message, array $replace = array(), $namespace = null ) {
		return $this->message( $message, self::INFO, $replace, $namespace );
	}
	
	/**
	 * Add a success message
	 * 
	 * @param string $message
	 * @param array $replace
	 * @param string $namespace
	 * @return \self
	 */
	public function success( $message, array $replace = array(), $namespace = null ) {
		return $this->message( $message, self::SUCCESS, $replace, $namespace );
	}
	
	/**
	 * Add an error message
	 * 
	 * @param string $message
	 * @param array $replace
	 * @param string $namespace
	 * @return \self
	 */
	public function error( $message, array $replace = array(), $namespace = null ) {
		return $this->message( $message, self::ERROR, $replace, $namespace )->important();
	}
	
	/**
	 * Add a warning message
	 * 
	 * @param string $message
	 * @param array $replace
	 * @param string $namespace
	 * @return \self
	 */
	public function warning( $message, array $replace = array(), $namespace = null ) {
		return $this->message( $message, self::WARNING, $replace, $namespace )->important();
	}
	
	/**
	 * Add a message
	 * 
	 * @param string|\Illuminate\Support\MessageBag|array $message
	 * @param string $level
	 * @param array $replace
	 * @param string $namespace
	 * @return \self
	 */
	public function message( $message, $level = self::INFO, array $replace = array(), $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		if( $message instanceof \Illuminate\Support\MessageBag ) {
			foreach( $message->all() as $msg ) {
				$this->message( $msg, $level, $replace, $namespace )->important();
				
				break; // add only first error
			}
			
			return $this;
		} else if( is_array( $message ) ) {
			foreach( $message as $msg ) {
				$this->message( $msg, $level )->important();
			}
			
			return $this;
		}
		
		if( $message !== '' && $message !== null && $message !== false ) {
			$this->currentMessages[$namespace][] = array( 'message' => $message, 'level' => $level, 'replace' => $replace, 'important' => false, 'delay' => 1000 );

			ocme()->arr()->set( ocme()->ocRegistry()->get('session')->data, self::$key, $this->currentMessages );
		}
		
		return $this;
	}
	
	public function has( $namespace = null, $level = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		if( $level == null ) {
			return ! empty( $this->currentMessages[$namespace] );
		}
		
		/* @var $message array */
		foreach( $this->currentMessages[$namespace] as $message ) {
			if( $message['level'] == $level ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Change current namespace
	 * 
	 * @param string $namespace
	 * @return \self
	 */
	public function changeNamespace( $namespace ) {
		$this->namespace = $namespace ? $namespace : self::NAMESPACE_DEFAULT;
		
		return $this;
	}
	
	/**
	 * Mark the last message as important
	 * 
	 * @param bool $important
	 * @param string $namespace
	 * @return \self
	 */
	public function important( $important = true, $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		if( ! empty( $this->currentMessages[$namespace] ) ) {
			/* @var $message array */
			$message = array_pop( $this->currentMessages[$namespace] );
			
			$message['important'] = $important;
			
			$this->currentMessages[$namespace][] = $message;
			
			ocme()->arr()->set( ocme()->ocRegistry()->get('session')->data, self::$key, $this->currentMessages );
		}
		
		return $this;
	}
	
	/**
	 * Mark the last message as not important
	 * 
	 * @param string $namespace
	 * @return \self
	 */
	public function notImportant( $namespace = null ) {
		return $this->important( false, $namespace );
	}
	
	/**
	 * Set delay
	 * 
	 * @param int $delay
	 * @param string $namespace
	 * @return \self
	 */
	public function delay( $delay, $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		if( ! empty( $this->currentMessages[$namespace] ) ) {
			/* @var $message array */
			$message = array_pop( $this->currentMessages[$namespace] );
			
			$message['important'] = false;
			$message['delay'] = $delay;
			
			$this->currentMessages[$namespace][] = $message;
			
			ocme()->arr()->set( ocme()->ocRegistry()->get('session')->data, self::$key, $this->currentMessages );
		}
		
		return $this;
	}
	
	/**
	 * Render
	 * 
	 * @param string $namespace
	 * @return string
	 */
	public function render( $namespace = null ) {
		if( is_null( $namespace ) ) {
			$namespace = 'default';
		}
		
		/* @var $messages array */
		$messages = array();
		
		if( ! $this->isEmpty( $namespace ) ) {
			foreach( $this->toRender( $namespace ) as $message ) {
				$messages[] = sprintf(
					'<div class="alert alert-%s" data-delay="%s" data-important="%s">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						%s
					</div>',
					$message['level'], 
					$message['delay'], 
					$message['important'], 
					$message['message']
				);
			}
		}
		
		// @todo return sprintf( '<ocme-msg inline-template namespace="%s"><div class="alert-list">%s</div></ocme-msg>', $namespace, implode( '', $messages ) );
		
		return sprintf( '<div class="alert-list">%s</div>', implode( '', $messages ) );
	}
	
	/**
	 * Get messages to render
	 * 
	 * @param string $namespace
	 * @return array
	 */
	public function toRender( $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		/* @var $messages array */
		$messages = array_map(function( $message ){
			return array(
				'message' => ocme()->trans( $message['message'], $message['replace'] ),
				'level' => $message['level'],
				'delay' => $message['delay'],
				'important' => $message['important']
			);
		}, array_merge( $this->get( $namespace ), $this->getCurrent( $namespace ) ));
		
		$this->clear( $namespace )->clearCurrent( $namespace );
		
		return $messages;
	}
	
	public function toJson( $namespace = null ) {
		/* @var $namespaces array */
		$namespaces = $namespace === null ? array_unique( array_merge( array_keys( $this->messages ), array_keys( $this->currentMessages ) ) ) : [ $namespace ];
		
		/* @var $messages array */
		$messages = array();
		
		foreach( $namespaces as $namespace ) {
			$messages[$namespace] = $this->toRender( $namespace );
		}
		
		return array( '_msg' => $messages );
	}
	
	/**
	 * Get messages
	 * 
	 * @param string $namespace
	 * @return array
	 */
	public function get( $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		return isset( $this->messages[$namespace] ) ? $this->messages[$namespace] : array();
	}
	
	/**
	 * Get current messages
	 * 
	 * @param string $namespace
	 * @return array
	 */
	public function getCurrent( $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		return isset( $this->currentMessages[$namespace] ) ? $this->currentMessages[$namespace] : array();
	}
	
	/**
	 * Clear list of messages
	 * 
	 * @param string $namespace
	 * @return \self
	 */
	public function clear( $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		if( isset( $this->messages[$namespace] ) ) {
			unset( $this->messages[$namespace] );
		}
		
		return $this;
	}
	
	/**
	 * Clear list of current messages
	 * 
	 * @param string $namespace
	 * @return \self
	 */
	public function clearCurrent( $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		if( isset( $this->currentMessages[$namespace] ) ) {
			unset( $this->currentMessages[$namespace] );
			
			ocme()->arr()->set( ocme()->ocRegistry()->get('session')->data, self::$key, $this->currentMessages );
		}
		
		return $this;
	}
	
	/**
	 * Is empty ?
	 * 
	 * @param string $namespace
	 * @return \self
	 */
	public function isEmpty( $namespace = null ) {
		if( $namespace === null ) {
			$namespace = $this->namespace;
		}
		
		return ! $this->get( $namespace ) && ! $this->getCurrent( $namespace );
	}
}