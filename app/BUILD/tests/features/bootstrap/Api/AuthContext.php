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

namespace DpBehat\Api;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\TmpData;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;
use DpBehat\Data\Factory\PersonFactories;
use DpBehat\DataSetContext;
use DpTestSrc\TestBundle\UserDetailsRepo;

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
     * @var DataSetContext
     */
    private $dataSetContext;

    /**
     * @var bool DB will be cleaned up before feature if this is set to true
     */
    private static $needCleanup = false;

    /**
     * @var bool
     */
    private static $isFirstFeatureScenario = false;

    /**
     * @var bool
     */
    private static $isTheFirstSuiteScenario = true;

    /**
     * Schedule DB cleanup before next login.
     *
     * @BeforeFeature
     */
    public static function scheduleCleanup()
    {
        self::$needCleanup = true;
    }

    /**
     * @BeforeFeature
     */
    public static function initFirstFeatureScenarioFlag()
    {
        self::$isFirstFeatureScenario = true;
    }

    /**
     * Clean up DB.
     */
    public function cleanup()
    {
        $this->em()->getConnection()->executeQuery('
            DELETE FROM permissions_cache;
            DELETE FROM permissions;
            DELETE FROM task_attachments;
            DELETE FROM custom_def_ticket;
            DELETE FROM department_permissions;
            DELETE FROM people;
            DELETE FROM usergroups;
        ');
        $this->em()->clear();
        DataContext::clear();
    }

    /**
     * @Given I'm authenticated as :role
     */
    public function iAmAuthenticatedAs($role)
    {
        // Install DB on @BeforeSuite ----------------------------------------------------------------------------------
        if (self::$isTheFirstSuiteScenario) {
            $this->dataSetContext->iInstallDataSet('api');
            self::$isTheFirstSuiteScenario = false;
        }

        // Cleanup on @BeforeFeature if needed -------------------------------------------------------------------------
        if (self::$needCleanup && self::$isFirstFeatureScenario) {
            $this->cleanup();
            self::$needCleanup = false;
        }
        self::$isFirstFeatureScenario = false;

        // Log in ------------------------------------------------------------------------------------------------------
        $email = "$role@deskpro.com";
        if (DataContext::getPlaceholder('myEmail', false) === $email) {
            $person = DataContext::getReference('me');
        } else {
            $person            = DataContext::getReference($role, false);
            $person or $person = $this->findPersonByEmail($email);
            if (!$person) {
                PersonFactories::initUsergroups($this->em());
                $person = PersonFactories::create($role, compact('email'));
                $this->persistAndFlush($person);
            }

            DataContext::setReference($role, $person);
            DataContext::setReference('me', $person);
            DataContext::setPlaceholder('myEmail', $email);
        }
        $this->authenticateAs($person);

        self::initOm();
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->restContext    = $environment->getContext('DpBehat\Api\RestContext');
        $this->dataSetContext = $environment->getContext('DpBehat\DataSetContext');
    }

    /**
     * @Given a valid api token exists with the code :token and id :id for :who
     */
    public function aValidApiTokenExistsWithTheCodeAndIdForAgent($token, $id, $who)
    {
        $api_token         = new ApiToken();
        $api_token->token  = $token;
        $api_token->person = $this->getUserDetails()->getWho($who);
        $api_token->scope  = ApiToken::SCOPE_CLIENT;

        $this->persistAndFlush($api_token);

        if ($api_token->id != $id) {
            throw new \Exception('expected api token id ('.$id.') is not correct. please check database.');
        }
    }

    /**
     * @Given a valid api key exists with the code :code for :who
     */
    public function aValidApiKeyExistsWithTheCodeForUser($code, $who)
    {
        $person = $this->getUserDetails()->getWho($who);
        $this->ensureApiKey($person, $code);
    }

    /**
     * @Given the agent session auth :session_id is valid for :who
     */
    public function theAgentSessionIsValidForPerson($session_id, $who)
    {
        $user = $this->getUserDetails()->getWho($who);

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
        if (!$person = $this->getUserDetails()->getWho($who)) {
            throw new \Exception("$who user is missing");
        }
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
        $this->em()->flush($tmpData);
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
     */
    private function authenticateAs(Person $person)
    {
        $key = $this->ensureApiKey($person, 'Testing');
        $this->restContext->iAddHeaderEqualTo('Authorization', 'key '.$key->getKeyString());
    }

    /**
     * @param Person $person
     * @param string $code
     *
     * @return ApiKey
     */
    private function ensureApiKey(Person $person, $code)
    {
        $key = $this->repository(ApiKey::class)->findOneBy(compact('code'));
        if (!$key) {
            $key         = new ApiKey();
            $key->code   = $code;
            $key->person = $person;

            $key_action = new ApiKeyAction();
            $key_action->setAction('*');
            $key->addApiKeyAction($key_action);

            $key_limit = new ApiKeyLimit();
            $key_limit
                ->setType(AbstractLimit::TYPE_KEY)
                ->setStartTime(new \DateTime())
                ->setInterval(AbstractLimit::INTERVAL_HOUR)
                ->setApiKey($key)
                ->setLimit(5000)
                ->setCurrent(5000);

            $this->persistAndFlush($key);
            $this->persistAndFlush($key_limit);
            $this->persistAndFlush($key_action);
        } elseif ($key->person !== $person) {
            $key->person = $person;
            $this->persistAndFlush($key);
        }

        DataContext::setReference('apiKey', $key);

        return $key;
    }

    /**
     * @param string $email
     *
     * @return Person|null
     */
    private function findPersonByEmail($email)
    {
        $email = $this->repository(PersonEmail::class)->findOneBy(compact('email'));

        return $email ? $email->getPerson() : null;
    }
}
