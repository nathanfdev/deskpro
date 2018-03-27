<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject\PhpTicketSubjectTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject\TicketSubjectTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketSubjectTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketSubjectTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_subject');
    }

    public function testCompileIs()
    {
        $term = new TicketSubjectTerm(
            [
                'subject' => ['this is the subject'],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('subject')->willReturn('this is the subject');
        $this->assertTicketCheck($php_check, true, $ticket);

        $ticket->offsetGet('subject')->willReturn('this is not the subject');
        $this->assertTicketCheck($php_check, false, $ticket);
    }

    public function testCompileHas()
    {
        $term = new TicketSubjectTerm(
            [
                'subject'          => ['foobar -'],
                'wildcard_postfix' => true,
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('subject')->willReturn('foobar - Something happened');
        $this->assertTicketCheck($php_check, true, $ticket);

        $ticket->offsetGet('subject')->willReturn('this is not the subject');
        $this->assertTicketCheck($php_check, false, $ticket);

        //// Opposite

        $term = new TicketSubjectTerm(
            [
                'subject'         => ['- foobar'],
                'wildcard_prefix' => true,
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('subject')->willReturn('Something happened - foobar');
        $this->assertTicketCheck($php_check, true, $ticket);

        $ticket->offsetGet('subject')->willReturn('this is not the subject');
        $this->assertTicketCheck($php_check, false, $ticket);
    }

    public function testCompileIsNot()
    {
        $term = new TicketSubjectTerm(
            [
                'subject' => ['This is the subject'],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('subject')->willReturn('This is the subject');
        $this->assertTicketCheck($php_check, false, $ticket);

        $ticket->offsetGet('subject')->willReturn('This is not the subject');
        $this->assertTicketCheck($php_check, true, $ticket);
    }

    public function testCompileHasNot()
    {
        $term = new TicketSubjectTerm(
            [
                'subject'          => ['foobar -'],
                'wildcard_postfix' => true,
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('subject')->willReturn('foobar - Something happened');
        $this->assertTicketCheck($php_check, false, $ticket);

        $ticket->offsetGet('subject')->willReturn('this is not the subject');
        $this->assertTicketCheck($php_check, true, $ticket);

        //// Opposite

        $term = new TicketSubjectTerm(
            [
                'subject'         => ['- foobar'],
                'wildcard_prefix' => true,
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('subject')->willReturn('Something happened - foobar');
        $this->assertTicketCheck($php_check, false, $ticket);

        $ticket->offsetGet('subject')->willReturn('this is not the subject');
        $this->assertTicketCheck($php_check, true, $ticket);
    }

    protected function createTicketProphecy()
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        $ticket->getId()->willReturn(4);

        return $ticket;
    }
}
