<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
