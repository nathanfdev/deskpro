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
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\TmpData;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use DpBehat\BaseContext;
use DpTestSrc\TestBundle\UserDetailsRepo;

/**
 * Defines application features from the specific context.
 */
class AuthContext extends BaseContext
{
    /**
     * @var RestContext
     */
    private $rest_context;

    /**
     * @var ApiKey|null
     */
    public static $apiKey = null;

    /**
     * @var Person|null
     */
    public static $user = null;

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
        if (!self::$apiKey || self::$apiKey->person !== $person) {
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

            self::$apiKey = $key;
        }
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

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->rest_context = $environment->getContext('DpBehat\Api\RestContext');
    }

    /**
     * @Given my request is authenticated
     */
    public function myRequestIsAuthenticated()
    {
        $this->myRequestIsAuthenticatedTo('admin');
    }

    /**
     * @Given my request is authenticated to :who
     *
     * @param string $who
     */
    public function myRequestIsAuthenticatedTo($who)
    {
        self::$user = $person = $this->getUserDetails()->getWho($who);

        $repository = $this->em()->getRepository(ApiKey::class);

        $keys = $repository->findAll();
        if (count($keys)) {
            $key = $keys[0];
            if ($key->person !== $person) {
                $key->person = $person;
                $this->persistAndFlush($key);
            }
        } else {
            $this->aValidApiKeyExistsWithTheCodeForUser('MyCode', $who);
            $key = $repository->findAll()[0];
        }

        $this->rest_context->iAddHeaderEqualTo('Authorization', 'key '.$key->getKeyString());
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
        return $this->getContainer()->get('user_details');
    }
}
