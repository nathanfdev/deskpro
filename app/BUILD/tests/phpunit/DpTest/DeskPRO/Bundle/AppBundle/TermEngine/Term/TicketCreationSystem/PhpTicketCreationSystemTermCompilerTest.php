<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem\PhpTicketCreationSystemTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem\TicketCreationSystemTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketCreationSystemTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketCreationSystemTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_creation_system');
    }

    public function testCompileIs()
    {
        $term = new TicketCreationSystemTerm(
            [
                'creation_system' => 'web.person',
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('creation_system')->willReturn('web.person');
        $this->assertTicketCheck($php_check, true, $ticket);

        $ticket->offsetGet('creation_system')->willReturn('web.person.portal');
        $this->assertTicketCheck($php_check, true, $ticket);

        $ticket->offsetGet('creation_system')->willReturn('gateway.person');
        $this->assertTicketCheck($php_check, false, $ticket);
    }

    public function testCompileIsNot()
    {
        $term = new TicketCreationSystemTerm(
            [
                'creation_system' => 'web.person',
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('creation_system')->willReturn('web.person');
        $this->assertTicketCheck($php_check, false, $ticket);

        $ticket->offsetGet('creation_system')->willReturn('web.person');
        $this->assertTicketCheck($php_check, false, $ticket);

        $ticket->offsetGet('creation_system')->willReturn('gateway.person');
        $this->assertTicketCheck($php_check, true, $ticket);
    }

    protected function createTicketProphecy()
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(4);

        return $ticket;
    }
}
