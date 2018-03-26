<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\DbalTicketFlaggedTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\TicketFlaggedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

/**
 * Class DbalTicketFlaggedTermCompilerTest.
 */
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

        $qp = $this->term_compiler->compile($term);

        $this->assertWhere($qp, 'tickets_flagged.color IN(:color)');
        $this->assertParameters(
            $qp,
            [
                'color'    => ['blue'],
                'agent_id' => new TermEngineExpression('agent.getId()'),
            ]
        );
        $this->assertJoins(
            $qp,
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

        $qp = $this->term_compiler->compile($term);

        $this->assertWhere($qp, 'tickets_flagged.color NOT IN(:color) OR tickets_flagged.color IS NULL');
        $this->assertParameters(
            $qp,
            [
                'color'    => ['blue'],
                'agent_id' => new TermEngineExpression('agent.getId()'),
            ]
        );
        $this->assertJoins(
            $qp,
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
