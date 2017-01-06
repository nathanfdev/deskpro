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

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\TicketFlaggedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketFlaggedTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketFlaggedTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_flagged');
    }

    public function testCompileIs()
    {
        $term = new TicketFlaggedTerm(
            ['flag' => 'blue']
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'tickets_flagged.color = :color');
        $this->assertParameters(
            $query_part,
            [
                'color'    => 'blue',
                'agent_id' => new TermEngineExpression('agent.getId()'),
            ]
        );
        $this->assertJoins(
            $query_part,
            [
                'tickets_flagged' => [
                    'table' => 'tickets_flagged',
                    'on'    => 'ticket.id = tickets_flagged.ticket_id AND tickets_flagged.person_id = :agent_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileIsNOT()
    {
        $term = new TicketFlaggedTerm(
            ['flag' => 'blue'],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'tickets_flagged.color != :color');
        $this->assertParameters(
            $query_part,
            [
                'color'    => 'blue',
                'agent_id' => new TermEngineExpression('agent.getId()'),
            ]
        );
        $this->assertJoins(
            $query_part,
            [
                'tickets_flagged' => [
                    'table' => 'tickets_flagged',
                    'on'    => 'ticket.id = tickets_flagged.ticket_id AND tickets_flagged.person_id = :agent_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }
}
