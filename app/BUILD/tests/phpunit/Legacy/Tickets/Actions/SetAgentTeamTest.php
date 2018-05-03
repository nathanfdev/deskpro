<?php

namespace DpUnitTests\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\SetAgentTeam;
use Application\DeskPRO\Tickets\ExecutorContext;
use Doctrine\Common\Collections\ArrayCollection;
use DpTest\DeskProTestCase;
use DpTestSrc\TestBundle\Mock\ContainerMock;
use Mockery as m;

class SetAgentTeamTest extends DeskProTestCase
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private function getMockContainer()
    {
        if ($this->container) {
            return $this->container;
        }
        $this->container = ContainerMock::create()->withAgentData()->get();

        return $this->container;
    }

    public function testSetAgent()
    {
        $ticket             = new Ticket();
        $ticket->agent_team = $this->getMockContainer()->getAgentData()->getTeam(1);
        $exec               = new ExecutorContext();

        $action = new SetAgentTeam(['agent_team_id' => 55]);
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\AgentTeam', $ticket->agent_team);
        $this->assertEquals(55, $ticket->agent_team->id);
    }

    public function testSetUnassigned()
    {
        $ticket        = new Ticket();
        $ticket->agent = $this->getMockContainer()->getAgentData()->get(1);
        $exec          = new ExecutorContext();

        $action = new SetAgentTeam(['agent_team_id' => 0]);
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->agent_team);
    }

    public function testSetSelf()
    {
        $ticket             = new Ticket();
        $ticket->agent_team = $this->getMockContainer()->getAgentData()->getTeam(1);

        $exec = new ExecutorContext();

        $agent           = m::mock('Application\\DeskPRO\\Entity\\Person')->makePartial();
        $agent->id       = 500;
        $agent->is_agent = true;
        $agent->teams    = new ArrayCollection([
            $this->getMockContainer()->getAgentData()->getTeam(5),
        ]);

        $exec->setPersonContext($agent);

        $action = new SetAgentTeam(['agent_team_id' => -1]);
        $action->setContainer($this->getMockContainer());

        $action->applyAction($ticket, $exec);

        $this->assertInstanceOf('Application\\DeskPRO\\Entity\\AgentTeam', $ticket->agent_team);
        $this->assertEquals(5, $ticket->agent_team->id);
    }

    public function testNoop()
    {
        $ticket             = new Ticket();
        $ticket->agent_team = $this->getMockContainer()->getAgentData()->getTeam(1);

        $exec = new ExecutorContext();

        $action = new SetAgentTeam(['agent_team_id' => 1]);
        $action->setContainer($this->container);

        $this->assertTrue($action->isNoop($ticket, $exec));
    }

    public function testInvalid()
    {
        $ticket = new Ticket();
        $exec   = new ExecutorContext();

        $action = new SetAgentTeam(['agent_team_id' => 200]);
        $action->setContainer($this->getMockContainer());
        $action->applyAction($ticket, $exec);

        $this->assertNull($ticket->agent_team);
    }
}
