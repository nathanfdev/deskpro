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

namespace DpBehat\Api;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Session;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use Doctrine\ORM\EntityManager;
use DpBehat\BaseContext;
use DpBehat\RebootableContextInterface;
use DpTestSrc\TestBundle\UserDetailsRepo;

/**
 * Defines application features from the specific context.
 */
class AuthContext extends BaseContext implements RebootableContextInterface
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var UserDetailsRepo
     */
    private $user_details;
    /**
     * @var RestContext
     */
    private $rest_context;

    public function rebootContext()
    {
        $this->rebootAuthContext();
    }

    public function rebootAuthContext()
    {
        $this->em           = $this->getKernel()->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->user_details = $this->getKernel()->getContainer()->get('user_details');
    }

    /**
     * @Given a valid api token exists with the code :token and id :id for :who
     */
    public function aValidApiTokenExistsWithTheCodeAndIdForAgent($token, $id, $who)
    {
        $api_token         = new ApiToken();
        $api_token->token  = $token;
        $api_token->person = $this->user_details->getWho($who);
        $api_token->scope  = ApiToken::SCOPE_CLIENT;

        $this->persistAndFlush($api_token);

        if ($api_token->id != $id) {
            throw new \Exception('expected api token id ('.$id.') is not correct. please check database.');
        }
    }

    /**
     * @Given a valid api key exists with the code :code and id :id for :who
     */
    public function aValidApiKeyExistsWithTheCodeAndIdForUser($code, $id, $who)
    {
        $key         = new ApiKey();
        $key->code   = $code;
        $key->person = $this->user_details->getWho($who);

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

        if ($key->id != $id) {
            throw new \Exception('expected api key id ('.$id.') does not match ('.$key->id.'). please check database.');
        }
    }

    /**
     * @Given the agent session auth :session_id is valid for :who
     */
    public function theAgentSessionIsValidForPerson($session_id, $who)
    {
        $user = $this->user_details->getWho($who);

        session_start();
        $_SESSION['_sf2_attributes'] = array('auth_person_id' => $user->getId());
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
        $key_repo = $this->em->getRepository('DeskPRO:ApiKey');
        if (!$key = $key_repo->find(1)) {
            $this->aValidApiKeyExistsWithTheCodeAndIdForUser('MyCode', 1, 'admin');
            $key = $key_repo->find(1);
        }

        $this->rest_context->iAddHeaderEqualTo('Authorization', 'key '.$key->getKeyString());
    }
}
