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
            array('sla' => array(1, 2))
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket_slas.sla_id IN :input0');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => array(1, 2),
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'ticket_slas' => array(
                    'table' => 'ticket_slas',
                    'on'    => 'tickets.id = ticket_slas.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testCompileHasStatus()
    {
        $term = new TicketSlaTerm(
            array('status' => array('ok', 'warning'))
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket_slas.sla_status IN :input0');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => array('ok', 'warning'),
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'ticket_slas' => array(
                    'table' => 'ticket_slas',
                    'on'    => 'tickets.id = ticket_slas.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testCompileHasSlaAndStatus()
    {
        $term = new TicketSlaTerm(
            array(
                'sla'    => array(1, 2),
                'status' => array('ok', 'warning'),
            )
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket_slas.sla_id IN :input0 AND ticket_slas.sla_status IN :input1');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => array(1, 2),
                'input1' => array('ok', 'warning'),
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'ticket_slas' => array(
                    'table' => 'ticket_slas',
                    'on'    => 'tickets.id = ticket_slas.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testCompileHasNotSla()
    {
        $term = new TicketSlaTerm(
            array('sla' => 1),
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket_slas.sla_id != :input0');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => 1,
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'ticket_slas' => array(
                    'table' => 'ticket_slas',
                    'on'    => 'tickets.id = ticket_slas.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testCompileHasNotStatus()
    {
        $term = new TicketSlaTerm(
            array('status' => array('ok', 'warning')),
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket_slas.sla_status NOT IN :input0');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => array('ok', 'warning'),
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'ticket_slas' => array(
                    'table' => 'ticket_slas',
                    'on'    => 'tickets.id = ticket_slas.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testCompileNotHasSlaAndStatus()
    {
        $term = new TicketSlaTerm(
            array(
                'sla'    => array(1, 2),
                'status' => array('ok', 'warning'),
            ),
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket_slas.sla_id NOT IN :input0 AND ticket_slas.sla_status NOT IN :input1');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => array(1, 2),
                'input1' => array('ok', 'warning'),
            )
        );
        $this->assertUniqueJoins(
            $query_part,
            array(
                'ticket_slas' => array(
                    'table' => 'ticket_slas',
                    'on'    => 'tickets.id = ticket_slas.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }
}
