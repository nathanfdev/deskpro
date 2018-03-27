<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketParticipantTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant\TicketParticipantTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketParticipantTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketParticipantTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_participant');
    }

    public function testCompileIs()
    {
        $term = new TicketParticipantTerm(
            [
                'person_ids' => [4, 9],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{participants}.person_id IN (:person_ids)');
        $this->assertParameters(
            $query_part,
            [
                'person_ids' => [4, 9],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'participants' => [
                    'table' => 'tickets_participants',
                    'on'    => '{participants}.ticket_id = ticket.id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileIsNOT()
    {
        $term = new TicketParticipantTerm(
            [
                'person_ids' => [14],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{participants}.person_id NOT IN (:person_ids)');
        $this->assertParameters(
            $query_part,
            [
                'person_ids' => [14],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'participants' => [
                    'table' => 'tickets_participants',
                    'on'    => '{participants}.ticket_id = ticket.id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileWithME()
    {
        $term = new TicketParticipantTerm(
            [
                'person_ids' => [10, TicketParticipantTerm::ID_ME],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{participants}.person_id IN (:person_ids)');
        $this->assertParameters(
            $query_part,
            [
                'person_ids' => [10, new TermEngineExpression('agent.getId()')],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'participants' => [
                    'table' => 'tickets_participants',
                    'on'    => '{participants}.ticket_id = ticket.id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }
}
