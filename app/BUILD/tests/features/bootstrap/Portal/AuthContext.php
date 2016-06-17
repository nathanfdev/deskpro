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

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use DpBehat\Data\PeopleContext as PeopleDataContext;
use DpBehat\RebootableContextInterface;
use DpTestSrc\TestBundle\UserDetailsRepo;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AuthContext extends BasePortalContext implements RebootableContextInterface
{
    public static $session;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $me;

    /**
     * @var PeopleDataContext
     */
    private $peopleDataContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->peopleDataContext = $scope->getEnvironment()->getContext('DpBehat\Data\PeopleContext');
    }

    public function rebootContext()
    {
        $this->resetAuthContext();
    }

    public function resetAuthContext()
    {
        $this->me = null;
    }

    /**
     * @When I login with :who credentials
     */
    public function iLoginWithCredentials($who)
    {
        $this->me = $this->getUserDetails()->getWho($who);

        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver', $driver);
        }

        $token = new UsernamePasswordToken($this->me, null, 'portal', $this->me->getRoles());

        $client  = $driver->getClient();
        $session = $client->getContainer()->get('session');
        $session->set('_security_portal', serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @When I am not logged in
     */
    public function iAmNotLoggedIn()
    {
        $this->visitPath('/en/logout-confirmation');
        $page = $this->getSession()->getPage();
        $page->clickLink('');
    }

    /**
     * @When I login with :who credentials from the login page
     */
    public function iLoginWithCredentialsFromTheLoginPage($who)
    {
        $this->getPage('Login')->login(
            $this->getUserDetails()->getEmail($who),
            $this->getUserDetails()->getPass($who)
        );

        $this->me = $this->getUserDetails()->getWho($who);
    }

    /**
     * @When I use bad :who credentials for login
     * @When I use bad credentials for login
     *
     * @param string $who
     */
    public function iLoginWithBadCredentialsFromTheLoginPage($who = '')
    {
        if ($who) {
            $this->getPage('Login')->login(
                $this->getUserDetails()->getEmail($who),
                'wrong_password'
            );
        } else {
            $this->getPage('Login')->login(
                'wrong_email@example.wrong',
                'wrong_password'
            );
        }
    }

    /**
     * @Given I have a verified email :email_address
     */
    public function iHaveAVerifiedEmail($email_address)
    {
        $email    = new \Application\DeskPRO\Entity\PersonEmail();
        $this->me = $this->repository(Person::class)->find($this->me->getId());
        $email->setPerson($this->me);
        $email->setEmail($email_address);
        $email->setIsValidated(true);

        $this->em()->persist($email);
        $this->em()->flush($email);

        $this->em()->refresh($this->me);
    }

    /**
     * @Then I should be authenticated as :who
     */
    public function iShouldBeAuthenticatedAs($who)
    {
        if (!$token = $this->get('security.token_storage')->getToken()) {
            throw new \Exception('no token found');
        }

        if (!$user = $token->getUser()) {
            throw new \Exception('no user in token');
        }

        if (!$user instanceof Person) {
            $user = $this->get('doctrine.orm.default_entity_manager')->getRepository('DeskPRO:Person')->find($user);
        }

        expect($user->getPrimaryEmailAddress())->toBeEqualTo($this->getUserDetails()->getEmail($who));

        $this->me = $this->getUserDetails()->getWho($who);
    }

    /**
     * @Then :email_address should be my primary email address
     */
    public function shouldBeMyPrimaryEmailAddress($email_address)
    {
        $this->me = $this->repository(Person::class)->find($this->me->getId());

        expect($this->me->getPrimaryEmailAddress())->toBe($email_address);
    }

    /**
     * @Given the organization :org exists
     */
    public function theOrganizationExists($org)
    {
        $organization = new Organization();
        $organization->setName($org);
        $this->em()->persist($organization);
        $this->em()->flush();
    }

    /**
     * @Given :who is an organization manager of :org
     */
    public function isAnOrganizationManagerOf($who, $org)
    {
        $person = $this->getUserDetails()->getWho($who);
        if (!$organization = $this->getOrganization($org)) {
            throw new \Exception('cannot find organization "'.$org.'"');
        }

        $person->setOrganization($organization);
        $person->organization_manager = true;
        $this->em()->flush();
    }

    /**
     * @Given I'm authenticated as :role
     *
     * @param string $role
     */
    public function iAmAuthenticatedAs($role)
    {
        $this->peopleDataContext->personByRoleExists($role);
        $this->iLoginWithCredentials($role);
    }

    /**
     * @Given my name is :name
     */
    public function myNameIs($name)
    {
        $this->me->setName($name);
        $this->me = $this->em()->merge($this->me);
        $this->em()->flush($this->me);
    }

    /**
     * @Then my name should be :name
     */
    public function myNameShouldBe($name)
    {
        $this->me = $this->repository(Person::class)->find($this->me->getId());
        expect($this->me->name)->toBe($name);
    }

    /**
     * @param $org
     *
     * @return Organization|null|object
     */
    protected function getOrganization($org)
    {
        return $this->repository(Organization::class)->findOneBy(['name' => $org]);
    }

    /**
     * @return UserDetailsRepo
     */
    private function getUserDetails()
    {
        return $this->get('user_details');
    }
}
