<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateResolved;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateResolved\TicketDateResolvedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketDateResolvedTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_date_resolved');
    }

    public function testCompileIs()
    {
        $date  = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $date2 = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $term = new TicketDateResolvedTerm(
            [
                'date' => $date,
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_resolved')->willReturn($date);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_resolved')->willReturn($date2);
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

        $term = new TicketDateResolvedTerm(
            [
                'date' => $date,
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_resolved')->willReturn($date);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->offsetGet('date_resolved')->willReturn($date2);
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

        $term = new TicketDateResolvedTerm(
            [
                'date' => $today,
            ],
            TermInterface::OP_GT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_resolved')->willReturn($tomorrow);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_resolved')->willReturn($yesterday);
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

        $term = new TicketDateResolvedTerm(
            [
                'date' => $today,
            ],
            TermInterface::OP_LT
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_resolved')->willReturn($yesterday);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_resolved')->willReturn($tomorrow);
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

        $term = new TicketDateResolvedTerm(
            [
                'date'  => $yesterday,
                'date2' => $tomorrow,
            ],
            TermInterface::OP_RANGE
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_resolved')->willReturn($today);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->offsetGet('date_resolved')->willReturn($two_days_ago);
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

        $term = new TicketDateResolvedTerm(
            [
                'date'  => $yesterday,
                'date2' => $tomorrow,
            ],
            TermInterface::OP_NOT_RANGE
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket = $this->createTicketProphecy();

        $ticket->offsetGet('date_resolved')->willReturn($today);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->offsetGet('date_resolved')->willReturn($two_days_ago);
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
