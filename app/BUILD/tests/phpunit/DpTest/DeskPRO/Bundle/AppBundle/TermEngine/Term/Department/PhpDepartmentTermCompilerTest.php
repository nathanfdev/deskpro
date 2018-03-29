<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Department;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpDepartmentTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.department');
    }

    public function testCompileIs()
    {
        $term = new DepartmentTerm(
            [
                'department_ids' => [1, 2, 15],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(1));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(15));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(7));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(13));
    }

    public function testCompileIsNot()
    {
        $term = new DepartmentTerm(
            [
                'department_ids' => [1, 2, 15],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(7));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(13));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(15));
    }

    protected function createTicketProphecy($dep)
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        if (!$dep) {
            $dep = 0;
        }
        $ticket->getId()->willReturn(5);
        $ticket->getDepartmentId()->willReturn($dep);

        return $ticket;
    }
}
