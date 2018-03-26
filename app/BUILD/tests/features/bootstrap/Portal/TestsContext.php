<?php

namespace DpBehat\Portal;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use DpBehat\RebootableContextInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Defines application features from the specific context.
 */
class TestsContext extends BasePortalContext implements RebootableContextInterface
{
    private $called;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $em;

    public function rebootContext()
    {
        $this->resetTestsContext();
    }

    public function resetTestsContext()
    {
        $this->called  = false;
        $this->session = $this->get('session');
        $this->em      = $this->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @Given I execute the test suite
     */
    public function iHaveRunTheTestSuite()
    {
    }

    /**
     * @When this scenario is called
     */
    public function thisScenarioIsCalled()
    {
        $this->called = true;
    }

    /**
     * @Then this scenario should be successful
     */
    public function thisScenarioShouldBeSuccessful()
    {
        expect($this->called)->toBe(true);
    }

    /**
     * @Given I injected session into the context
     */
    public function iInjectedSessionIntoTheContext()
    {
    }

    /**
     * @Then I should see that I have the session
     */
    public function iShouldSeeThatIHaveTheSession()
    {
        expect($this->session)->toHaveType('Symfony\Component\HttpFoundation\Session\SessionInterface');
    }

    /**
     * @Then I should be able to load an admin user
     */
    public function iShouldBeAbleToLoadAnAdminUser()
    {
        $person = $this->em->getRepository('DeskPRO:Person')->findOneBy(['can_admin' => true]);

        expect($person)->toHaveType('Application\DeskPRO\Entity\Person');
    }
}
