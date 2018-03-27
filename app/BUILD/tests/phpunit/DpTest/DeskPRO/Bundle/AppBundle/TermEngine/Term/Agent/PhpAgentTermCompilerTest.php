<?php

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
            [
                'agent_ids' => [1, 2, 15],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(10));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(null)); // unassigned
    }

    public function testSimpleNOT()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [1, 2, 15],
            ],
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
            [
                'agent_ids' => [1, 15, AgentTerm::ID_ME],
            ]
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
            [
                'agent_ids' => [1, 2, 15, AgentTerm::ID_ME],
            ],
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
            [
                'agent_ids' => [1, 2, 15, 0],
            ],
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
            [
                'agent_ids' => [0],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(null)); // unassigned
    }

    public function testISWithOnlyUnassigned()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [0],
            ],
            TermInterface::OP_IS
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(2));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(null)); // unassigned
    }

    public function testISAllIdTypes()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [1, 21, 15, AgentTerm::ID_ME, 0],
            ],
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
            [
                'agent_ids' => [1, 21, 15, AgentTerm::ID_ME, 0],
            ],
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
