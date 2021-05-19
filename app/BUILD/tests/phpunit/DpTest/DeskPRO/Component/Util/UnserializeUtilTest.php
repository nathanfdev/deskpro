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
            UnserializeUtil::unserializeClass($this->objStr, [self::class, TestClass::class])->myVal,
            'object unserialize with allowedClasses'
        );

        $this->assertEquals(
            $this->obj->myVal,
            UnserializeUtil::unserializeClass($this->objStr, [TestClass::class, self::class])->myVal,
            'object unserialize with allowedClasses 2'
        );
    }

    public function testUnserializeTypes()
    {
        $this->assertEquals(
            $this->arr,
            UnserializeUtil::safeUnserialize($this->arrStr),
            'array unserialize via safeUnserialize'
        );

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

        $this->assertEquals(
            123,
            UnserializeUtil::unserializeInteger('this is invalid', 123),
            'int unserialize default value'
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

    public function testFailArrayDefault()
    {
        $this->assertEquals(
            [],
            UnserializeUtil::unserializeArray($this->scalarStr, [])
        );

        $this->assertEquals(
            null,
            UnserializeUtil::unserializeArray($this->scalarStr, null)
        );
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
        UnserializeUtil::unserializeClass($this->objStr, [self::class]);
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailNoClass()
    {
        UnserializeUtil::unserializeClass($this->objStr, []);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailSafeWithClass()
    {
        UnserializeUtil::safeUnserialize($this->objStr);
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
