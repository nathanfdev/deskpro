<?php

/**
 * Orb.
 */

namespace Orb\Validator;

use Orb\Util\Util;

/**
 * A simple adapter that lets you use any ZF validator.
 */
class ZendValidator extends AbstractValidator
{
    /**
     * @var Zend\Validator\AbstractValidator
     */
    protected $zend_validator;

    public function init()
    {
        $this->zend_validator = $this->getOption('zend_validator');
    }

    /**
     * Create a new ZendValidator and automatically instantiate the Zend validator.
     *
     * <code>
     * $v = ZendValidator::factory('Hostname');
     * $v = ZendValidator::factory('Between', array('min' => 10, 'max' => 20'));
     * $v = ZendValidator::factory('Alnum', true);
     * </code>
     *
     * @param string $name     The Zend validator classname
     * @param mixed  $param... Any parameters to pass to the validator constructor
     *
     * @return Orb\Validator\ZendValidator
     */
    public static function factory($name)
    {
        $classname = $name;
        if (!class_exists($classname)) {
            $classname = 'Zend\\Validator\\'.$name;
            if (!class_exists($classname)) {
                throw new \InvalidArgumentException('Could not find any validator class named `'.$name.'`');
            }
        }
        if (!($classname instanceof \Zend\Validator\AbstractValidator)) {
            throw new \InvalidArgumentException('Invalid validator `'.$name.'`: It must be of type Zend\\Validator\\AbstractValidator');
        }

        $args = func_get_args();
        array_shift($args); // get rid of $name

        $zend_validator = Util::callUserConstructorArray($classname, $args);

        return new self(['zend_validator' => $zend_validator]);
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if ($this->zend_validator->isValid($value)) {
            return true;
        }

        $this->errors      = $this->zend_validator->getErrors();
        $this->errors_info = $this->zend_validator->getMessages();

        return false;
    }

    /**
     * @return Zend\Validator\AbstractValidator
     */
    public function getZendValidator()
    {
        return $this->zend_validator;
    }
}
