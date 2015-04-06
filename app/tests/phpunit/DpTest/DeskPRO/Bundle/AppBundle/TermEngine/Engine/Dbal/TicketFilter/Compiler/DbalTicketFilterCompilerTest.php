<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler;


use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler\DbalTicketFilterCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\ApiTestCase;

class DbalTicketFilterCompilerTest extends ApiTestCase
{
    /**
     * @var DbalTicketFilterCompiler
     */
    protected $compiler;

    public function __construct()
    {
        $this->compiler = $this->get('term_engine.dbal_ticket_filters.compiler');
    }

    public function testBasicTermCompile()
    {
        $term = new AgentTerm(array('agent_ids' => array(1)));

        $dbal_query = $this->compiler->compile($term);

        $this->assertEquals(
            array('ids_0' => array(1)),
            $dbal_query->getParameters()
        );
        $this->assertEquals(
            'ticket.agent_id IN (:ids_0)',
            $dbal_query->generateWhereString()
        );
        $this->assertEquals(
            'SELECT * FROM tickets ticket WHERE (ticket.agent_id IN (:ids_0))',
            (string)$dbal_query
        );
    }

    public function testCompositeTermCompile()
    {
        $term = new CompositeTerm(array(), TermInterface::OP_AND);
        $term->addTerm(new AgentTerm(array('agent_ids' => array(1))));
        $term->addTerm(new TicketStatusTerm(array('status' => array(Ticket::STATUS_RESOLVED))));

        $dbal_query = $this->compiler->compile($term);

        $this->assertEquals(
            array(
                'ids_0' => array(1),
                'status_0' => array(Ticket::STATUS_RESOLVED)
            ),
            $dbal_query->getParameters()
        );
        $this->assertEquals(
            '(ticket.agent_id IN (:ids_0)) AND (ticket.status IN (:status_0))',
            $dbal_query->generateWhereString()
        );
        $this->assertEquals(
            'SELECT * FROM tickets ticket WHERE ((ticket.agent_id IN (:ids_0)) AND (ticket.status IN (:status_0)))',
            (string)$dbal_query
        );
    }
}
