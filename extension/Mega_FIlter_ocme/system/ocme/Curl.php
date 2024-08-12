<?php namespace Ocme;

use stdClass;

class Curl {
	
    /* @var resource $curl */
    protected $curl = null;

    /* @var array $options */
    protected $options = array(
        'RETURNTRANSFER'        => true,
        'FAILONERROR'           => false,
        'FOLLOWLOCATION'        => false,
        'CONNECTTIMEOUT'        => '',
        'TIMEOUT'               => 30,
        'USERAGENT'             => '',
        'URL'                   => '',
        'POST'                  => false,
        'HTTPHEADER'            => array(),
        'SSL_VERIFYPEER'        => false,
        'HEADER'                => false,
    );

    /* @var array $parameters */
    protected $parameters = array(
        'data'                  => array(),
        'files'                 => array(),
        'asJsonRequest'         => false,
        'asJsonResponse'        => false,
        'returnAsArray'         => false,
        'responseObject'        => false,
        'responseArray'         => false,
        'enableDebug'           => false,
        'xDebugSessionName'     => '',
        'containsFile'          => false,
        'debugFile'             => '',
        'saveFile'              => '',
    );


    /**
     * Set the URL to which the request is to be sent
     *
     * @param string $url The URL to which the request is to be sent
     * @return \static
     */
    public function to( $url ) {
        return $this->withCurlOption( 'URL', $url );
    }

    /**
     * Set the request timeout
     *
     * @param float $timeout The timeout for the request (in seconds, fractions of a second are okay. Default: 30 seconds)
     * @return \static
     */
    public function withTimeout( $timeout = 30.0 ) {
        return $this->withCurlOption( 'TIMEOUT_MS', ( $timeout * 1000 ) );
    }

    /**
     * Add GET or POST data to the request
     *
     * @param mixed $data Array of data that is to be sent along with the request
     * @return \static
     */
    public function withData( $data = array() ) {
        return $this->withPackageOption( 'data', $data );
    }

    /**
     * Add a file to the request
     *
     * @param string $key Identifier of the file (how it will be referenced by the server in the $_FILES array)
     * @param string $path Full path to the file you want to send
     * @param string $mimeType Mime type of the file
     * @param string $postFileName Name of the file when sent. Defaults to file name
     *
     * @return \static
     */
    public function withFile( $key, $path, $mimeType = '', $postFileName = '' ) {
        $fileData = array(
            'fileName' => $path,
            'mimeType' => $mimeType,
            'postFileName' => $postFileName,
        );

        $this->parameters['files'][$key] = $fileData;

        return $this->containsFile();
    }

    /**
     * Allow for redirects in the request
     *
     * @return \static
     */
    public function allowRedirect( $allow = true ) {
        return $this->withCurlOption( 'FOLLOWLOCATION', (bool) $allow );
    }

    /**
     * Configure the package to encode and decode the request data
     *
     * @param boolean $asArray    Indicates whether or not the data should be returned as an array. Default: false
     * @return \static
     */
    public function asJson( $asArray = false ) {
        return $this->asJsonRequest()
            ->asJsonResponse( (bool) $asArray );
    }

    /**
     * Configure the package to encode the request data to json before sending it to the server
     *
     * @return \static
     */
    public function asJsonRequest( $asJsonRequest = true ) {
        return $this->withPackageOption( 'asJsonRequest', (bool) $asJsonRequest );
    }

    /**
     * Configure the package to decode the request data from json to object or associative array
     *
     * @param boolean $asArray Indicates whether or not the data should be returned as an array. Default: false
     * @return \static
     */
    public function asJsonResponse( $asArray = false ) {
        return $this->withPackageOption( 'asJsonResponse', true )
            ->withPackageOption( 'returnAsArray', $asArray );
    }

    /**
     * Set any specific cURL option
     *
     * @param string $key The name of the cURL option
     * @param string $value The value to which the option is to be set
     * @return \static
     */
    public function withOption( $key, $value ) {
        return $this->withCurlOption( $key, $value );
    }

    /**
     * Set Cookie File
     *
     * @param   string $cookieFile  File name to read cookies from
     * @return \static
     */
    public function setCookieFile( $cookieFile ) {
        return $this->withOption( 'COOKIEFILE', $cookieFile );
    }

    /**
     * Set Cookie Jar
     *
     * @param string $cookieJar File name to store cookies to
     * @return \static
     */
    public function setCookieJar( $cookieJar ) {
        return $this->withOption( 'COOKIEJAR', $cookieJar );
    }
    
    /**
     * Set any specific cURL option
     *
     * @param string $key The name of the cURL option
     * @param string $value The value to which the option is to be set
     * @return \static
     */
    protected function withCurlOption( $key, $value ) {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set any specific package option
     *
     * @param string $key The name of the cURL option
     * @param string $value The value to which the option is to be set
     * @return \static
     */
    protected function withPackageOption( $key, $value ) {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Add a HTTP header to the request
     *
     * @param string $header The HTTP header that is to be added to the request
     * @return \static
     */
    public function withHeader( $header ) {
        $this->options['HTTPHEADER'][] = $header;

        return $this;
    }

    /**
     * Add multiple HTTP header at the same time to the request
     *
     * @param array $headers Array of HTTP headers that must be added to the request
     * @return \static
     */
    public function withHeaders(array $headers) {
        $this->options['HTTPHEADER'] = array_merge(
            $this->options['HTTPHEADER'], $headers
        );

        return $this;
    }

    /**
     * Add a content type HTTP header to the request
     *
     * @param string $contentType The content type of the file you would like to download
     * @return \static
     */
    public function withContentType( $contentType ) {
        return $this->withHeader( 'Content-Type: '. $contentType )
            ->withHeader( 'Connection: Keep-Alive' );
    }

    /**
     * Add response headers to the response object or response array
     *
     * @return \static
     */
    public function withResponseHeaders() {
        return $this->withCurlOption( 'HEADER', TRUE );
    }

    /**
     * Return a full response object with HTTP status and headers instead of only the content
     *
     * @return \static
     */
    public function returnResponseObject() {
        return $this->withPackageOption( 'responseObject', true );
    }

    /**
     * Return a full response array with HTTP status and headers instead of only the content
     *
     * @return \static
     */
    public function returnResponseArray() {
        return $this->withPackageOption( 'responseArray', true );
    }

    /**
     * Enable debug mode for the cURL request
     *
     * @param string $logFile The full path to the log file you want to use
     * @return \static
     */
    public function enableDebug( $logFile ) {
        return $this->withPackageOption( 'enableDebug', true )
            ->withPackageOption( 'debugFile', $logFile )
            ->withOption( 'VERBOSE', true );
    }

    /**
     * Enable Proxy for the cURL request
     *
     * @param string $proxy Hostname
     * @param string $port Port to be used
     * @param string $type Scheme to be used by the proxy
     * @param string $username Authentication username
     * @param string $password Authentication password
     * @return \static
     */
    public function withProxy($proxy, $port = '', $type = '', $username = '', $password = '') {
        $this->withOption( 'PROXY', $proxy );

        if( ! empty( $port ) ) {
            $this->withOption( 'PROXYPORT', $port );
        }

        if( ! empty( $type ) ) {
            $this->withOption( 'PROXYTYPE', $type );
        }

        if( ! empty( $username ) && ! empty( $password ) ) {
            $this->withOption( 'PROXYUSERPWD', $username .':'. $password );
        }

        return $this;
    }

    /**
     * Enable File sending
     *
     * @return \static
     */
    public function containsFile() {
        return $this->withPackageOption( 'containsFile', true );
    }

    /**
     * Add the XDebug session name to the request to allow for easy debugging
     *
     * @param string $sessionName
     * @return \static
     */
    public function enableXDebug( $sessionName = 'session_1' ) {
        $this->parameters['xDebugSessionName'] = $sessionName;

        return $this;
    }

    /**
     * Send a GET request to a URL using the specified cURL options
     *
     * @return mixed
     */
    public function get() {
        return $this->appendDataToURL()->send();
    }

    /**
     * Send a POST request to a URL using the specified cURL options
     *
     * @return mixed
     */
    public function post() {
        return $this->setPostParameters()->send();
    }

     /**
      * Send a download request to a URL using the specified cURL options
      *
      * @param string $fileName
      * @return mixed
      */
     public function download( $fileName ) {
         $this->parameters['saveFile'] = $fileName;

         return $this->send();
     }

    /**
     * Add POST parameters to the options array
	 * 
     * @return \static
     */
    protected function setPostParameters() {
        $this->options['POST'] = true;

        $parameters = $this->parameters['data'];
		
        if( ! empty( $this->parameters['files'] ) ) {
            foreach( $this->parameters['files'] as $key => $file ) {
                $parameters[$key] = $this->getCurlFileValue( $file['fileName'], $file['mimeType'], $file['postFileName'] );
            }
        }

        if( $this->parameters['asJsonRequest'] ) {
            $parameters = json_encode( $parameters );
        }

        $this->options['POSTFIELDS'] = $parameters;
		
		return $this;
    }

    protected function getCurlFileValue( $filename, $mimeType, $postFileName ) {
        // PHP 5 >= 5.5.0, PHP 7
        if( function_exists('curl_file_create') ) {
            return curl_file_create($filename, $mimeType, $postFileName);
        }

        // Use the old style if using an older version of PHP
        $value = "@{$filename};filename=" . $postFileName;
		
        if( $mimeType ) {
            $value .= ';type=' . $mimeType;
        }

        return $value;
    }

    /**
     * Send a PUT request to a URL using the specified cURL options
     *
     * @return mixed
     */
    public function put() {
		return $this->setPostParameters()->withOption('CUSTOMREQUEST', 'PUT')->send();
    }

    /**
     * Send a PATCH request to a URL using the specified cURL options
     *
     * @return mixed
     */
    public function patch() {
		return $this->setPostParameters()->withOption('CUSTOMREQUEST', 'PATCH')->send();
    }

    /**
     * Send a DELETE request to a URL using the specified cURL options
     *
     * @return mixed
     */
    public function delete() {
		return $this->appendDataToURL()->withOption('CUSTOMREQUEST', 'DELETE')->send();
    }

    /**
     * Send the request
     *
     * @return mixed
     */
    protected function send() {
        // Add JSON header if necessary
        if( $this->parameters['asJsonRequest'] ) {
            $this->withHeader( 'Content-Type: application/json' );
        }

        if( $this->parameters['enableDebug'] ) {
            $debugFile = fopen( $this->parameters['debugFile'], 'w');
            $this->withOption('STDERR', $debugFile);
        }

        // Create the request with all specified options
        $this->curl = curl_init();
		
		/* @var $options array */
        $options = $this->forgeOptions();
		
        curl_setopt_array( $this->curl, $options );

        // Send the request
        $response = curl_exec( $this->curl );

		/* @var $responseHeader string|null */
        $responseHeader = null;
		
        if( $this->options['HEADER'] ) {
            $headerSize = curl_getinfo( $this->curl, CURLINFO_HEADER_SIZE );
            $responseHeader = substr( $response, 0, $headerSize );
            $response = substr( $response, $headerSize );
        }

        // Capture additional request information if needed
		
		/* @var $responseData array */
        $responseData = array();
		
        if( $this->parameters['responseObject'] || $this->parameters['responseArray'] ) {
            $responseData = curl_getinfo( $this->curl );

            if( curl_errno( $this->curl ) ) {
                $responseData['errorMessage'] = curl_error($this->curl);
            }
        }
		
        curl_close( $this->curl );
		
        if( $this->parameters['saveFile'] ) {
            // Save to file if a filename was specified
            $file = fopen($this->parameters['saveFile'], 'w');
            fwrite($file, $response);
            fclose($file);
        } else if( $this->parameters['asJsonResponse'] ) {
            // Decode the request if necessary
            $response = json_decode($response, $this->parameters['returnAsArray']);
        }

        if( $this->parameters['enableDebug'] ) {
            fclose( $debugFile );
        }
		
        // Return the result
        return $this->returnResponse( $response, $responseData, $responseHeader );
    }

    /**
     * @param   string $headerString    Response header string
     * @return mixed
     */
    protected function parseHeaders( $headerString ) {
        $headers = array_filter(array_map(function ($x) {
            $arr = array_map('trim', explode(':', $x, 2));
            if( count( $arr ) == 2 ) {
                return [ $arr[ 0 ] => $arr[ 1 ] ];
            }
        }, array_filter(array_map('trim', explode("\r\n", $headerString)))));

        return $this->arrayCollapse($headers);
    }
	
	protected function arrayCollapse( $array ) {
		$results = [];

        foreach( $array as $values ) {
            if( $values instanceof Collect ) {
                $values = $values->all();
            } elseif ( ! is_array( $values ) ) {
                continue;
            }

            $results = array_merge( $results, $values );
        }

        return $results;
	}

    /**
     * @param   mixed $content          Content of the request
     * @param   array $responseData     Additional response information
     * @param   string $header          Response header string
     * @return mixed
     */
    protected function returnResponse($content, array $responseData = array(), $header = null) {
        if( ! $this->parameters['responseObject'] && !$this->parameters['responseArray'] ) {
            return $content;
        }

		/* @var $object \stdClass */
        $object = new stdClass();
        $object->content = $content;
        $object->status = $responseData['http_code'];
        $object->contentType = $responseData['content_type'];
		
        if( array_key_exists('errorMessage', $responseData) ) {
            $object->error = $responseData['errorMessage'];
        }

        if( $this->options['HEADER'] ) {
            $object->headers = $this->parseHeaders( $header );
        }

        if( $this->parameters['responseObject'] ) {
            return $object;
        }

        if( $this->parameters['responseArray'] ) {
            return (array) $object;
        }

        return $content;
    }

    /**
     * Convert the options to an array of usable options for the cURL request
     *
     * @return array
     */
    protected function forgeOptions() {
		/* @var $results array */
        $results = array();
		
        foreach( $this->options as $key => $value ) {
            $arrayKey = constant( 'CURLOPT_' . $key );

            if( !$this->parameters['containsFile'] && $key == 'POSTFIELDS' && is_array( $value ) ) {
                $results[$arrayKey] = http_build_query( $value, '', '&' );
            } else {
                $results[$arrayKey] = $value;
            }
        }

        if( ! empty( $this->parameters['xDebugSessionName'] ) ) {
            $char = strpos($this->options['URL'], '?') ? '&' : '?';
            $this->options['URL'] .= $char . 'XDEBUG_SESSION_START='. $this->parameters['xDebugSessionName'];
        }

        return $results;
    }

    /**
     * Append set data to the query string for GET and DELETE cURL requests
     *
     * @return \static
     */
    protected function appendDataToURL() {
		/* @var $parameterString string */
        $parameterString = '';
		
        if( is_array( $this->parameters['data'] ) && count( $this->parameters['data'] ) != 0 ) {
            $parameterString = '?'. http_build_query( $this->parameters['data'], '', '&' );
        }
		
		$this->options['URL'] .= $parameterString;
		
		return $this;
    }
}