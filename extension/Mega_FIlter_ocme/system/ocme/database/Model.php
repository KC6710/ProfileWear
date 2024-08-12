<?php namespace Ocme\Database;

use Illuminate\Database\Eloquent\Builder,
	Illuminate\Database\Query\Builder as QueryBuilder;

abstract class Model extends \Illuminate\Database\Eloquent\Model {

	/**
	 * The attributes that can be null.
	 *
	 * @var array
	 */
	protected $nullable = array();
	
    /**
     * The cache for the model.
     *
     * @var array
     */
    protected $_cache = array();
	
	/**
	 * Try to flush cache after update/delete record
	 * 
	 * @var bool
	 */
	protected $flushCache = true;

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

    /**
     * Determine if the given attribute may be null.
     *
     * @param  string  $key
     * @return bool
     */
    public function isNullable($key) {
		return in_array( $key, $this->nullable );
    }

	/**
	 * Set a given attribute on the model.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function setAttribute($key, $value)
	{
		// First we will check for the presence of a mutator for the set operation
		// which simply lets the developers tweak the attribute as it is set on
		// the model, such as "json_encoding" an listing of data for storage.
		if ($this->hasSetMutator($key))
		{
			$method = 'set'.studly_case($key).'Attribute';

			return $this->{$method}($value);
		}

		// If an attribute is listed as a "date", we'll convert it from a DateTime
		// instance into a form proper for storage on the database tables using
		// the connection grammar's date format. We will auto set the values.
		elseif (in_array($key, $this->getDates()) && $value)
		{
			$value = $this->fromDateTime($value);
		}
		
		elseif( $this->isNullable( $key ) && $value === '' ) {
			$value = null;
		}

		if ($this->isJsonCastable($key))
		{
			$value = json_encode($value);
		}

		$this->attributes[$key] = $value;
	}

	public function getObservableEvents() {
		return array_merge(
			parent::getObservableEvents(),
			array('validating', 'validated')
		);
	}

	/**
	 * Register a validating model event with the dispatcher.
	 *
	 * @param Closure|string $callback
	 * @return void
	 */
	public static function validating($callback) {
		static::registerModelEvent('validating', $callback);
	}

	/**
	 * Register a validated model event with the dispatcher.
	 *
	 * @param Closure|string $callback
	 * @return void
	 */
	public static function validated($callback) {
		static::registerModelEvent('validated', $callback);
	}
	
	/**
	 * Add the error messages
	 * 
	 * @param \Illuminate\Support\MessageBag|array $validationErrors
	 * @return void
	 */
	public function addErrors( $validationErrors, $prefix = '' ) {
		if( ! $this->validationErrors ) {
			$this->setErrors(new \Illuminate\Support\MessageBag);
		}
		
		if( $validationErrors instanceof \Illuminate\Support\MessageBag ) {
			foreach( $validationErrors->messages() as $key => $messages ) {
				foreach( $messages as $message ) {
					$this->validationErrors->add( $prefix . $key, $message );
				}
			}
		} else {
			foreach( $validationErrors as $key => $messages ) {
				if( ! is_array( $messages ) ) {
					$messages = [ $messages ];
				}
				
				foreach( $messages as $message ) {echo $message;
					$this->validationErrors->add( $prefix . $key, $message );
				}
			}
		}
	}

    /**
     * Validate the model against it's rules, returning whether
     * or not it passes and setting the error messages on the
     * model if required.
     *
     * @param  array $rules
     * @return bool
     * @throws \Watson\Validating\ValidationException
     */
    protected function performValidation( array $rules = [] ) {
        if( $this->fireModelEvent('validating') === false ) {
            return false;
        }
		
		/* @var $validation \Illuminate\Validation\Factory */
        $validation = $this->makeValidator($rules);
		
		/* @var $result bool */
        $result = $validation->passes();
		
        $this->addErrors($validation->messages());

        if( false === $this->fireModelEvent('validated') ) {
			return false;
		}
		
        return $result;
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
		if( $key == 'rules' ) {
			$this->rules = $value;
		} else {
			parent::__set( $key, $value );
		}
    }
}