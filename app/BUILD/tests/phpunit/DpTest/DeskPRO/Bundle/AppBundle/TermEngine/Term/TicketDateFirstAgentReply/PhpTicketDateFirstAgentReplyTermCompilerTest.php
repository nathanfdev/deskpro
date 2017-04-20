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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateFirstAgentReply;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateFirstAgentReply\TicketDateFirstAgentReplyTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketDateFirstAgentReplyTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_date_first_agent_reply');
    }

    public function testCompileIs()
    {
        $date  = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $date2 = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $term = new TicketDateFirstAgentReplyTerm(
            [
                'date' => $date,
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_first_agent_reply')->willReturn($date);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_first_agent_reply')->willReturn($date2);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testCompileIsNot()
    {
        $date  = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $date2 = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $term = new TicketDateFirstAgentReplyTerm(
            [
                'date' => $date,
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_first_agent_reply')->willReturn($date);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->offsetGet('date_first_agent_reply')->willReturn($date2);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
    }

    public function testGreaterThan()
    {
        $yesterday = new \DateTime('-1 day', new \DateTimeZone('Europe/London'));
        $today     = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $tomorrow  = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $term = new TicketDateFirstAgentReplyTerm(
            [
                'date' => $today,
            ],
            TermInterface::OP_GT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_first_agent_reply')->willReturn($tomorrow);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_first_agent_reply')->willReturn($yesterday);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testLesserThan()
    {
        $yesterday = new \DateTime('-1 day', new \DateTimeZone('Europe/London'));
        $today     = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $tomorrow  = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $term = new TicketDateFirstAgentReplyTerm(
            [
                'date' => $today,
            ],
            TermInterface::OP_LT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_first_agent_reply')->willReturn($yesterday);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_first_agent_reply')->willReturn($tomorrow);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testInRange()
    {
        $two_days_ago = new \DateTime('-2 day', new \DateTimeZone('Europe/London'));
        $yesterday    = new \DateTime('-1 day', new \DateTimeZone('Europe/London'));
        $today        = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $tomorrow     = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $term = new TicketDateFirstAgentReplyTerm(
            [
                'date'  => $yesterday,
                'date2' => $tomorrow,
            ],
            TermInterface::OP_RANGE
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_first_agent_reply')->willReturn($today);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_first_agent_reply')->willReturn($two_days_ago);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testNotInRange()
    {
        $two_days_ago = new \DateTime('-2 day', new \DateTimeZone('Europe/London'));
        $yesterday    = new \DateTime('-1 day', new \DateTimeZone('Europe/London'));
        $today        = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $tomorrow     = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $term = new TicketDateFirstAgentReplyTerm(
            [
                'date'  => $yesterday,
                'date2' => $tomorrow,
            ],
            TermInterface::OP_NOT_RANGE
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_first_agent_reply')->willReturn($today);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->offsetGet('date_first_agent_reply')->willReturn($two_days_ago);
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
