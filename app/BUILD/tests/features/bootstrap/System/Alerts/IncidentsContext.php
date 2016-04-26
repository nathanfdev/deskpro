<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
