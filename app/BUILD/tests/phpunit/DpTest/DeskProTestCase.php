<?php

/**
 * DeskPRO.
 */

namespace DpTest;

abstract class DeskProTestCase extends \PHPUnit_Framework_TestCase
{
    use \DpTestSrc\TestBundle\MockHelpers\DbalMocksHelper;

    /**
     * Stub creation helper.
     *
     * @param string $type Class or interface
     *
     * @return object
     */
    protected function stub($type)
    {
        return $this->prophesize($type)->reveal();
    }

    /**
     * @param mixed $value
     * @param array $array
     *
     * @return array
     */
    protected function removeFromArray($value, array $array)
    {
        $this->assertContains($value, $array);
        unset($array[array_search($value, $array)]);

        return $array;
    }
}
