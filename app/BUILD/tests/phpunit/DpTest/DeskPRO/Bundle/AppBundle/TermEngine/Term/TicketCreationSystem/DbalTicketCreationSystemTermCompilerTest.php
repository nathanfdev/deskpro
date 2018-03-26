<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem\DbalTicketCreationSystemTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem\TicketCreationSystemTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketCreationSystemTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketCreationSystemTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_creation_system');
    }

    public function testCompileIs()
    {
        $term = new TicketCreationSystemTerm(
            [
                'creation_system' => 'web.person',
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'string0' => 'web.person%',
            ]
        );

        $this->assertWhere($query_part, 'ticket.creation_system LIKE :string0');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileIsNot()
    {
        $term = new TicketCreationSystemTerm(
            [
                'creation_system' => 'web.person',
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertParameters(
            $query_part,
            [
                'string0' => 'web.person%',
            ]
        );

        $this->assertWhere($query_part, 'ticket.creation_system NOT LIKE :string0');
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
