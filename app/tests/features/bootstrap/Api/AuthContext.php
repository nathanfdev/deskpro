<?php

namespace DpBehat\Api;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Session;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use DeskPRO\Bundle\AppBundle\Entity\SandboxWidget;
use Doctrine\ORM\EntityManager;
use DpBehat\BaseContext;
use DpTests\TestBundle\UserDetailsRepo;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Defines application features from the specific context.
 */
class AuthContext extends BaseContext
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

    public function __construct(EntityManager $em, UserDetailsRepo $user_details)
    {
        $this->em = $em;
        $this->user_details = $user_details;
    }

    /**
     * @Given a valid api token exists with the code :token and id :id for :who
     */
    public function aValidApiTokenExistsWithTheCodeAndIdForAgent($token, $id, $who)
    {
        $api_token = new ApiToken();
        $api_token->token = $token;
        $api_token->person = $this->user_details->getWho($who);
        $api_token->scope = ApiToken::SCOPE_CLIENT;

        $this->persistAndFlush($api_token);

        if ($api_token->id != $id) {
            throw new \Exception('expected api token id (' . $id . ') is not correct. please check database.');
        }
    }

    /**
     * @Given a valid api key exists with the code :code and id :id for :who
     */
    public function aValidApiKeyExistsWithTheCodeAndIdForUser($code, $id, $who)
    {
        $key = new ApiKey();
        $key->code = $code;
        $key->person = $this->user_details->getWho($who);

        $this->persistAndFlush($key);

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
        $data = session_encode();
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

        $this->rest_context->iAddHeaderEqualTo('Authorization', 'key ' . $key->getKeyString());
    }
}
