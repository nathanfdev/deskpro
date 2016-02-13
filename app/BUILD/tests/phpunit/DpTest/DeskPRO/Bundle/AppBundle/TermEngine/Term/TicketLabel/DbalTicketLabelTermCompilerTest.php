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
            array('label' => 'blue')
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'labels_tickets.label = :input0');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => 'blue',
            )
        );
        $this->assertJoins(
            $query_part,
            array(
                'labels_tickets' => array(
                    'table' => 'labels_tickets',
                    'on'    => 'ticket.id = labels_tickets.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }

    public function testCompileIsNOT()
    {
        $term = new TicketLabelTerm(
            array('label' => 'blue'),
            TermInterface::OP_NOT_HAS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'labels_tickets.label != :input0');
        $this->assertParameters(
            $query_part,
            array(
                'input0' => 'blue',
            )
        );
        $this->assertJoins(
            $query_part,
            array(
                'labels_tickets' => array(
                    'table' => 'labels_tickets',
                    'on'    => 'ticket.id = labels_tickets.ticket_id',
                    'type'  => DbalQuery::JOIN_LEFT,
                ),
            )
        );
    }
}
