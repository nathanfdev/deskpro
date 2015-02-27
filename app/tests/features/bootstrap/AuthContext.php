<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace DpBehat;

use Application\AuthBundle\Security\DpFormLoginToken;
use DeskPRO\Bundle\AppBundle\Brand\BrandStack;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Language as LanguageRepo;
use Application\DeskPRO\Languages\LangPackInfo;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Tests\Driver\BrowserKitConfig;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Doctrine\ORM\EntityManager;
use DpBehat\TestBundle\UserDetailsRepo;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
        $this->user_details = $user_details;
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
        $this->getPage('Login')->login(
            $this->user_details->getEmail($who),
            $this->user_details->getPass($who)
        );
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
