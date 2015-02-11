<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class TestsContext implements Context, SnippetAcceptingContext
{
    private $called;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->called = false;
    }

    /**
     * @Given I have run the test suite
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

}
