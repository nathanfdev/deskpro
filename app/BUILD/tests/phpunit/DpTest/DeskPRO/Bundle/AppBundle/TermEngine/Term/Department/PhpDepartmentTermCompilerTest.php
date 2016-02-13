<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
            array(
                'department_ids' => array(1, 2, 15),
            )
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
            array(
                'department_ids' => array(1, 2, 15),
            ),
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
