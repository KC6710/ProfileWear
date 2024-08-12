<?php namespace Ocme\Support;

class Request
{

    /**
     * @var string
     */
    protected $method;
	
	/**
	 * @var 
	 */
	protected $headers;
	
	public function createOpenCartUrlParams() {
		/* @var $url array */
		$url = array();

		foreach( func_get_args() as $key ) {
			if( ocme()->request()->hasQuery( $key ) ) {
				$url[] = $key . '=' . ocme()->request()->query( $key );
			}
		}
		
		return implode('&', $url);
	}

    /**
     * Retrieve an input item from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function input($key = null, $default = null) {
		return ocme()->data()->get( ocme()->oc()->registry()->get('request')->get + ocme()->oc()->registry()->get('request')->post, $key, $default );
    }

    /**
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
	public function query( $key = null, $default = null ) {
		return ocme()->data()->get( ocme()->oc()->registry()->get('request')->get, $key, $default );
	}

    /**
	 * @param string $default
     * @return string
     */
	public function ocQueryRoute( $default = 'common/home' ) {
		return $this->query( 'route', $default );
	}

    /**
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
	public function post( $key = null, $default = null ) {
		return ocme()->data()->get( ocme()->oc()->registry()->get('request')->post, $key, $default );
	}

    /**
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
	public function server( $key = null, $default = null ) {
		return ocme()->data()->get( ocme()->oc()->registry()->get('request')->server, $key, $default );
	}

    /**
     * @param  string|array  $key
     * @param  bool
     */
	public function has( $key ) {
		/* @var $keys array */
		$keys = is_array( $key ) ? $key : func_get_args();
		
		foreach( $keys as $value ) {
			if( $this->input( $value ) === null ) {
				return false;
			}
		}
		
		return true;
	}

    /**
     * @param  string|array  $key
     * @param  bool
     */
	public function hasAny( $key ) {
		/* @var $keys array */
		$keys = is_array( $key ) ? $key : func_get_args();
		
		foreach( $keys as $value ) {
			if( $this->input( $value ) !== null ) {
				return true;
			}
		}
		
		return false;
	}

    /**
     * @param  string|array  $key
     * @param  bool
     */
	public function hasQuery( $key ) {
		/* @var $keys array */
		$keys = is_array( $key ) ? $key : func_get_args();
		
		foreach( $keys as $value ) {
			if( ! ocme()->arr()->has( ocme()->oc()->registry()->get('request')->get, $value ) ) {
				return false;
			}
		}
		
		return true;
	}

    /**
     * @param  string  $key
     * @return bool
     */
	public function hasPost( $key ) {
		/* @var $keys array */
		$keys = is_array( $key ) ? $key : func_get_args();
		
		foreach( $keys as $value ) {
			if( ! ocme()->arr()->has( ocme()->oc()->registry()->get('request')->post, $value ) ) {
				return false;
			}
		}
		
		return true;
	}

    /**
     * @param  string  $key
     * @return bool
     */
	public function hasServer( $key ) {
		/* @var $keys array */
		$keys = is_array( $key ) ? $key : func_get_args();
		
		foreach( $keys as $value ) {
			if( ! ocme()->arr()->has( ocme()->oc()->registry()->get('request')->server, $value ) ) {
				return false;
			}
		}
		
		return true;
	}
	
	public function whenFilled( $key, \Closure $callback ) {
		if( $this->filled( $key ) ) {
			return $callback( $this->input( $key ) );
		}
	}
	
	public function whenHas( $key, \Closure $callback ) {
		if( $this->has( $key ) ) {
			return $callback( $this->input( $key ) );
		}
	}
	
	public function filled( $key ) {
		return $this->has( $key ) && $this->input( $key ) !== '';
	}

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function initialize()
    {
		$this->headers = ocme()->collection()->make( $this->requestHeaders() );
	}
	
	public function requestHeaders() {
		if( function_exists( 'apache_request_headers' ) ) {
			return apache_request_headers();
		}
		
		$arh = array();
		$rx_http = '/\AHTTP_/';
		
		foreach(ocme()->oc()->registry()->get('request')->server as $key => $val) {
			if( preg_match($rx_http, $key) ) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = array();
				$rx_matches = explode('_', $arh_key);
				
				if( count($rx_matches) > 0 && strlen($arh_key) > 2 ) {
					foreach($rx_matches as $ak_key => $ak_val) {
						$rx_matches[$ak_key] = ucfirst($ak_val);
					}
					
					$arh_key = implode('-', $rx_matches);
				}
				
				$arh[strtolower($arh_key)] = $val;
			}
		}
		
		return $arh;
	}

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->get('x-requested-with');
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Get the client user agent.
     *
     * @return string
     */
    public function userAgent()
    {
        return $this->headers->get('user-agent');
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    public function ip()
    {
        return $this->getClientIp();
    }

    /**
     * Get the client IP addresses.
     *
     * @return array
     */
    public function ips()
    {
        return $this->getClientIps();
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * @return string|null The client IP address
     *
     * @see getClientIps()
     * @see http://en.wikipedia.org/wiki/X-Forwarded-For
     */
    public function getClientIp()
    {
        $ipAddresses = $this->getClientIps();

        return $ipAddresses[0];
    }

    /**
     * Returns the client IP addresses.
     *
     * In the returned array the most trusted IP address is first, and the
     * least trusted one last. The "real" client IP address is the last one,
     * but this is also the least trusted one. Trusted proxies are stripped.
     *
     * Use this method carefully; you should use getClientIp() instead.
     *
     * @return array The client IP addresses
     *
     * @see getClientIp()
     */
    public function getClientIps()
    {
        $ip = ocme()->arr()->get( ocme()->oc()->registry()->get('request')->server, 'REMOTE_ADDR');

        return array($ip);
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }

    /**
     * If the request method is POST.
     *
     * @return bool
     */
    public function methodIsPost()
    {
        return $this->method() == 'POST';
    }

    /**
     * Gets the request "intended" method.
     *
     * The method is always an uppercased string.
     *
     * @return string The request method
     *
     * @see getRealMethod()
     */
    public function getMethod()
    {
        if( null === $this->method ) {
            $this->method = $this->getRealMethod();
        }

        return $this->method;
    }

    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod()
     */
    public function getRealMethod()
    {
        return strtoupper(ocme()->arr()->get( ocme()->oc()->registry()->get('request')->server, 'REQUEST_METHOD', 'GET' ) );
    }
	
}