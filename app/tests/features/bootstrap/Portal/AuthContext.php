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

namespace DpBehat\Portal;

use Application\DeskPRO\Entity\Person;
use DpTestSrc\TestBundle\UserDetailsRepo;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AuthContext extends BasePortalContext
{
    /**
     * @var UserDetailsRepo
     */
    private $user_details;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    public function __construct(UserDetailsRepo $user_details, TokenStorage $token_storage)
    {
        $this->user_details  = $user_details;
        $this->token_storage = $token_storage;
    }

    /**
     * @When I login using the sidebar with :who credentials
     */
    public function iLoginUsingTheSidebarWithCredentials($who)
    {
        $this->getPage('Home')->sidebarLogin(
            $this->user_details->getEmail($who),
            $this->user_details->getPass($who)
        );
    }

    /**
     * @When I login with :who credentials
     */
    public function iLoginWithCredentials($who)
    {
        $this->getSession()->visit('/login');

        /** @var \Behat\Mink\Element\DocumentElement $page */
        $page = $this->getSession()->getPage();
        $page->fillField('username', $this->user_details->getEmail($who));
        $page->fillField('password', $this->user_details->getPass($who));
        $page->pressButton('Login');
    }

    /**
     * @Then I should be authenticated as :who
     */
    public function iShouldBeAuthenticatedAs($who)
    {
        if (!$token = $this->getContainer()->get('security.token_storage')->getToken()) {
            throw new \Exception('no token found');
        }

        if (!$user = $token->getUser()) {
            throw new \Exception('no user in token');
        }

        if (!$user instanceof Person) {
            $user = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('DeskPRO:Person')->find($user);
        }

        print $this->user_details->getEmail($who);

        expect($user->getPrimaryEmailAddress())->toBeEqualTo($this->user_details->getEmail($who));
    }

    /**
     * @Given I am authenticated as :who
     */
    public function iAmAuthenticatedAsUser($who)
    {
        $this->iLoginWithCredentials($who);
    }
}
