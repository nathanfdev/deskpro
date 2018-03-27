<?php

/**
 * Orb.
 */

namespace Orb\Validator;

/**
 * A validator that calls a callback function.
 */
class Callback extends AbstractValidator
{
    /**
     * Special value to represent the argument we'll place the arg in.
     */
    const ARG_PLACEHOLDER = '__ORB_VALIDATOR_CALLBACK_ARG_PLACEHOLDER__';

    /**
     * The callback to call.
     *
     * @var mixed
     */
    protected $callback_fn;

    /**
     * Arguments to pass to the callback.
     *
     * @var array
     */
    protected $callback_args = [];

    /**
     * The position where the value in args should be set.
     *
     * @var int
     */
    protected $callback_value_arg_pos = 0;

    /**
     * $callback_args should contain ARG_PLACEHOLDER in the array where the value
     * should be called. If ARG_PLACEHOLDER is not found, then the value will always
     * be the first argument.
     *
     * The callback must return an error code, or an array of error codes, upon error.
     * Return false if no errors.
     *
     * @param mixed $callback_fn   The callback to call
     * @param array $callback_args The arguments to pass to the callback
     */
    public function init()
    {
        $callback_fn   = $this->getOption('callback_function');
        $callback_args = $this->getOption('callback_args', []);

        $this->callback_fn = $callback_fn;

        // The position of the value in the callback must be defined by using the special
        // placeholder. If it's not found, it'll be the first value
        $pos = array_search(self::ARG_PLACEHOLDER, $callback_args, true);
        if (!$pos) {
            $pos = 0;
            array_unshift($callback_args, self::ARG_PLACEHOLDER);
        }

        $this->callback_args          = $callback_args;
        $this->callback_value_arg_pos = $pos;
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        $args                                = $this->callback_args;
        $args[$this->callback_value_arg_pos] = $value;

        $errors = call_user_func_array($this->callback_fn, $args);

        if ($errors) {
            if (!is_array($errors)) {
                $errors = [$errors];
            }

            foreach ($errors as $info) {
                if (is_array($info)) {
                    $this->addError($info[0], $info[1]);
                } else {
                    $this->addError($info);
                }
            }

            return false;
        }

        return true;
    }
}
