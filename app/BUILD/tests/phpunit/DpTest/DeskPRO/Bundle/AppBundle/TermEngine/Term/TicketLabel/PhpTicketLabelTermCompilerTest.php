<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel\TicketLabelTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpTicketLabelTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.ticket_label');
    }

    public function testCompileHas()
    {
        $ticket = $this->createTicketProphecy();

        $term = new TicketLabelTerm(
            [
                'label' => 'blue',
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket->findLabelByString('blue')->willReturn(true);
        $this->assertTicketCheck(
            $php_check,
            true,
            $ticket
        );
        $ticket->findLabelByString('blue')->willReturn(null);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
    }

    public function testCompileHasNot()
    {
        $ticket = $this->createTicketProphecy();

        $term = new TicketLabelTerm(
            [
                'label' => 'blue',
            ],
            TermInterface::OP_NOT_HAS
        );

        $php_check = $this->term_compiler->compile($term);

        $ticket->findLabelByString('blue')->willReturn(true);
        $this->assertTicketCheck(
            $php_check,
            false,
            $ticket
        );
        $ticket->findLabelByString('blue')->willReturn(null);
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
