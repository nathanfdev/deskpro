<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketParticipant\TicketParticipantTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketParticipantTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_participant');
    }

    public function testCompileIs()
    {
        $term = new TicketParticipantTerm(
            [
                'person_ids' => [4, 9],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->hasAnyParticipantId([4, 9])->willReturn(true);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->hasAnyParticipantId([4, 9])->willReturn(false);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testCompileIsNot()
    {
        $term = new TicketParticipantTerm(
            [
                'person_ids' => [4, 9],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->hasAnyParticipantId([4, 9])->willReturn(true);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->hasAnyParticipantId([4, 9])->willReturn(false);
        $this->assertTicketCheck(
            $php_check,
            true,
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
