<?php

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\TypeUtils;
use DpTest\DeskProTestCase;

/**
 * Class TypeUtilsTest.
 */
class TypeUtilsTest extends DeskProTestCase
{
    public function testGetParts()
    {
        $parts = TypeUtils::getTypeNameParts(new TypeUtilsTestClass());
        $this->assertEquals(['DpTest', 'DeskPRO', 'Component', 'Util', 'TypeUtilsTestClass'], $parts);
    }

    public function testGetBaseTypeName()
    {
        $this->assertEquals(TypeUtils::getBaseTypeName(new TypeUtilsTestClass()), 'TypeUtilsTestClass');
    }
}

/**
 * Class TypeUtilsTestClass.
 */
class TypeUtilsTestClass
{
}
