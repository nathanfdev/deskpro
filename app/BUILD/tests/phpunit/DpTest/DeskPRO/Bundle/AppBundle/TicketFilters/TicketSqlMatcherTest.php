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

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSqlMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\FilterQueryLanguage\Parser;
use DpTestSrc\TestBundle\Mock\Dbal\ConnectionMock;

class TicketSqlMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Agent
     */
    private $agent;

    /**
     * @var MatcherContext
     */
    private $matcherContext;

    /**
     * @var TicketSqlMatcher
     */
    private $matcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $resolver           = new ValueResolver();
        $this->agent        = new Agent();
        $this->agent->id    = 1;
        $this->agent->teams = [1, 2, 3];

        $this->matcherContext = new Context($this->agent);

        $this->matcher = new TicketSqlMatcher($resolver, [
            new TicketBasicTermsHandler(),
        ], ConnectionMock::create(), TicketSqlMatcher::ACTIVE);
    }

    public function test_id_match()
    {
        $this->assertEqualQuery(
            'ticket.id = 1',
            'SELECT COUNT(*) FROM tickets_search_active tickets WHERE tickets.id = :c0',
            ['c0' => 1]
        );
    }

    public function test_builtin_assigned_to_me()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.agent = $me',
            'SELECT COUNT(*) FROM tickets_search_active tickets WHERE tickets.status = :c0 AND tickets.agent_id = :c1',
            ['c0' => 'awaiting_agent', 'c1' => 1]
        );
    }

    public function test_builtin_follow()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.followers HAS $me',
            'SELECT
               COUNT(*) FROM tickets_search_active tickets
               LEFT JOIN tickets_participants c1_part ON c1_part.ticket_id = tickets.id
               WHERE tickets.status = :c0 AND c1_part.person_id IN (:c1)',
            ['c0' => 'awaiting_agent', 'c1' => [1]]
        );
    }

    public function test_builtin_assigned_to_team()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.agent_team IN $my_teams',
            'SELECT COUNT(*) FROM tickets_search_active tickets WHERE tickets.status = :c0 AND tickets.agent_team_id IN (:c1)',
            ['c0' => 'awaiting_agent', 'c1' => [1, 2, 3]]
        );
    }

    public function test_builtin_unassigned()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.agent IS EMPTY',
            'SELECT COUNT(*) FROM tickets_search_active tickets WHERE tickets.status = :c0 AND tickets.agent_id IS NULL',
            ['c0' => 'awaiting_agent']
        );
    }

    public function test_builtin_awaiting_Agent()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT COUNT(*) FROM tickets_search_active tickets WHERE tickets.status = :c0',
            ['c0' => 'awaiting_agent']
        );
    }

    /**
     * @param string $fql
     * @param string $expectedSql
     * @param array  $expectedParams
     */
    private function assertEqualQuery($fql, $expectedSql, $expectedParams = [])
    {
        $qb = $this->queryFromFql($fql);

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
     * @param string $fql
     *
     * @return \DeskPRO\Bundle\AppBundle\TicketFilters\SqlBuilder\SqlBuilder
     */
    private function queryFromFql($fql)
    {
        $qb = $this->matcher->getCountQueryBuilder(
            $this->parseFql($fql),
            $this->matcherContext
        );

        return $qb;
    }

    /**
     * @param string $sql
     *
     * @return string
     */
    private function normalizeForCmp($sql)
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
    private function parseFql($fql)
    {
        $parser = new Parser();
        $q      = $parser->parseQuery($fql);

        return $q;
    }
}
