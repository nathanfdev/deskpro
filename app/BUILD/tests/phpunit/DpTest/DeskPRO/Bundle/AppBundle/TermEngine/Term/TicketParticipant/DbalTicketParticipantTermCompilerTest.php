<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
