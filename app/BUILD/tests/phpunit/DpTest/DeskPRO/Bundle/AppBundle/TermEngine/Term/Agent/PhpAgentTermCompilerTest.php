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
namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\PhpAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpAgentTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpAgentTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.agent');
    }

    public function testSimpleIS()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15),
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(10));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(null)); // unassigned
    }

    public function testSimpleNOT()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15),
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(10));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(null)); // unassigned
    }

    public function testISCompileWithMe()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 15, AgentTerm::ID_ME),
            )
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2)); // me
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(1));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(11));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(null)); // unassigned
    }

    public function testIsNOTWithMe()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15, AgentTerm::ID_ME),
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(2)); // me
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(1));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(11));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(null)); // unassigned
    }

    public function testISWithUnassigned()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15, 0),
            ),
            TermInterface::OP_IS
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(null)); // unassigned
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(10));
    }

    public function testWithNOTUnassigned()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(0),
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(null)); // unassigned
    }

    public function testISWithOnlyUnassigned()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(0),
            ),
            TermInterface::OP_IS
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(null)); // unassigned
    }

    public function testISAllIdTypes()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 21, 15, AgentTerm::ID_ME, 0),
            ),
            TermInterface::OP_IS
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(211));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(null)); // unassigned
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(1));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(21));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2)); // me
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(15));
    }

    public function testNOTAllIdTypes()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 21, 15, AgentTerm::ID_ME, 0),
            ),
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(211));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(null)); // unassigned
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(1));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(21));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(2)); // me
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(15));
    }

    protected function createTicketProphecy($agent_id)
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        if (!$agent_id) {
            $agent_id = 0;
        }
        $ticket->getId()->willReturn(5);
        $ticket->getAgentId()->willReturn($agent_id);

        return $ticket;
    }
}
