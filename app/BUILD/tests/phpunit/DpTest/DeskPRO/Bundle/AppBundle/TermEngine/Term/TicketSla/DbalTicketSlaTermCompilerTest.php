<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSla;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSla\TicketSlaTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketSlaTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketSlaTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_sla');
    }

    public function testCompileHasSla()
    {
        $term = new TicketSlaTerm(
            ['sla' => [1, 2]]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{ticket_slas}.sla_id IN (:input0)');
        $this->assertParameters(
            $query_part,
            [
                'input0' => [1, 2],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'ticket_slas' => [
                    'table' => 'ticket_slas',
                    'on'    => 'ticket.id = {ticket_slas}.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileHasStatus()
    {
        $term = new TicketSlaTerm(
            ['status' => ['ok', 'warning']]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{ticket_slas}.sla_status IN (:input0)');
        $this->assertParameters(
            $query_part,
            [
                'input0' => ['ok', 'warning'],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'ticket_slas' => [
                    'table' => 'ticket_slas',
                    'on'    => 'ticket.id = {ticket_slas}.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileHasSlaAndStatus()
    {
        $term = new TicketSlaTerm(
            [
                'sla'    => [1, 2],
                'status' => ['ok', 'warning'],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{ticket_slas}.sla_id IN (:input0) AND {ticket_slas}.sla_status IN (:input1)');
        $this->assertParameters(
            $query_part,
            [
                'input0' => [1, 2],
                'input1' => ['ok', 'warning'],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'ticket_slas' => [
                    'table' => 'ticket_slas',
                    'on'    => 'ticket.id = {ticket_slas}.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileHasNotSla()
    {
        $term = new TicketSlaTerm(
            ['sla' => 1],
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{ticket_slas}.sla_id != :input0');
        $this->assertParameters(
            $query_part,
            [
                'input0' => 1,
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'ticket_slas' => [
                    'table' => 'ticket_slas',
                    'on'    => 'ticket.id = {ticket_slas}.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileHasNotStatus()
    {
        $term = new TicketSlaTerm(
            ['status' => ['ok', 'warning']],
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{ticket_slas}.sla_status NOT IN (:input0)');
        $this->assertParameters(
            $query_part,
            [
                'input0' => ['ok', 'warning'],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'ticket_slas' => [
                    'table' => 'ticket_slas',
                    'on'    => 'ticket.id = {ticket_slas}.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function testCompileNotHasSlaAndStatus()
    {
        $term = new TicketSlaTerm(
            [
                'sla'    => [1, 2],
                'status' => ['ok', 'warning'],
            ],
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, '{ticket_slas}.sla_id NOT IN (:input0) AND {ticket_slas}.sla_status NOT IN (:input1)');
        $this->assertParameters(
            $query_part,
            [
                'input0' => [1, 2],
                'input1' => ['ok', 'warning'],
            ]
        );
        $this->assertUniqueJoins(
            $query_part,
            [
                'ticket_slas' => [
                    'table' => 'ticket_slas',
                    'on'    => 'ticket.id = {ticket_slas}.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }
}
