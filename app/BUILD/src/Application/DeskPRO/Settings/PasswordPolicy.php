<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

class PasswordPolicy
{
    const UPPERCASE = 'uppercase';
    const LOWERCASE = 'lowercase';
    const NUMBER    = 'number';
    const SYMBOLS   = 'symbols';

    /**
     * Minimum password length.
     *
     * @var int
     */
    public $min_length = 5;

    /**
     * Max age until we prompt user to reset password.
     *
     * @var int
     */
    public $max_age = 0;

    /**
     * Dont allow reuse of old passwords.
     *
     * @var bool
     */
    public $forbid_reuse = false;

    /**
     * (Character class) Require this many uppercase chars.
     *
     * @var int
     */
    public $require_num_uppercase = 0;

    /**
     * (Character class) Require this many lowercase chars.
     *
     * @var int
     */
    public $require_num_lowercase = 0;

    /**
     * (Character class) Require this many numbers.
     *
     * @var int
     */
    public $require_num_number = 0;

    /**
     * (Character class) Require this many symbols.
     *
     * @var int
     */
    public $require_num_symbol = 0;

    /**
     * Makes sure the values are the correct types and valid ranges.
     */
    public function verify()
    {
        $this->min_length = (int) $this->min_length;
        if ($this->min_length < 0) {
            $this->min_length = 0;
        }

        $this->max_age = (int) $this->max_age;
        if ($this->max_age < 0) {
            $this->max_age = 0;
        }

        $this->forbid_reuse = (bool) $this->forbid_reuse;

        $this->require_num_uppercase = (int) $this->require_num_uppercase;
        if ($this->require_num_uppercase < 0) {
            $this->require_num_uppercase = 0;
        }

        $this->require_num_lowercase = (int) $this->require_num_lowercase;
        if ($this->require_num_lowercase < 0) {
            $this->require_num_lowercase = 0;
        }

        $this->require_num_number = (int) $this->require_num_number;
        if ($this->require_num_number < 0) {
            $this->require_num_number = 0;
        }

        $this->require_num_symbol = (int) $this->require_num_symbol;
        if ($this->require_num_symbol < 0) {
            $this->require_num_symbol = 0;
        }

        $total = $this->require_num_uppercase + $this->require_num_lowercase + $this->require_num_number + $this->require_num_symbol;
        if ($this->min_length > 0 && $this->min_length < $total) {
            $this->min_length = $total;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'min_length'            => $this->min_length,
            'max_age'               => $this->max_age,
            'forbid_reuse'          => $this->forbid_reuse,
            'require_num_uppercase' => $this->require_num_uppercase,
            'require_num_lowercase' => $this->require_num_lowercase,
            'require_num_number'    => $this->require_num_number,
            'require_num_symbol'    => $this->require_num_symbol,
        ];
    }

    /**
     * @param array $values
     */
    public function fromArray(array $values)
    {
        foreach ([
            'min_length',
            'max_age',
            'forbid_reuse',
            'require_num_uppercase',
            'require_num_lowercase',
            'require_num_number',
            'require_num_symbol',
        ] as $name) {
            if (isset($values[$name])) {
                $this->$name = $values[$name];
            }
        }

        $this->verify();
    }
}
