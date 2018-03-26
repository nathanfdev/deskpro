<?php

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
        $term = new AgentTerm(['agent_ids' => [1]]);

        $dbal_query = $this->compiler->compile($term);

        $this->assertEquals(
            ['ids_0' => [1]],
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
        $term = new CompositeTerm([], TermInterface::OP_OR);
        $term->addTerm(new AgentTerm(['agent_ids' => [1]]));
        $term->addTerm(new TicketStatusTerm(['status' => [Ticket::STATUS_RESOLVED]]));

        $dbal_query = $this->compiler->compile($term);

        $this->assertEquals(
            [
                'ids_0'    => [1],
                'status_0' => [Ticket::STATUS_RESOLVED],
            ],
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
        $composite_term1 = new CompositeTerm([], TermInterface::OP_OR);
        $composite_term1->addTerm(new AgentTerm(['agent_ids' => [1]]));
        $composite_term1->addTerm(new TicketStatusTerm(['status' => [Ticket::STATUS_RESOLVED]]));

        $composite_term2 = new CompositeTerm([], TermInterface::OP_AND);
        $composite_term2->addTerm(new DepartmentTerm(['department_ids' => [1, 2]]));
        $composite_term2->addTerm(new PersonEmailTerm(['email' => 'chris.tickner@deskpro.com']));

        $term = new CompositeTerm([], TermInterface::OP_OR);
        $term->addTerm($composite_term1);
        $term->addTerm(new AgentTeamTerm(['agent_team_ids' => ['me']]));
        $term->addTerm($composite_term2);

        $dbal_query = $this->compiler->compile($term);

        $this->assertEquals(
            [
                'ids_0'    => [1],
                'status_0' => [Ticket::STATUS_RESOLVED],
                'ids_1'    => [new TermEngineExpression('agent.getTeamIds()')],
                'ids_2'    => [1, 2],
                'email_0'  => ['chris.tickner@deskpro.com'],
            ],
            $dbal_query->getParameters()
        );
        $this->assertEquals(
            '((ticket.agent_id IN (:ids_0)) OR (ticket.status IN (:status_0))) OR (ticket.agent_team_id IN (:ids_1)) OR ((ticket.department_id IN (:ids_2)) AND (ticket.person_id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email_0)) OR  EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email_0)) AND ticket.id = tp.ticket_id
                )))',
            $dbal_query->generateWhereString()
        );
        $this->assertEquals(
            'SELECT * FROM tickets ticket WHERE (((ticket.agent_id IN (:ids_0)) OR (ticket.status IN (:status_0))) OR (ticket.agent_team_id IN (:ids_1)) OR ((ticket.department_id IN (:ids_2)) AND (ticket.person_id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email_0)) OR  EXISTS(
                  SELECT * FROM
                    tickets_participants tp
                        JOIN
                    people p ON tp.person_id = p.id
                  WHERE
                    p.is_agent = 0 AND p.id  IN(SELECT pe.person_id FROM people_emails pe WHERE pe.email IN(:email_0)) AND ticket.id = tp.ticket_id
                ))))',
            (string) $dbal_query
        );
    }
}
