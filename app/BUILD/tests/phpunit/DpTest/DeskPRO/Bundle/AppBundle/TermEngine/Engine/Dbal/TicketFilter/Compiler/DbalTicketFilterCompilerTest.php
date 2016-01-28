<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler\DbalTicketFilterCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
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
            (string) $dbal_query
        );
    }

    public function testCompositeTermCompile()
    {
        $term = new CompositeTerm(array(), TermInterface::OP_OR);
        $term->addTerm(new AgentTerm(array('agent_ids' => array(1))));
        $term->addTerm(new TicketStatusTerm(array('status' => array(Ticket::STATUS_RESOLVED))));

        $dbal_query = $this->compiler->compile($term);

        $this->assertEquals(
            array(
                'ids_0'    => array(1),
                'status_0' => array(Ticket::STATUS_RESOLVED),
            ),
            $dbal_query->getParameters()
        );
        $this->assertEquals(
            '(ticket.agent_id IN (:ids_0)) OR (ticket.status IN (:status_0))',
            $dbal_query->generateWhereString()
        );
        $this->assertEquals(
            'SELECT * FROM tickets ticket WHERE ((ticket.agent_id IN (:ids_0)) OR (ticket.status IN (:status_0)))',
            (string) $dbal_query
        );
    }

    public function testComplexEmbeddedCompositeTermCompile()
    {
        $composite_term1 = new CompositeTerm(array(), TermInterface::OP_OR);
        $composite_term1->addTerm(new AgentTerm(array('agent_ids' => array(1))));
        $composite_term1->addTerm(new TicketStatusTerm(array('status' => array(Ticket::STATUS_RESOLVED))));

        $composite_term2 = new CompositeTerm(array(), TermInterface::OP_AND);
        $composite_term2->addTerm(new DepartmentTerm(array('department_ids' => array(1, 2))));
        $composite_term2->addTerm(new PersonEmailTerm(array('email' => 'chris.tickner@deskpro.com')));

        $term = new CompositeTerm(array(), TermInterface::OP_OR);
        $term->addTerm($composite_term1);
        $term->addTerm(new AgentTeamTerm(array('agent_team_ids' => array('me'))));
        $term->addTerm($composite_term2);

        $dbal_query = $this->compiler->compile($term);

        $this->assertEquals(
            array(
                'ids_0'    => array(1),
                'status_0' => array(Ticket::STATUS_RESOLVED),
                'ids_1'    => array(new TermEngineExpression('agent.getTeamIds()')),
                'ids_2'    => array(1, 2),
                'email_0'  => 'chris.tickner@deskpro.com',
            ),
            $dbal_query->getParameters()
        );
        $this->assertEquals(
            '((ticket.agent_id IN (:ids_0)) OR (ticket.status IN (:status_0))) OR (ticket.agent_team_id IN (:ids_1)) OR ((ticket.department_id IN (:ids_2)) AND (people_emails.email = :email_0))',
            $dbal_query->generateWhereString()
        );
        $this->assertEquals(
            'SELECT * FROM tickets ticket LEFT JOIN people_emails ON (ticket.person_id = people_emails.person_id) WHERE (((ticket.agent_id IN (:ids_0)) OR (ticket.status IN (:status_0))) OR (ticket.agent_team_id IN (:ids_1)) OR ((ticket.department_id IN (:ids_2)) AND (people_emails.email = :email_0)))',
            (string) $dbal_query
        );
    }
}
