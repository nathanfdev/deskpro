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
        $person = $this->em->getRepository('DeskPRO:Person')->findOneBy(array('can_admin' => true));

        expect($person)->toHaveType('Application\DeskPRO\Entity\Person');
    }
}
