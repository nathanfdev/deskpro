<?php

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\UnserializeUtil;
use DpTest\DeskProTestCase;

class ObjUtilsTest extends DeskProTestCase
{
    public $obj;
    public $arr;
    public $scalar;

    public $objStr;
    public $arrStr;
    public $scalarStr;
    public $nullStr;

    public function __construct()
    {
        parent::__construct();

        $this->obj    = new TestClass();
        $this->objStr = serialize($this->obj);

        $this->arr    = [1, 'foo', null];
        $this->arrStr = serialize($this->arr);

        $this->scalar    = 42;
        $this->scalarStr = serialize($this->scalar);

        $this->nullStr = serialize(null);
    }

    public function testUnserialize()
    {
        $this->assertEquals(
            $this->obj->myVal,
            UnserializeUtil::safeUnserialize($this->objStr, [self::class, TestClass::class])->myVal,
            'object unserialize with allowedClasses'
        );

        $this->assertEquals(
            $this->obj->myVal,
            UnserializeUtil::safeUnserialize($this->objStr, [TestClass::class, self::class])->myVal,
            'object unserialize with allowedClasses 2'
        );

        $this->assertEquals(
            $this->obj->myVal,
            UnserializeUtil::safeUnserialize($this->objStr, UnserializeUtil::ALLOW_ALL)->myVal,
            'object unserialize with allowedClasses=ALL'
        );
    }

    public function testUnserializeTypes()
    {
        $this->assertEquals(
            $this->arr,
            UnserializeUtil::unserializeArray($this->arrStr),
            'array unserialize'
        );

        $this->assertEquals(
            $this->scalar,
            UnserializeUtil::unserializeScalar($this->scalarStr),
            'scalar unserialize'
        );

        $this->assertEquals(
            $this->scalar,
            UnserializeUtil::unserializeInteger($this->scalarStr),
            'int unserialize'
        );
    }

    public function testIsNull()
    {
        $this->assertTrue(
            UnserializeUtil::isNull($this->nullStr),
            'isNull'
        );

        $this->assertFalse(
            UnserializeUtil::isNull($this->scalarStr),
            'isNull'
        );
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailArray()
    {
        UnserializeUtil::unserializeArray($this->scalarStr);
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailNullArray()
    {
        UnserializeUtil::unserializeArray($this->nullStr);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailInt()
    {
        UnserializeUtil::unserializeInteger($this->arrStr);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailBadClass()
    {
        UnserializeUtil::safeUnserialize($this->objStr, [self::class]);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailNoClass()
    {
        UnserializeUtil::safeUnserialize($this->objStr, UnserializeUtil::ALLOW_NONE);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailParams()
    {
        UnserializeUtil::safeUnserialize($this->objStr, 55);
    }
}

class TestClass
{
    public $myVal;

    public function __construct($myVal = 'foobar')
    {
        $this->myVal = $myVal;
    }
}
