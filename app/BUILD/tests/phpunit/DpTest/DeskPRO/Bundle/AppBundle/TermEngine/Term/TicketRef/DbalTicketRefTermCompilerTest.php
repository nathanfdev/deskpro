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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef\DbalTicketRefTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef\TicketRefTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketRefTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketRefTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_ref');
    }

    public function testCompileIs()
    {
        $term = new TicketRefTerm(
            [
                'ref' => [
                    'XXX-111-XXX',
                    'ZZZ-222-ZZZ',
                ],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'string0' => 'XXX-111-XXX',
                'string1' => 'ZZZ-222-ZZZ',
            ]
        );

        $this->assertWhere($query_part, 'ticket.ref = :string0 OR ticket.ref = :string1');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsNot()
    {
        $term = new TicketRefTerm(
            [
                'ref' => [
                    'XXX-111-XXX',
                    'ZZZ-222-ZZZ',
                ],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'string0' => 'XXX-111-XXX',
                'string1' => 'ZZZ-222-ZZZ',
            ]
        );

        $this->assertWhere($query_part, 'ticket.ref != :string0 AND ticket.ref != :string1');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
