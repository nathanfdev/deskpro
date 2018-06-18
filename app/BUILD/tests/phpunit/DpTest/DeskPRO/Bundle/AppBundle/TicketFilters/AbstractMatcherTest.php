<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Component\FilterQueryLanguage\Parser;

/**
 * Class AbstractMatcherTest.
 */
abstract class AbstractMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $fql
     *
     * @return \DeskPRO\Component\FilterQueryLanguage\Query\Query
     */
    protected function parseFql($fql)
    {
        $parser = new Parser();
        $q      = $parser->parseQuery($fql);

        return $q;
    }
}
