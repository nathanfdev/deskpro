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
