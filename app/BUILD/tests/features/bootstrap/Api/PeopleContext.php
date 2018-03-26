<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Api;

use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use DpBehat\BaseContext;

/**
 * Class PeopleContext.
 */
class PeopleContext extends BaseContext
{
    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @var Person
     */
    private $lastPerson;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->restContext = $environment->getContext('DpBehat\Api\RestContext');
    }

    /**
     * @Given there are no registered users
     */
    public function thereAreNoRegisteredUsersInTheDb()
    {
        /** @var Person[] $people */
        $people = $this->repository(Person::class)->findAll();
        foreach ($people as $person) {
            if (!$person->can_admin) {
                $this->em()->remove($person);
            }
        }
        $this->em()->flush();
    }

    /**
     * @Given I've just created a new person with name :arg1 and primary email :arg2
     * @Given I have a person with name :arg1 and primary email :arg2
     * @When I create a new person with name :arg1 and primary email :arg2
     * @Given :arg1 has just created an account with primary email :arg2
     */
    public function iVeJustCreatedANewPersonWithNameAndPrimaryEmail($name, $email)
    {
        $person = new Person();
        $person->setName($name);
        $personEmail = new PersonEmail();
        $personEmail->setEmail($email);
        $person->setPrimaryEmail($personEmail);
        $this->persistAndFlush($person);
        $this->lastPerson = $person;
    }

    /**
     * @Given I create a person with name :arg1 and emails :arg2
     * @Given I have a person with name :arg1 and emails :arg2
     */
    public function iCreateAPersonWithNameAndEmails($name, $emails)
    {
        $person = new Person();
        $person->setName($name);

        $emails = explode(',', $emails);
        foreach ($emails as $email) {
            $personEmail = new PersonEmail();
            $personEmail->setEmail(trim($email));
            $person->addEmail($personEmail);
        }
        $this->persistAndFlush($person);
        $this->lastPerson = $person;
    }

    /**
     * @Given I've just created a new person with name :arg1
     */
    public function iVeJustCreatedANewPersonWithName($name)
    {
        $person = new Person();
        $person->setName($name);
        $this->persistAndFlush($person);
        $this->lastPerson = $person;
    }

    /**
     * @Given I've just created a new email account :email
     *
     * @param $email
     */
    public function iVeJustCreatedEmailAccount($email)
    {
        $emailAccount          = new EmailAccount('tickets');
        $emailAccount->address = $email;

        $this->persistAndFlush($emailAccount);
    }

    /**
     * @When I send a PUT request to the just created person resource:
     * @When I send a PUT request to the person resource:
     * @When he sends a PUT request to modify his personal data:
     * @When she sends a PUT request to modify her personal data:
     */
    public function iSendAPutRequestToTheJustCreatedPersonResource(PyStringNode $data)
    {
        return $this->restContext->iSendARequestToWithBody(
            'PUT', '/api/v2/people/'.$this->lastPerson->getId(), $data
        );
    }

    /**
     * @When I send a PUT request to the last created person permissions resource:
     */
    public function iSendAPostRequestToTheJustCreatedPersonPermissionsResource(PyStringNode $data)
    {
        return $this->restContext->iSendARequestToWithBody(
            'PUT', '/api/v2/people/'.$this->lastPerson->getId().'/permissions', $data
        );
    }

    /**
     * @When I send a :method request to the last created person resource via agents endpoint
     */
    public function iSendADeleteRequestToTheJustCreatedAgentResourceViaPeopleEndpoint($method)
    {
        return $this->restContext->iSendARequestTo($method, '/api/v2/agents/'.$this->lastPerson->getId());
    }

    /**
     * @When I retrieve the person( data)
     */
    public function iRetrieveThePerson()
    {
        return $this->restContext->iSendARequestTo('GET', '/api/v2/people/'.$this->lastPerson->getId());
    }

    /**
     * @When I delete just created person
     */
    public function iDeleteJustCreatedPerson()
    {
        $this->em()->remove($this->lastPerson);
        $this->em()->flush();
    }
}
