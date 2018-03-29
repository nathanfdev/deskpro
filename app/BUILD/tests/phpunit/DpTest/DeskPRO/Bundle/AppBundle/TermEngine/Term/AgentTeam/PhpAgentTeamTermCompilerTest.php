<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractPhpTermCompilerTest;

class PhpAgentTeamTermCompilerTest extends AbstractPhpTermCompilerTest
{
    /**
     * @var PhpTicketStatusTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.php_ticket_checker.compiler.agent_team');
    }

    public function testSimpleIsCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(1));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(199));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(4));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(0));
    }

    public function testSimpleIsNOTCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(8));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(0));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(3));
    }

    public function testSimpleIsWithMeCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199, AgentTeamTerm::TEAM_ID_ME],
            ]
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(1));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(2)); // me
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(199));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(4));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(0));
    }

    public function testNotWithMeCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199, AgentTeamTerm::TEAM_ID_ME],
            ],
            TermInterface::OP_NOT
        );

        $php_check = $this->term_compiler->compile($term);

        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(8));
        $this->assertTicketCheck($php_check, true, $this->createTicketProphecy(0));
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(2)); // me
        $this->assertTicketCheck($php_check, false, $this->createTicketProphecy(3));
    }

    protected function createTicketProphecy($id)
    {
        $ticket = $this->prophesize('Application\DeskPRO\Entity\Ticket');
        if (!$id) {
            $id = 0;
        }
        $ticket->getId()->willReturn(5);
        $ticket->getAgentTeamId()->willReturn($id);

        return $ticket;
    }
}
