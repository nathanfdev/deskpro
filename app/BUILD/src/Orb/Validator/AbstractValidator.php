<?php

/**
 * Orb.
 */

namespace Orb\Validator;

/**
 * Validates a value.
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * An array of simple error codes. Language must be handled elsewhere.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Sometimes an error might have additional information, such as a position or
     * context where an error took place. This should be an array of errorcode=>info
     * that could be used in some other system to report errors to a user.
     *
     * @var array
     */
    protected $errors_info = [];

    /**
     * Options for the validator.
     *
     * @var array
     */
    protected $options = [];

    final public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->init();
    }

    protected function init()
    {
    }

    /**
     * Check to see if a value is valid or not.
     *
     * @return bool
     */
    public function isValid($value)
    {
        // Reset
        $this->errors      = [];
        $this->errors_info = [];

        return $this->checkIsValid($value);
    }

    /**
     * Check to see if a value is valid or not.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function __invoke($value)
    {
        return $this->isValid($value);
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    abstract protected function checkIsValid($value);

    /**
     * Get an array of error codes.
     *
     * @return array
     */
    public function getErrors($keyed = false)
    {
        if (!$this->errors) {
            return [];
        }

        if ($keyed) {
            return array_combine($this->errors, array_fill(0, count($this->errors), 1));
        }

        return $this->errors;
    }

    /**
     * Get an array of errcode=>info. Null means no info available.
     *
     * @return array
     */
    public function getErrorsInfo()
    {
        if (!$this->errors) {
            return [];
        }

        $ret = [];
        foreach ($this->errors as $k) {
            $ret[$k] = isset($this->errors_info[$k]) ? $this->errors_info[$k] : null;
        }

        return $ret;
    }

    /**
     * Using dot notation in error codes, we can sort errors into groups
     * so you can easily test classes of errors. This will return those classes.
     *
     * For example:
     * - Error code: profile.name.short
     * - Returns classes: profile, profile.name
     *
     * @return
     */
    public function getErrorGroups($keyed = false)
    {
        if (!$this->errors) {
            return [];
        }

        $groups = [];

        foreach ($this->getErrors() as $code) {
            if (strpos($code, '.') === false) {
                continue;
            }

            $parts = explode('.', $code);
            array_pop($parts);
            while ($parts) {
                $groups[implode('.', $parts)] = 1;
                array_pop($parts);
            }
        }

        $groups = array_keys($groups);

        if ($keyed) {
            $groups = array_combine($groups, array_fill(0, count($groups), 1));
        }

        return $groups;
    }

    /**
     * Get a string of all errors and info that can help in debugging.
     *
     * @return string
     */
    public function getErrorsDebug()
    {
        $ret = [];
        foreach ($this->errors as $k => $errcode) {
            $line = $errcode;
            if (!empty($this->errors_info[$k])) {
                $line .= ' :: '.$this->errors_info[$k];
            }

            $ret[] = $line;
        }

        return implode("\n", $ret);
    }

    /**
     * Add an error to the errors array.
     *
     * @param string $code       The error code to add
     * @param mixed  $error_info Additional info that can help explain the error
     */
    protected function addError($code, $error_info = null)
    {
        $this->errors[] = $code;
        array_unique($this->errors);

        if ($error_info !== null) {
            $this->errors_info[$code] = $error_info;
        }
    }

    /**
     * Check if a certain error has occurred.
     *
     * @param string $code
     *
     * @return bool
     */
    public function hasError($code)
    {
        return in_array($code, $this->errors);
    }

    /**
     * Gets info about the error if the validator set anything.
     * If nothing was set but the error exists, then $code is just
     * given back to you.
     *
     * @param string $code
     *
     * @return mixed
     */
    public function getErrorInfo($code)
    {
        if (!$this->hasError($code)) {
            return;
        }

        if (isset($this->errors_info[$code])) {
            return $this->errors_info[$code];
        }

        return $code;
    }

    /**
     * Remove an error from the collection.
     *
     * @param string $code
     */
    public function removeError($code)
    {
        $this->errors = \Orb\Util\Arrays::removeValue($this->errors, $code);
        unset($this->errors_info[$code]);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * @param string $name
     * @param mxied  $value
     */
    public function setOption($name, $value)
    {
        $this->option[$name] = $value;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }
}
