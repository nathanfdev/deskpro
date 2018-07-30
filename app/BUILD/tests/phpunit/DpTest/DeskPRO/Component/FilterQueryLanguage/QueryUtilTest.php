<?php

namespace DpTest\DeskPRO\Component\FilterQueryLanguage;

use DeskPRO\Component\FilterQueryLanguage\QueryUtil;

/**
 * Class QueryUtilTest.
 */
class QueryUtilTest extends \PHPUnit_Framework_TestCase
{
    public function test_iso_format()
    {
        list($op, $value) = QueryUtil::parseDateFieldFromQuery('20180607T040506+0500');

        $this->assertEquals('>=', $op);
        $this->assertEquals('"2018-06-07T04:05:06+05:00"', $value);
    }

    public function test_single_value_with_time()
    {
        list($op, $value) = QueryUtil::parseDateFieldFromQuery('2018-06-14 14:40');

        $this->assertEquals('>=', $op);
        $this->assertEquals('"2018-06-14T14:40:00+00:00"', $value);
    }

    public function test_single_value_without_time()
    {
        list($op, $value) = QueryUtil::parseDateFieldFromQuery('2018-06-14');

        $this->assertEquals('>=', $op);
        $this->assertEquals('"2018-06-14T00:00:00+00:00"', $value);
    }

    public function test_single_offset_value()
    {
        list($op, $value) = QueryUtil::parseDateFieldFromQuery('2018-06-14+1day');

        $this->assertEquals('>=', $op);
        $this->assertEquals('"2018-06-15T00:00:00+00:00"', $value);
    }

    public function test_single_time_value()
    {
        $datetime         = new \DateTime();
        list($op, $value) = QueryUtil::parseDateFieldFromQuery('16:00');

        $this->assertEquals('>=', $op);
        $this->assertEquals('"'.$datetime->format('Y-m-d').'T16:00:00+00:00"', $value);
    }

    /**
     * @dataProvider singleValueWithOperatorProvider
     *
     * @param string $operator
     */
    public function test_single_value_with_operator($operator)
    {
        list($op, $value) = QueryUtil::parseDateFieldFromQuery($operator.'2018-06-14');

        $this->assertEquals($operator, $op);
        $this->assertEquals('"2018-06-14T00:00:00+00:00"', $value);
    }

    /**
     * @return array
     */
    public function singleValueWithOperatorProvider()
    {
        return [['>'], ['>='], ['<'], ['<=']];
    }

    /**
     * @dataProvider betweenDelimiterProvider
     *
     * @param string $delimiter
     */
    public function test_between_with_specified_time($delimiter)
    {
        list($op, $value) = QueryUtil::parseDateFieldFromQuery("2018-06-14 14:00{$delimiter}2018-06-14 16:00");

        $this->assertEquals('BETWEEN', $op);
        $this->assertEquals('"2018-06-14T14:00:00+00:00" AND "2018-06-14T16:00:00+00:00"', $value);
    }

    public function test_between_without_time()
    {
        list($op, $value) = QueryUtil::parseDateFieldFromQuery('2018-06-14/2018-06-14');

        $this->assertEquals('BETWEEN', $op);
        $this->assertEquals('"2018-06-14T00:00:00+00:00" AND "2018-06-14T23:59:59+00:00"', $value);
    }

    /**
     * @return array
     */
    public function betweenDelimiterProvider()
    {
        return [['/'], ['--']];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to parse datetime value
     */
    public function test_unknown_delimiter()
    {
        QueryUtil::parseDateFieldFromQuery('2018-06-14 14:00\\2018-06-14 16:00');
    }
}
