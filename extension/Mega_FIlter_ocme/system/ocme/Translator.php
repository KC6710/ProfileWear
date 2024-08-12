<?php namespace Ocme;

class Translator {
	
    /**
     * A cache of the parsed items.
     *
     * @var array
     */
    protected $parsed = array();

    /**
     * The array of loaded translation groups.
     *
     * @var array
     */
    protected $loaded = array();

    /**
     * Parse a key into namespace, group, and item.
     *
     * @param  string  $key
     * @return array
     */
    public function parseKey($key)
    {
        $segments = $this->_parseKey($key);

        if (is_null($segments[0])) {
            $segments[0] = '*';
        }

        return $segments;
    }

    /**
     * Parse a key into namespace, group, and item.
     *
     * @param  string  $key
     * @return array
     */
    public function _parseKey($key)
    {
        if (isset($this->parsed[$key])) {
            return $this->parsed[$key];
        }
		
        if (strpos($key, '::') === false) {
            $segments = explode('.', $key);

            $parsed = $this->parseBasicSegments($segments);
        } else {
            $parsed = $this->parseNamespacedSegments($key);
        }
		
        return $this->parsed[$key] = $parsed;
    }

    /**
     * Parse an array of basic segments.
     *
     * @param  array  $segments
     * @return array
     */
    protected function parseBasicSegments(array $segments)
    {
        $group = $segments[0];

        if (count($segments) == 1) {
            return [null, $group, null];
        } else {
            $item = implode('.', array_slice($segments, 1));

            return [null, $group, $item];
        }
    }

    /**
     * Parse an array of namespaced segments.
     *
     * @param  string  $key
     * @return array
     */
    protected function parseNamespacedSegments($key)
    {
        list($namespace, $item) = explode('::', $key);
		
        $itemSegments = explode('.', $item);

        $groupAndItem = array_slice($this->parseBasicSegments($itemSegments), 1);

        return array_merge([$namespace], $groupAndItem);
    }

    /**
     * Set the parsed value of a key.
     *
     * @param  string  $key
     * @param  array   $parsed
     * @return void
     */
    public function setParsedKey($key, $parsed)
    {
        $this->parsed[$key] = $parsed;
    }

    /**
     * Get the translation for the given key.
     *
     * @param  string  $key
     * @param  array   $replace
     * @return string|array|null
     */
    public function get($key, array $replace = array())
    {
		/* @var $locale string */
		$locale = $this->directory();
		
        list($namespace, $group, $item) = $this->parseKey($key);

        $this->load($namespace, $group, $locale);
		
		if( null === ( $line = $this->getLine( $namespace, $group, $locale, $item, $replace ) ) ) {
			return $key;
		}

        return $line;
    }

    /**
     * Retrieve a language line out the loaded array.
     *
     * @param  string  $namespace
     * @param  string  $group
     * @param  string  $locale
     * @param  string  $item
     * @param  array   $replace
     * @return string|array|null
     */
    protected function getLine($namespace, $group, $locale, $item, array $replace)
    {
        $line = ocme()->arr()->get($this->loaded[$namespace][$group][$locale], $item);

        if (is_string($line)) {
            return $this->makeReplacements($line, $replace);
        } elseif (is_array($line) && count($line) > 0) {
            return $line;
        }
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param  string  $line
     * @param  array   $replace
     * @return string
     */
    protected function makeReplacements($line, array $replace)
    {
        $replace = $this->sortReplacements($replace);

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':'.ocme()->str()->upper($key), ':'.ocme()->str()->ucfirst($key), ':'.$key],
                [ocme()->str()->upper($value), ocme()->str()->ucfirst($value), $value],
                $line
            );
        }
		
        return $line;
    }

    /**
     * Sort the replacements array.
     *
     * @param  array  $replace
     * @return array
     */
    protected function sortReplacements(array $replace)
    {
        return ocme()->collection()->make($replace)->sortBy(function ($value, $key) {
            return mb_strlen($key) * -1;
        });
    }
	
	/**
	 * Get the directory of current language
	 * 
	 * @return string
	 */
	public function defaultDirectory() {
		if( version_compare( VERSION, '4', '>=' ) ) {
			return ocme()->ocRegistry()->get('config')->get('language_code');
		}
		
		return ocme()->ocRegistry()->get('config')->get('language_directory');
	}
	
	/**
	 * Get the directory of current language
	 * 
	 * @return string
	 */
	public function directory() {
		return ocme()->ocRegistry()->get('config')->get('config_language');
	}
	
	protected function sanitize( $string ) {
		return preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $string);
	}

    /**
     * Load the specified language group.
     *
     * @param  string  $namespace
     * @param  string  $group
	 * @param  string  $locale
     * @return void
     */
    public function load($namespace, $group, $locale)
    {		
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }
		
		/* @var $ds string */
		$ds = DIRECTORY_SEPARATOR;
		
		// Sanitize
		$end = $this->sanitize( $namespace ) . $ds . $this->sanitize( $group );
		
		/* @var $path string */
		$path = DIR_OCME . 'open-cart' . $ds . strtolower( ocme()->environment() ) . $ds . 'language' . $ds . $locale . $ds . $end . '.php';
		
		if( file_exists( $path ) ) {
			$lines = include $path;
		} else {
			$path = DIR_OCME . 'open-cart' . $ds . strtolower( ocme()->environment() ) . $ds . 'language' . $ds . $this->defaultDirectory() . $ds . $end . '.php';
			
			if( file_exists( $path ) ) {
				$lines = include $path;
			} else {
				$lines = array();
			}
		}

        $this->loaded[$namespace][$group][$locale] = $lines;
    }

    /**
     * Determine if the given group has been loaded.
     *
     * @param  string  $namespace
     * @param  string  $group
     * @param  string  $locale
     * @return bool
     */
    protected function isLoaded($namespace, $group, $locale)
    {
        return isset($this->loaded[$namespace][$group][$locale]);
    }	
	
}