<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Api;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketManager;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DpBehat\BaseContext;

/**
 * Class AgentsContext.
 */
class AgentsContext extends BaseContext
{
    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @var Person
     */
    private $lastAgent;

    /**
     * @var Ticket[]
     */
    private $lastAgentTickets;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment       = $scope->getEnvironment();
        $this->restContext = $environment->getContext('DpBehat\Api\RestContext');
    }

    /**
     * @Given I've just created a new agent with name :arg1
     * @Given I create a new agent with name :arg1
     */
    public function iVeJustCreatedANewAgentWithName($name)
    {
        $person = new Person();
        $person->setName($name);
        $person->setIsAgent(true);
        $this->persistAndFlush($person);
        $this->lastAgent = $person;
    }

    /**
     * @Given I create an agent with :num ticket(s)
     */
    public function iCreateANewAgentWithNumTickets($num)
    {
        $this->iVeJustCreatedANewAgentWithName($name = 'Agent '.uniqid());

        $this->lastAgentTickets = [];
        for ($i = 1; $i <= $num; ++$i) {
            $ticket = new Ticket();
            $ticket->disableAutoTicketProcess();
            $ticket->setSubject("$name ticket #$i");
            $ticket->setAgent($this->lastAgent);
            $this->saveTicket($ticket);
            $this->lastAgentTickets[$i] = $ticket;
        }
    }

    /**
     * @Then agent's ticket #:num should be unassigned
     */
    public function agentTicketNumShouldBeUnassigned($num)
    {
        \PHPUnit_Framework_Assert::assertArrayHasKey($num, $this->lastAgentTickets);
        $ticket = $this->repository(Ticket::class)->find($this->lastAgentTickets[$num]->getId());
        \PHPUnit_Framework_Assert::assertNotNull($ticket, "Ticket $num not found");
        \PHPUnit_Framework_Assert::assertNull($ticket->getAgent());
    }

    /**
     * @When I send a :method request to the just created agent resource
     * @When I send a :method request to the last created agent resource
     */
    public function iSendADeleteRequestToTheJustCreatedAgentResource($method)
    {
        return $this->restContext->iSendARequestTo($method, '/api/v2/agents/'.$this->lastAgent->getId());
    }

    /**
     * @When I send a :method request to the last created agent resource via people endpoint
     */
    public function iSendADeleteRequestToTheJustCreatedAgentResourceViaPeopleEndpoint($method)
    {
        return $this->restContext->iSendARequestTo($method, '/api/v2/people/'.$this->lastAgent->getId());
    }

    /**
     * @When I send a DELETE request to the just created agent permissions resource
     */
    public function iSendADeleteRequestToTheJustCreatedAgentPermissionResource()
    {
        return $this->restContext->iSendARequestTo(
            'DELETE', '/api/v2/agents/'.$this->lastAgent->getId().'/agent_permissions'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function saveTicket($ticket)
    {
        /* @var TicketManager $tm */
        $tm      = $this->container()->getTicketManager();
        $action  = $ticket->getId() ? 'update' : 'new';
        $context = $tm->createAgentExecutorContext(null, $action, 'api');
        $tm->saveTicket($ticket, $context);

        return $ticket;
    }
}
