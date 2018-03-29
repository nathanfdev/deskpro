<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef\PhpTicketRefTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef\TicketRefTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketRefTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketRefTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_ref');
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

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('ref')->willReturn('XXX-111-XXX');
        $this->assertTicketCheck($php_check, true, $ticket);

        $ticket->offsetGet('ref')->willReturn('ZZZ-222-ZZZ');
        $this->assertTicketCheck($php_check, true, $ticket);

        $ticket->offsetGet('ref')->willReturn('YYY-333-YYY');
        $this->assertTicketCheck($php_check, false, $ticket);
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

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('ref')->willReturn('XXX-111-XXX');
        $this->assertTicketCheck($php_check, false, $ticket);

        $ticket->offsetGet('ref')->willReturn('ZZZ-222-ZZZ');
        $this->assertTicketCheck($php_check, false, $ticket);

        $ticket->offsetGet('ref')->willReturn('YYY-333-YYY');
        $this->assertTicketCheck($php_check, true, $ticket);
    }

    protected function createTicketProphecy()
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(4);

        return $ticket;
    }
}
