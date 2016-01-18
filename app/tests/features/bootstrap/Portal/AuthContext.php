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

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DpBehat\RebootableContextInterface;
use DpTestSrc\TestBundle\UserDetailsRepo;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AuthContext extends BasePortalContext implements RebootableContextInterface
{
    /**
     * @var UserDetailsRepo
     */
    private $user_details;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    /** @var  \Application\DeskPRO\Entity\Person */
    private $me;

    public function rebootContext()
    {
        $this->resetAuthContext();
    }

    public function resetAuthContext()
    {
        $this->user_details  = $this->getKernel()->getContainer()->get('user_details');
        $this->token_storage = $this->getKernel()->getContainer()->get('security.token_storage');
        $this->me            = null;
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

        $this->me = $this->user_details->getWho($who);
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

        $this->me = $this->user_details->getWho($who);
    }

    /**
     * @Given I have a verified email :email_address
     */
    public function iHaveAVerifiedEmail($email_address)
    {
        $email    = new \Application\DeskPRO\Entity\PersonEmail();
        $this->me = $this->em()->getRepository(Person::class)->find($this->me->getId());
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
        if (!$token = $this->getContainer()->get('security.token_storage')->getToken()) {
            throw new \Exception('no token found');
        }

        if (!$user = $token->getUser()) {
            throw new \Exception('no user in token');
        }

        if (!$user instanceof Person) {
            $user = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository('DeskPRO:Person')->find($user);
        }

        expect($user->getPrimaryEmailAddress())->toBeEqualTo($this->user_details->getEmail($who));

        $this->me = $this->user_details->getWho($who);
    }

    /**
     * @Then :email_address should be my primary email address
     */
    public function shouldBeMyPrimaryEmailAddress($email_address)
    {
        $this->me = $this->em()->getRepository(Person::class)->find($this->me->getId());

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
        $person = $this->user_details->getWho($who);
        if (!$organization = $this->getOrganization($org)) {
            throw new \Exception('cannot find organization "'.$org.'"');
        }

        $person->setOrganization($organization);
        $person->organization_manager = true;
        $this->em()->flush();
    }

    /**
     * @Given I am authenticated as :who
     */
    public function iAmAuthenticatedAsUser($who)
    {
        $this->iLoginWithCredentials($who);
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
        $this->me = $this->em()->getRepository(Person::class)->find($this->me->getId());
        expect($this->me->name)->toBe($name);
    }

    /**
     * @param $org
     *
     * @return Organization|null|object
     */
    protected function getOrganization($org)
    {
        return $this->em()->getRepository(Organization::class)->findOneBy(['name' => $org]);
    }
}
