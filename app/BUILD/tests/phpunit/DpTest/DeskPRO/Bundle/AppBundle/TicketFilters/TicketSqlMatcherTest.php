<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketDateTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketSlaTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSqlMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\FilterQueryLanguage\Parser;
use DpTest\ApiTestCase;

class TicketSqlMatcherTest extends ApiTestCase
{
    /**
     * @var TicketModel
     */
    private $ticket1;

    /**
     * @var TicketModel
     */
    private $ticket2;

    /**
     * @var AgentContext
     */
    private $agentContext;

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
        $resolver                  = new ValueResolver();
        $this->agentContext        = new AgentContext();
        $this->agentContext->id    = 1;
        $this->agentContext->teams = [1, 2, 3];

        $this->matcherContext = new Context($this->agentContext);

        $this->matcher = new TicketSqlMatcher($resolver, [
            new TicketBasicTermsHandler(),
//            new TicketSlaTermsHandler(),
//            new TicketDateTermsHandler(),
        ], $this->getContainer()->get('database_connection'));
    }

    public function test_id_match()
    {
        $this->assertEquals(
            'SELECT COUNT(*) FROM tickets tickets WHERE tickets.id = :c0_ticketsid',
            $this->sqlForQuery('ticket.id = 1')
        );
    }

    private function sqlForQuery($fql)
    {
        $qb = $this->matcher->getCountQueryBuilder(
            $this->parseFql($fql),
            $this->matcherContext
        );

        return $this->normalizeForCmp($qb->getSQL());
    }

    private function normalizeForCmp($sql)
    {
        $sql = preg_replace('/\s+/', ' ', $sql);

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

        return $parser->parseQuery($fql);
    }
}
