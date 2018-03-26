<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel\TicketLabelTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketLabelTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketLabelTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_label');
    }

    public function testCompileIs()
    {
        $term = new TicketLabelTerm(
            ['label' => 'blue']
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'labels_tickets.label = :input0');
        $this->assertParameters(
            $query_part,
            [
                'input0' => 'blue',
            ]
        );
        $this->assertJoins(
            $query_part,
            [
                'labels_tickets' => [
                    'table' => 'labels_tickets',
                    'on'    => 'ticket.id = labels_tickets.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileIsNOT()
    {
        $term = new TicketLabelTerm(
            ['label' => 'blue'],
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'labels_tickets.label != :input0');
        $this->assertParameters(
            $query_part,
            [
                'input0' => 'blue',
            ]
        );
        $this->assertJoins(
            $query_part,
            [
                'labels_tickets' => [
                    'table' => 'labels_tickets',
                    'on'    => 'ticket.id = labels_tickets.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }
}
