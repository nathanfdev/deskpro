<?php

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
