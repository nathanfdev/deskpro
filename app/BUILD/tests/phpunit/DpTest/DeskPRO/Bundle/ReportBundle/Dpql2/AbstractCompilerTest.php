<?php

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;
use DpTest\ApiTestCase;

/**
 * Class AbstractCompilerTest.
 */
abstract class AbstractCompilerTest extends ApiTestCase
{
    /**
     * @var DpqlCompiler
     */
    protected $compiler;

    /**
     * @var DpqlContext
     */
    protected $context;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $connection = $this->getEntityManager()->getConnection();
        $connection->executeQuery('DELETE FROM object_aliases');
        $connection->executeQuery('DELETE FROM custom_def_ticket');
        $connection->executeQuery('DELETE FROM custom_def_people');
        $connection->executeQuery('DELETE FROM custom_def_organizations');

        $this->compiler = $this->getContainer()->get('dpql.compiler');
        $this->context  = null;
    }

    /**
     * @param string $dpql
     * @param string $sql
     */
    protected function assertDpqlQuery($dpql, $sql)
    {
        $statement   = $this->compiler->compile($dpql, [], $this->context);
        $exceptedSql = preg_replace('/\s+/', ' ', $sql);
        $actualSql   = preg_replace('/\s+/', ' ', $statement->toSql());

        $this->assertEquals($exceptedSql, $actualSql);
    }
}
