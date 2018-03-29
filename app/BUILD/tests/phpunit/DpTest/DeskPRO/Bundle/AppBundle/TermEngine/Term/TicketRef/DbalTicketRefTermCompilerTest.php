<?php

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
