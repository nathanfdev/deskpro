<?php

namespace DpBehat;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManager;
use DpBehat\TestBundle\DataSetManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Defines application features from the specific context.
 */
class TestsContext implements Context, SnippetAcceptingContext
{
    private $called;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var DataSetManager
     */
    private $dataset_manager;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct(SessionInterface $session, DataSetManager $dataset_manager, EntityManager $em)
    {
        $this->called = false;
        $this->session = $session;
        $this->dataset_manager = $dataset_manager;
        $this->em = $em;
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
     * @Given I have access to dataset_manager
     */
    public function iHaveAccessToDbsetManager()
    {
        expect($this->dataset_manager)->toNotBeNull();
    }

    /**
     * @When I install the :set data set
     */
    public function iInstallTheFreshDataSet($set)
    {
        $this->dataset_manager->install($set);
    }

    /**
     * @Then I should be able to load an admin user
     */
    public function iShouldBeAbleToLoadAnAdminUser()
    {
        $person = $this->em->getRepository('DeskPRO:Person')->findOneBy(array('can_admin' => true));

        expect($person)->toHaveType('Application\DeskPRO\Entity\Person');
    }
}
