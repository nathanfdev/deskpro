<?php

/**
 * DeskPRO.
 */

namespace DpBehat\System\Alerts;

use Application\DeskPRO\Entity\EmailAccount;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\IncomingEmailFailureIncident;
use Doctrine\ORM\EntityManager;
use DpBehat\Api\RestContext;
use DpBehat\BaseContext;
use Zend\Mail\Exception\RuntimeException;

/**
 * Class IncidentsContext.
 */
class IncidentsContext extends BaseContext
{
    /**
     * @var RestContext
     */
    private $rest_context;

    /**
     * @var AbstractIncident[]
     */
    private $incidents = [];

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment        = $scope->getEnvironment();
        $this->rest_context = $environment->getContext('DpBehat\Api\RestContext');
        $this->sysEm()->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->sysEm()->clear();
    }

    /**
     * @Given there are no incidents
     */
    public function thereAreNoIncidentsInTheDb()
    {
        $incidents = $this->sysEm()->getRepository(AbstractIncident::class)->findAll();
        foreach ($incidents as $incident) {
            $this->sysEm()->remove($incident);
        }
        $this->sysEm()->flush();
    }

    /**
     * @Given I add a :arg1 incident #:arg2
     */
    public function iAddAnIncident($status, $number)
    {
        $incident = new IncomingEmailFailureIncident();
        $incident->setRaised(true);
        switch ($status) {
            case 'continuing':
                $incident->setResolved(false);
                break;
            case 'resolved':
                $incident->setResolved(true);
                break;
            default:
                throw new \Exception("Unknown '$status' incident status");
        }
        $email          = new EmailAccount(EmailAccount::TYPE_TICKETS);
        $email->id      = uniqid();
        $email->address = $email->id.'@email.lo';
        $this->sysEm()->persist($event1 = new IncomingEmailFailureEvent($email, new RuntimeException()));
        $this->sysEm()->persist($event2 = new IncomingEmailFailureEvent($email, new RuntimeException()));
        $incident->addEvent($event1);
        $incident->addEvent($event2);
        $this->sysEm()->persist($incident);
        $this->sysEm()->flush();

        $this->incidents[$number] = $incident;
    }

    /**
     * @When I send a request to modify the incident #:arg1 with the following data:
     */
    public function iModifyTheIncident($number, PyStringNode $string)
    {
        return $this->rest_context->iSendARequestToWithBody(
            'PUT', '/api/v2/system/incidents/'.$this->incidents[$number]->getId(), $string);
    }

    /**
     * @When I send a request to retrieve the incident #:arg1
     */
    public function iRetrieveTheIncident($number)
    {
        return $this->rest_context->iSendARequestTo(
            'GET', '/api/v2/system/incidents/'.$this->incidents[$number]->getId());
    }

    /**
     * @When I send a request to delete the incident #:arg1
     */
    public function iDeleteTheIncident($number)
    {
        return $this->rest_context->iSendARequestTo(
            'DELETE', '/api/v2/system/incidents/'.$this->incidents[$number]->getId());
    }

    /**
     * @return EntityManager
     */
    protected function sysEm()
    {
        return $this->get('doctrine.orm.system_entity_manager');
    }
}
