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
            array(
                'agent_team_ids' => array(1, 3, 199),
            )
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
            array(
                'agent_team_ids' => array(1, 3, 199),
            ),
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
            array(
                'agent_team_ids' => array(1, 3, 199, AgentTeamTerm::TEAM_ID_ME),
            )
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
            array(
                'agent_team_ids' => array(1, 3, 199, AgentTeamTerm::TEAM_ID_ME),
            ),
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
