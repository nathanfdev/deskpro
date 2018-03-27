<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency\TicketUrgencyTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketUrgencyTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_urgency');
    }

    public function testCompileIs()
    {
        $term = new TicketUrgencyTerm(
            [
                'num' => [1, 2, 3],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('urgency')->willReturn(2);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('urgency')->willReturn(5);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testCompileIsNot()
    {
        $term = new TicketUrgencyTerm(
            [
                'num' => [1, 2, 3],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('urgency')->willReturn(5);

        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('urgency')->willReturn(2);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    protected function createTicketProphecy()
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(4);

        return $ticket;
    }
}
