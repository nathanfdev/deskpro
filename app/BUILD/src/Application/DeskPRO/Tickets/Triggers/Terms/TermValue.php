<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

class TermValue
{
    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @var callback
     */
    private $value_callback;

    /**
     * @param mixed $value
     *
     * @return TermValue
     */
    public static function createWithValue($value)
    {
        return new self($value, null);
    }

    /**
     * @param callback $value_callback
     *
     * @return TermValue
     */
    public static function createWithCallback($value_callback)
    {
        return new self(null, $value_callback);
    }

    /**
     * @param mixed    $value
     * @param callback $value_callback
     */
    private function __construct($value = null, $value_callback = null)
    {
        $this->value          = $value;
        $this->value_callback = $value_callback;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->value_callback) {
            return call_user_func($this->value_callback);
        }

        return $this->value;
    }
}
