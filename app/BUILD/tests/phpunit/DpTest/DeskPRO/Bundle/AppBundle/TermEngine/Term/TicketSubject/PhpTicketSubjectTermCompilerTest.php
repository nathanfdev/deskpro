<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
