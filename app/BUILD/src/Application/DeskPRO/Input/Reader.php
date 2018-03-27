<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Input;

use Orb\Input\Reader\Reader as BaseReader;

/**
 * Class Reader.
 *
 * This class just defines some useful getX methods.
 */
class Reader extends BaseReader
{
    const POST    = 'post';
    const GET     = 'get';
    const REQUEST = 'req';
    const COOKIE  = 'cookie';

    protected $do_not_clean = [
        'password'         => 1,
        'password2'        => 1,
        'new_password'     => 1,
        'new_password2'    => 1,
        'current_password' => 1,
    ];

    /**
     * Gets a string.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return string
     */
    public function getString($name, $source_name = null)
    {
        return isset($this->do_not_clean[$name])
            ? $this->getCleanValue($name, 'raw', $source_name)
            : $this->getCleanValue($name, 'str', $source_name, null);
    }

    /**
     * Gets a string and strips HTML.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return string
     */
    public function getStringNoHtml($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'str_nohtml', $source_name, null);
    }

    /**
     * Gets a raw string. No UTF-8 fixing, no trimming, etc.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return string
     */
    public function getStringRaw($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'str_raw', $source_name, null);
    }

    /**
     * Gets a boolean.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return bool
     */
    public function getBool($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'bool', $source_name, null);
    }

    /**
     * Gets a 1/0 based on boolean input.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return int
     */
    public function getBoolInt($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'ibool', $source_name, null);
    }

    /**
     * Gets an integer.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return int
     */
    public function getInt($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'int', $source_name, null);
    }

    /**
     * Gets an unsigned integer.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return int
     */
    public function getUInt($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'uint', $source_name, null);
    }

    /**
     * Gets a float.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return float
     */
    public function getFloat($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'float', $source_name, null);
    }

    /**
     * Gets an unsigned float.
     *
     * @param string $name
     * @param null   $source_name
     *
     * @return float
     */
    public function getUFloat($name, $source_name = null)
    {
        return $this->getCleanValue($name, 'ufloat', $source_name, null);
    }

    /**
     * Gets an array of integers (with keys discarded).
     *
     * @param $name
     * @param null $source_name
     *
     * @return array
     */
    public function getArrayOfInts($name, $source_name = null)
    {
        return $this->getCleanValueArray($name, 'int', 'discard', $source_name);
    }

    /**
     * Gets an array of unsigned integers (with keys discarded).
     *
     * @param $name
     * @param null $source_name
     *
     * @return array
     */
    public function getArrayOfUInts($name, $source_name = null)
    {
        return $this->getCleanValueArray($name, 'uint', 'discard', $source_name);
    }

    /**
     * Gets an array of strings (with keys discarded).
     *
     * @param $name
     * @param null $source_name
     *
     * @return array
     */
    public function getArrayOfStrings($name, $source_name = null)
    {
        return $this->getCleanValueArray($name, 'str', 'discard', $source_name);
    }

    /**
     * Gets an array of id=>string (where id is uint).
     *
     * @param $name
     * @param null $source_name
     *
     * @return array
     */
    public function getIdMappedStrings($name, $source_name = null)
    {
        return $this->getCleanValueArray($name, 'str', 'uint', $source_name);
    }

    /**
     * Gets an array of id=>int (where id is uint).
     *
     * @param $name
     * @param null $source_name
     *
     * @return array
     */
    public function getIdMappedInts($name, $source_name = null)
    {
        return $this->getCleanValueArray($name, 'int', 'uint', $source_name);
    }

    /**
     * Gets an array of id=>uint (where id is uint).
     *
     * @param $name
     * @param null $source_name
     *
     * @return array
     */
    public function getIdMappedUInts($name, $source_name = null)
    {
        return $this->getCleanValueArray($name, 'uint', 'uint', $source_name);
    }
}
