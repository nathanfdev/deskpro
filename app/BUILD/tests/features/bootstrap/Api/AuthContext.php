<?php

namespace DpBehat\Api;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\TmpData;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use DeskPRO\Bundle\AppBundle\Entity\OAuthAccessToken;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;
use DpBehat\Data\PeopleContext as PeopleDataContext;
use DpTestSrc\TestBundle\UserDetailsRepo;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Defines application features from the specific context.
 */
class AuthContext extends BaseContext
{
    /**
     * @var RestContext
     */
    private $restContext;

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
        $environment = $scope->getEnvironment();

        $this->restContext       = $environment->getContext('DpBehat\Api\RestContext');
        $this->peopleDataContext = $environment->getContext('DpBehat\Data\PeopleContext');
    }

    /**
     * @Given I'm authenticated as person with email :email
     *
     * @param string $email
     */
    public function iAmAuthenticatedAsPersonWithEmail($email)
    {
        // Log in ------------------------------------------------------------------------------------------------------
        $person = $this->peopleDataContext->findPersonByEmail($email);
        $this->authenticateAs($person);

        self::initOm();
    }

    /**
     * @Given I'm authenticated as person with email :email with super key
     *
     * @param string $email
     */
    public function iAmAuthenticatedAsPersonSuperWithEmail($email)
    {
        // Log in ------------------------------------------------------------------------------------------------------
        $person = $this->peopleDataContext->findPersonByEmail($email);
        $this->authenticateAs($person, true);

        self::initOm();
    }

    /**
     * @Given I'm authenticated as :role
     *
     * @param string $role
     */
    public function iAmAuthenticatedAs($role)
    {
        // Log in ------------------------------------------------------------------------------------------------------
        $person = $this->peopleDataContext->personByRoleExists($role);
        DataContext::setReference($role, $person);
        DataContext::setReference('me', $person);

        $this->authenticateAs($person);

        self::initOm();
    }

    /**
     * @Given I'm authenticated as :role and my apiKey has super flag
     *
     * @param string $role
     */
    public function iAmAuthenticatedWithSuperKeyAs($role)
    {
        // Log in ------------------------------------------------------------------------------------------------------
        $person = $this->peopleDataContext->personByRoleExists($role);
        DataContext::setReference($role, $person);
        DataContext::setReference('me', $person);

        $this->authenticateAs($person, true);

        self::initOm();
    }

    /**
     * @Given I'm authenticated via session as :role
     *
     * @param string $role
     *
     * @throws UnsupportedDriverActionException
     */
    public function iAmAuthenticatedViaSessionAs($role)
    {
        // Log in ------------------------------------------------------------------------------------------------------
        $person = $this->peopleDataContext->personByRoleExists($role);
        DataContext::setReference($role, $person);
        DataContext::setReference('me', $person);

        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver', $driver);
        }

        session_start();
        $_SESSION['_sf2_attributes']['auth_person_id'] = $person->getId();

        $data = session_encode();
        session_destroy();

        $session = new Session();
        $session->setPerson($person);
        $session->setData($data);

        $this->em()->persist($session);
        $this->em()->flush();

        $client = $driver->getClient();
        $cookie = new Cookie('dpsid-agent', $session->getSessionCode());
        $client->getCookieJar()->set($cookie);

        self::initOm();
    }

    /**
     * @Given a valid api token with the code :token for :who and referenced as :ref exists
     */
    public function aValidApiTokenExistsWithTheCodeAndIdForAgent($token, $who, $ref)
    {
        $apiToken        = new ApiToken();
        $apiToken->token = $token;
        $person          = DataContext::hasReference($who)
            ? DataContext::getReference($who)
            : $this->getUserDetails()->getWho($who);
        $apiToken->person = $person;
        $apiToken->scope  = ApiToken::SCOPE_CLIENT;

        $this->persistAndFlush($apiToken);
        DataContext::setReference($ref, $apiToken);
    }

    /**
     * @Given a valid api token for oauth client :client with the code :token for :who and referenced as :ref exists
     */
    public function aValidApiTokenForOauthClientExistsWithTheCodeAndIdForAgent($client, $token, $who, $ref)
    {
        if (!DataContext::hasReference($client)) {
            throw new \Exception("OAuth client ref #$client does not exist");
        }

        $client = DataContext::getReference($client);
        $person = DataContext::hasReference($who)
            ? DataContext::getReference($who)
            : $this->getUserDetails()->getWho($who);
        $oauthToken = new OAuthAccessToken();
        $oauthToken->setToken($token);
        $oauthToken->setClient($client);
        $oauthToken->setUser($person);
        $oauthToken->setScope(ApiToken::SCOPE_SESSION);

        $this->persistAndFlush($oauthToken);
        DataContext::setReference($ref, $oauthToken->getTokenEntity());
    }

    /**
     * @Given a valid api key exists with the code :code for :who
     */
    public function aValidApiKeyExistsWithTheCodeForUser($code, $who)
    {
        $person = $this->peopleDataContext->personByRoleExists($who);
        $this->ensureApiKey($person, $code);
    }

    /**
     * @Given I remove a flag :flag from ApiKey
     */
    public function removeFlagFromApiKey($flag)
    {
        $key   = DataContext::getReference('apiKey');
        $flags = array_filter($key->flags, function ($value) use ($flag) {
            return $value !== $flag;
        });
        $key->flags = $flags;
        $this->persistAndFlush($key);
    }

    /**
     * @Given the agent session auth :session_id is valid for :who and referenced as :ref
     */
    public function theAgentSessionIsValidForPerson($session_id, $who, $ref)
    {
        $user =
            DataContext::hasReference($who)
                ? DataContext::getReference($who, false)
                : $this->getUserDetails()->getWho($who);

        session_start();
        $_SESSION['_sf2_attributes'] = ['auth_person_id' => $user->getId()];
        $data                        = session_encode();
        unset($_SESSION['_sf2_attributes']);
        session_destroy();

        $session = new Session();
        $session->setAuth($session_id);
        $session->setData($data);
        $session->setPerson($user);

        $this->persistAndFlush($session);

        DataContext::setReference($ref, $session);
    }

    /**
     * @Then I should have an authenticated token with the role :role
     */
    public function iShouldHaveAnAuthenticatedTokenWithTheRole($role)
    {
        $token = $this->get('security.token_storage')->getToken();

        expect($token)->toNotBe(null);
        expect($token->isAuthenticated())->toBe(true);

        $has_role = false;
        foreach ($token->getRoles() as $the_role) {
            if ($the_role->getRole() == $role) {
                $has_role = true;
            }
        }

        expect($has_role)->toBe(true);
    }

    /**
     * @Given my request is authenticated
     *
     * @deprecated
     */
    public function myRequestIsAuthenticated()
    {
        $this->myRequestIsAuthenticatedTo('admin');
    }

    /**
     * @Given my request is authenticated to :who
     *
     * @deprecated
     *
     * @param string $who
     *
     * @throws \Exception
     */
    public function myRequestIsAuthenticatedTo($who)
    {
        $person = $this->peopleDataContext->personByRoleExists($who);
        $this->authenticateAs($person);
    }

    /**
     * @Given a device setup auth code :code for agent :who
     *
     * @param string $code
     * @param string $who
     */
    public function iAddDeviceSetupAuthCode($code, $who)
    {
        $reflection = new \ReflectionProperty(TmpData::class, 'auth');
        $reflection->setAccessible(true);

        $tmpData       = new TmpData();
        $tmpData->auth = $code;
        $tmpData->setData('agent_id', $this->getUserDetails()->getWho($who)->getId());

        $reflection->setAccessible(false);

        $this->em()->persist($tmpData);
        $this->em()->flush();
    }

    /**
     * @return UserDetailsRepo
     */
    private function getUserDetails()
    {
        return $this->get('user_details');
    }

    /**
     * @param Person $person
     * @
     */
    private function authenticateAs(Person $person, $super = false)
    {
        $key = $this->ensureApiKey($person, 'Testing'.($super ? 'super' : ''), $super);
        $this->restContext->iAddHeaderEqualTo('Authorization', 'key '.$key->getKeyString());
    }

    /**
     * @param Person $person
     * @param string $code
     *
     * @return ApiKey
     */
    private function ensureApiKey(Person $person, $code, $super = false)
    {
        // reset global rate limit settings
        $this->em()->getConnection()->executeUpdate(
            'REPLACE INTO settings (name, value) VALUES (:name, :value)',
            ['name' => 'api_limits.global.hour', 'value' => -1]
        );

        $key = $this->repository(ApiKey::class)->findOneBy(compact('code'));
        if (!$key) {
            $key         = new ApiKey();
            $key->code   = $code;
            $key->person = $person;
            $key->addFlag(ApiKey::FLAG_API_V2);
            if ($super) {
                $key->addFlag(ApiKey::FLAG_SUPER_KEY);
            }

            $key_action = new ApiKeyAction();
            $key_action->setAction('*');
            $key->addApiKeyAction($key_action);

            $key_limit = new ApiKeyLimit();
            $key_limit
                ->setType(AbstractLimit::TYPE_KEY)
                ->setStartTime(new \DateTime())
                ->setInterval(AbstractLimit::INTERVAL_HOUR)
                ->setApiKey($key)
                ->setLimit(-1)
                ->setCurrent(-1)
            ;

            $this->persistAndFlush($key);
            $this->persistAndFlush($key_limit);
            $this->persistAndFlush($key_action);
        } else {
            if ($key->person !== $person) {
                $key->person = $person;
                if ($super) {
                    $key->addFlag(ApiKey::FLAG_SUPER_KEY);
                }
                $this->persistAndFlush($key);
            }

            if (!in_array(ApiKey::FLAG_API_V2, $key->flags)) {
                $key->addFlag(ApiKey::FLAG_API_V2);
                $this->persistAndFlush($key);
            }

            // reset rate limit settings of existing api key
            $limit = $this->repository(ApiKeyLimit::class)->findOneBy(['api_key' => $key]);
            $limit->setCurrent(-1);
            $limit->setLimit(-1);
            $this->persistAndFlush($limit);
        }

        DataContext::setReference('apiKey', $key);

        return $key;
    }
}
