<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketStatusTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_status');
    }

    public function testCompileIs()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [Ticket::STATUS_AWAITING_AGENT, Ticket::STATUS_RESOLVED, 'hidden.spam'],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(Ticket::STATUS_AWAITING_AGENT));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(Ticket::STATUS_RESOLVED));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy('hidden.spam'));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(Ticket::STATUS_AWAITING_USER));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(Ticket::STATUS_ARCHIVED));
    }

    public function testCompileIsNot()
    {
        $term = new TicketStatusTerm(
            [
                'status' => [Ticket::STATUS_ARCHIVED, Ticket::STATUS_RESOLVED, 'hidden.spam'],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(Ticket::STATUS_ARCHIVED));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(Ticket::STATUS_RESOLVED));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy('hidden.spam'));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(Ticket::STATUS_AWAITING_AGENT));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(Ticket::STATUS_AWAITING_USER));
    }

    protected function createTicketProphecy($status)
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        if (!$status) {
            $status = null;
        }
        $ticket->getStatusCode()->willReturn($status);
        $ticket->getId()->willReturn(5);

        return $ticket;
    }
}
