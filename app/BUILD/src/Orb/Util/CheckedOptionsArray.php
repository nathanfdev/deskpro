<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

use Orb\Validator\Callback as CallbackValidator;
use Orb\Validator\ValidatorInterface;

/**
 * Like a normal options array except that we run validations.
 */
class CheckedOptionsArray extends OptionsArray
{
    /**
     * Array if name=>array(ValidatorInterface).
     *
     * @var array
     */
    private $validators = [];

    /**
     * An array of the only valid names.
     *
     * @var array
     */
    private $valid_names = [];

    /**
     * An array of required names.
     *
     * @var array
     */
    private $required_names = [];

    /**
     * An array of required names.
     *
     * @var array
     */
    private $alias_names = [];

    /**
     * Add required names. If you are also using valid names, required names are automatically
     * considered valid names.
     *
     * @param string|string[] $name...
     */
    public function addRequiredNames($name)
    {
        $args = func_get_args();

        foreach ($args as $a) {
            if (is_array($a)) {
                foreach ($a as $a2) {
                    $this->required_names[$a2] = true;
                }
            } else {
                $this->required_names[$a] = true;
            }
        }
    }

    /**
     * @param $name
     *
     * @return array|string[]
     */
    public function getAliases($name)
    {
        if (array_key_exists($name, $this->alias_names)) {
            return $this->alias_names[$name];
        }

        return [];
    }

    /**
     * @param string         $name
     * @param array|string[] $aliases
     */
    public function setAliases($name, array $aliases)
    {
        $this->alias_names[$name] = $aliases;
    }

    /**
     * Add valid names.
     *
     * @param string|string[] $name...
     */
    public function addValidNames($name)
    {
        $args = func_get_args();

        foreach ($args as $a) {
            if (is_array($a)) {
                foreach ($a as $a2) {
                    $this->valid_names[$a2] = true;
                }
            } else {
                $this->valid_names[$a] = true;
            }
        }
    }

    /**
     * Ensures we have all the required options.
     *
     * @throws CheckedOptionsException
     */
    public function ensureRequired()
    {
        $required_names = array_keys($this->required_names);
        $diff           = array_diff($required_names, array_keys($this->options));
        if ($diff) {
            // check aliases
            foreach ($diff as $required) {
                $requiredAliases = array_intersect(array_keys($this->options), $this->getAliases($required));
                if (empty($requiredAliases)) {
                    throw new CheckedOptionsException('Missing required options: '.implode(', ', $diff), ['required'], ['names' => $diff]);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getRequiredNames()
    {
        return array_keys($this->required_names);
    }

    /**
     * @return array
     */
    public function getValidNames()
    {
        return array_keys($this->valid_names);
    }

    /**
     * Adds a checked option. When $name is set, it will run through the validator.
     *
     * @param string             $name
     * @param ValidatorInterface $validator
     */
    public function addCheckedOption($name, ValidatorInterface $validator)
    {
        if (!isset($this->validators[$name])) {
            $this->validators[$name] = [];
        }

        $this->validators[$name][] = $validator;
    }

    /**
     * Adds a checked option with a custom callback that does the checking.
     *
     * The callback sholud return an array of error codes or boolean false on error. Any other value is considered valid.
     *
     * $callback is passed $val, $name and $extra.
     *
     * @param string   $name     The name of the option
     * @param callback $callback The callback to call
     * @param array    $extra    Extra info to pass to your callback
     */
    public function addCallbackCheckedOption($name, $callback, array $extra = null)
    {
        $fn = function ($val) use ($callback, $name, $extra) {
            $ret = $callback($val, $name, $extra);
            if (is_array($ret)) {
                return $ret;
            }

            if ($ret === false) {
                return [['invalid_value', ['expected_type' => 'callback']]];
            }

            return;
        };

        $validator = new CallbackValidator(['callback_function' => $fn]);
        $this->addCheckedOption($name, $validator);
    }

    /**
     * Add a not-null validator for $name.
     *
     * @param string $name
     */
    public function addNotNullOption($name)
    {
        $fn = function ($val) {
            if ($val === null) {
                return [['null_value', ['expected_type' => 'not_null']]];
            }

            return;
        };

        $validator = new CallbackValidator(['callback_function' => $fn]);
        $this->addCheckedOption($name, $validator);
    }

    /**
     * Ensure $name is an instance of $type.
     *
     * @param string $name
     * @param string $type
     * @param bool   $allow_null
     */
    public function addTypeCheckedOption($name, $type, $allow_null = false)
    {
        $fn = function ($val) use ($name, $type, $allow_null) {
            if ($val === null && $allow_null) {
                return;
            }

            if (!is_object($val)) {
                return [['null_value', ['expected_type' => $type]]];
            }
            if (!($val instanceof $type)) {
                return [['invalid_type', ['expected_type' => $type, 'got_type' => get_class($val)]]];
            }
        };

        $validator = new CallbackValidator(['callback_function' => $fn]);

        $this->addCheckedOption($name, $validator);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws CheckedOptionsException
     */
    public function set($name, $value)
    {
        if ($this->valid_names && (!isset($this->valid_names[$name]) && !isset($this->required_names[$name]))) {
            $foundAlias = false;
            foreach ($this->alias_names as $n => $ans) {
                if ($name == $n || in_array($name, $ans)) {
                    $foundAlias = true;
                    break;
                }
            }
            if (!$foundAlias) {
                throw new CheckedOptionsException('Invalid option name: '.$name, ['invalid_name'], ['name' => $name]);
            }
        }

        if (isset($this->validators[$name])) {
            foreach ($this->validators[$name] as $validator) {
                if (!$validator->isValid($value)) {
                    throw new CheckedOptionsException("`$name` has an invalid option value", $validator->getErrors(), $validator->getErrorsInfo());
                }
            }
        }

        parent::set($name, $value);
    }
}
