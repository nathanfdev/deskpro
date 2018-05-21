<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\CustomFieldSet;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\CustomFieldsTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSearchParams;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSqlMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\FilterQueryLanguage\Parser;
use DpTestSrc\TestBundle\Mock\Dbal\ConnectionMock;

abstract class BaseTicketSqlMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueResolver
     */
    protected $valueResolver;

    /**
     * @var Agent
     */
    protected $agent;

    /**
     * @var Context
     */
    protected $matcherContext;

    /**
     * @var TicketSqlMatcher
     */
    protected $matcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->setUpValueResolver();
        $this->setUpAgent();
        $this->setUpMatcherContext();
        $this->setUpMatcher();
    }

    protected function setUpValueResolver()
    {
        $this->valueResolver = new ValueResolver();
    }

    protected function setUpAgent()
    {
        $this->agent        = new Agent();
        $this->agent->id    = 1;
        $this->agent->teams = [1, 2, 3];
    }

    protected function setUpMatcherContext()
    {
        $this->matcherContext = new Context($this->agent);
    }

    protected function setUpMatcher()
    {
        $this->matcher = new TicketSqlMatcher($this->valueResolver, [
            new TicketBasicTermsHandler(),
            new CustomFieldsTermsHandler(new CustomFieldSet([
                new CustomField(1, 'choice', ['my_choice']),
                new CustomField(2, 'choice', ['my_other_choice']),
                new CustomField(3, 'text', ['my_text']),
                new CustomField(4, 'date', ['my_date']),
                new CustomField(5, 'toggle', ['my_toggle']),
            ])),
        ], ConnectionMock::create(), TicketSqlMatcher::ACTIVE);

        $this->matcher->disableContextPermissions();
    }

    /**
     * @param string             $fql
     * @param string             $expectedSql
     * @param array              $expectedParams
     * @param TicketSearchParams $params
     */
    public function assertEqualQuery($fql, $expectedSql, $expectedParams = [], TicketSearchParams $params = null)
    {
        $qb = $this->queryFromFql($fql, $params);

        // the sql compiler gives placeholders descriptive names like :c12_fieldname
        // this can help debugging queries manually, but is a bit of a pain
        // when comparing them here.

        // noramlizing them here just strips off the descriptive bit and just
        // leaves the positioning id like :c12

        $expectedSql = $this->normalizeForCmp($expectedSql);
        $realSql     = $this->normalizeForCmp($qb->getSQL());

        $realParams = [];
        foreach ($qb->getParameters() as $name => $val) {
            $name              = preg_replace('#_.*?$#', '', $name);
            $realParams[$name] = $val;
        }

        $this->assertEquals($expectedSql, $realSql);
        $this->assertEquals($expectedParams, $realParams);
    }

    /**
     * @param string             $fql
     * @param TicketSearchParams $params
     *
     * @return \DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder
     */
    public function queryFromFql($fql, TicketSearchParams $params = null)
    {
        $qb = $this->matcher->getCountQueryBuilder(
            $this->parseFql($fql),
            $this->matcherContext,
            $params
        );

        return $qb;
    }

    /**
     * @param string             $fql
     * @param string             $expectedSql
     * @param array              $expectedParams
     * @param TicketSearchParams $params
     */
    public function assertEqualIdQuery($fql, $expectedSql, $expectedParams = [], TicketSearchParams $params = null)
    {
        $qb = $this->idsQueryFromFql($fql, $params);

        $expectedSql = $this->normalizeForCmp($expectedSql);
        $realSql     = $this->normalizeForCmp($qb->getSQL());

        $realParams = [];
        foreach ($qb->getParameters() as $name => $val) {
            $name              = preg_replace('#_.*?$#', '', $name);
            $realParams[$name] = $val;
        }

        $this->assertEquals($expectedSql, $realSql);
        $this->assertEquals($expectedParams, $realParams);
    }

    /**
     * @param string             $fql
     * @param TicketSearchParams $params
     *
     * @return \DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder
     */
    protected function idsQueryFromFql($fql, TicketSearchParams $params = null)
    {
        $qb = $this->matcher->getIdsQueryBuilder(
            $this->parseFql($fql),
            $this->matcherContext,
            $params
        );

        return $qb;
    }

    /**
     * @param string $sql
     *
     * @return string
     */
    protected function normalizeForCmp($sql)
    {
        $sql = str_replace(['(', ')'], [' ( ', ' ) '], $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = preg_replace('#(:c\d+)_.*?\b#', '$1', $sql);

        return trim($sql);
    }

    /**
     * @param $fql
     *
     * @return array
     */
    protected function parseFql($fql)
    {
        $parser = new Parser();
        $q      = $parser->parseQuery($fql);

        return $q;
    }
}
