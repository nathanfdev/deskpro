<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

/**
 * Class CompilerToDpqlTest.
 */
class CompilerToDpqlTest extends AbstractCompilerTest
{
    public function test_union()
    {
        $dpql = <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION
    (SELECT tickets.id FROM tickets)
) as tickets
DPQL;

        $statement = $this->compiler->compile($dpql, []);
        $parts     = $statement->getDpqlParts();

        $this->assertEquals('tickets.id', $parts['SELECT']);
        $this->assertEquals('(SELECT tickets.id
FROM tickets)
UNION DISTINCT
(SELECT tickets.id
FROM tickets) AS \'tickets\'', $parts['FROM']);
    }

    public function test_distinct_union()
    {
        $dpql = <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION DISTINCT
    (SELECT tickets.id FROM tickets)
) as tickets
DPQL;

        $statement = $this->compiler->compile($dpql, []);
        $parts     = $statement->getDpqlParts();

        $this->assertEquals('tickets.id', $parts['SELECT']);
        $this->assertEquals('(SELECT tickets.id
FROM tickets)
UNION DISTINCT
(SELECT tickets.id
FROM tickets) AS \'tickets\'', $parts['FROM']);
    }

    public function test_all_union()
    {
        $dpql = <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION ALL
    (SELECT tickets.id FROM tickets)
) as tickets
DPQL;

        $statement = $this->compiler->compile($dpql, []);
        $parts     = $statement->getDpqlParts();

        $this->assertEquals('tickets.id', $parts['SELECT']);
        $this->assertEquals('(SELECT tickets.id
FROM tickets)
UNION ALL
(SELECT tickets.id
FROM tickets) AS \'tickets\'', $parts['FROM']);
    }

    public function test_in_subquery()
    {
        $dpql = <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE tickets.ref IN (
    SELECT tickets.ref
    FROM tickets
    WHERE tickets.ref = 'AAAA-%'
)
DPQL;
        $statement = $this->compiler->compile($dpql, []);
        $parts     = $statement->getDpqlParts();

        $this->assertEquals('tickets.ref', $parts['SELECT']);
        $this->assertEquals('tickets', $parts['FROM']);
        $this->assertEquals('tickets.ref IN (SELECT tickets.ref
FROM tickets
WHERE tickets.ref = \'AAAA-%\')', $parts['WHERE']);
    }
}
