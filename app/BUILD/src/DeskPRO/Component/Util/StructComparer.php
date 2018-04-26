<?php

namespace DeskPRO\Component\Util;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class StructComparer
{
    private $findByIndex  = null;
    private $findByProp   = null;
    private $findByKey    = null;
    private $findByGetter = null;
    private $findByPath   = null;

    /**
     * @return StructComparer
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param string $id
     * @param mixed  $findValue
     * @param bool   $strict
     *
     * @return StructComparer
     */
    public static function byProp($id, $findValue, $strict = false)
    {
        return self::create()->setFindByProp($id, $findValue, $strict);
    }

    /**
     * @param string $id
     * @param mixed  $findValue
     * @param bool   $strict
     *
     * @return StructComparer
     */
    public static function byKey($id, $findValue, $strict = false)
    {
        return self::create()->setFindByKey($id, $findValue, $strict);
    }

    /**
     * @param string $id
     * @param mixed  $findValue
     * @param bool   $strict
     *
     * @return StructComparer
     */
    public static function byGetter($id, $findValue, $strict = false)
    {
        return self::create()->setFindByGetter($id, $findValue, $strict);
    }

    /**
     * @param string           $id
     * @param mixed            $findValue
     * @param bool             $strict
     * @param PropertyAccessor $propertyAccessor
     *
     * @return StructComparer
     */
    public static function byPath($id, $findValue, $strict = false, PropertyAccessor $propertyAccessor = null)
    {
        return self::create()->setFindByPath($id, $findValue, $strict, $propertyAccessor);
    }

    /**
     * @param string $id
     * @param mixed  $compareValue
     * @param bool   $strict
     *
     * @return StructComparer
     */
    public function setFindByIndex($findValue, $strict = false)
    {
        $this->findByIndex = [null, $findValue, $strict];

        return $this;
    }

    /**
     * @param string $id
     * @param mixed  $compareValue
     * @param bool   $strict
     *
     * @return StructComparer
     */
    public function setFindByProp($id, $findValue, $strict = false)
    {
        $this->findByProp = [$id, $findValue, $strict];

        return $this;
    }

    /**
     * @param string $id
     * @param mixed  $compareValue
     * @param bool   $strict
     *
     * @return StructComparer
     */
    public function setFindByKey($id, $findValue, $strict = false)
    {
        $this->findByKey = [$id, $findValue, $strict];

        return $this;
    }

    /**
     * @param string $id
     * @param mixed  $compareValue
     * @param bool   $strict
     *
     * @return StructComparer
     */
    public function setFindByGetter($id, $findValue, $strict = false)
    {
        $this->findByGetter = [$id, $findValue, $strict];

        return $this;
    }

    /**
     * @param string           $id
     * @param mixed            $compareValue
     * @param bool             $strict
     * @param PropertyAccessor $propertyAccessor
     *
     * @return StructComparer
     */
    public function setFindByPath($id, $findValue, $strict = false, PropertyAccessor $propertyAccessor = null)
    {
        if (!$propertyAccessor) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
        }
        $this->findByPath = [$id, $findValue, $strict, $propertyAccessor];

        return $this;
    }

    private function cmp($val1, $val2, $strict)
    {
        if ($strict) {
            return $val1 === $val2;
        } else {
            return $val1 == $val2;
        }
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function testValue($value)
    {
        $match = false;

        if ($this->findByIndex) {
            if ($this->cmp($idx, $this->findByIndex[1], $this->findByIndex[2])) {
                $match = true;
            }
        }

        if (!$match && $this->findByKey && (is_array($value) || $value instanceof \ArrayAccess)) {
            if ($this->cmp($value[$this->findByKey[0]], $this->findByKey[1], $this->findByKey[2])) {
                $match = true;
            }
        }

        if (!$match && $this->findByProp && is_object($value)) {
            if ($this->cmp($value->{$this->findByProp[0]}, $this->findByProp[1], $this->findByProp[2])) {
                $match = true;
            }
        }

        if (!$match && $this->findByGetter && is_object($value)) {
            if ($this->cmp($value->{$this->findByGetter[0]}(), $this->findByGetter[1], $this->findByGetter[2])) {
                $match = true;
            }
        }

        if (!$match && $this->findByPath) {
            /** @var PropertyAccessor $pc */
            $pc = $this->findByPath[3];
            if ($this->cmp($pc->getValue($value, $this->findByPath[0]), $this->findByGetter[1], $this->findByGetter[3])) {
                $match = true;
            }
        }

        return $match;
    }

    public function __invoke($value)
    {
        return $this->testValue($value);
    }
}
