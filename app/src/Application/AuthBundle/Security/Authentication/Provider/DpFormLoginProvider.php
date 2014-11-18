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
 * @subpackage Portal
 */

namespace Application\AuthBundle\Security\Authentication\Provider;

use Application\AuthBundle\Security\DpFormLoginToken;
use Application\AuthBundle\Security\DpPersonUserProvider;
use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Auth\AuthenticationManager as DpAuthManager;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Result;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class DpFormLoginProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Application\DeskPRO\Auth\AuthenticationManager
     */
    private $dp_auth_manager;

    /**
     * @var \Application\AuthBundle\Security\DpPersonUserProvider
     */
    private $dp_person_provider;

    public function __construct(DpAuthManager $dp_auth_manager, DpPersonUserProvider $dp_person_provider)
    {
        $this->dp_auth_manager = $dp_auth_manager;
        $this->dp_person_provider = $dp_person_provider;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(TokenInterface $token)
    {
        if ($token->isAuthenticated()) {
            return $token;
        }

        /** @var Usersource $usersource */
        /** @var Result $authResult */
        $auth_manager = $this->dp_auth_manager;
        list($authResult, $usersource) = $this->getDpAuthResultForGivenUsersources($token, $auth_manager);

        // if its the user interface and we failed, please try agent usersources as well
        if (!$authResult->isValid() && 'user' === $this->dp_auth_manager->getInterface()) {
            $auth_manager = $this->dp_auth_manager->cloneForInterface('agent');
            list($authResult, $usersource) = $this->getDpAuthResultForGivenUsersources($token, $auth_manager);
        }

        if ($authResult->isRedirectRequired()) {
            // there currently are not any cases in form login where a redirect is required
        }

        if ($authResult->isValid()) {
            // now we emulate the LoginProcessor
            // TODO: for now, we are actually using the LoginProcessor class here, but we will change this to be in
            // some sort of UserProviderInterface eventually....
            $login_processor = new LoginProcessor($usersource, $authResult->getIdentity());
            $person = $login_processor->getPerson();

            $authenticatedToken = new DpFormLoginToken($person, $person->getPassword(), array_merge(array('ROLE_USER'), $person->getRoles()));
            $authenticatedToken->setAttributes($token->getAttributes());

            return $authenticatedToken;
        }

        throw new BadCredentialsException;
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return bool    true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof DpFormLoginToken;
    }

    /**
     * @param TokenInterface $token
     * @param Usersource[]   $usersources
     * @return Result
     */
    protected function getDpAuthResultForGivenUsersources(TokenInterface $token, AuthenticationManager $auth_manager)
    {
        foreach ($auth_manager->getFormLoginUsersources() as $us) {
            $adapter = $auth_manager->getAuthAdapterFactory()->getAuthAdapter($us);

            if ($adapter instanceof FormLoginInterface) {
                $adapter->setFormData(
                    array(
                        'username' => $token->getUsername(),
                        'password' => $token->getCredentials()
                    )
                );

                try {
                    $authResult = $adapter->authenticate();
                } catch (\Exception $e) {
                    KernelErrorHandler::logException($e, false);
                    $GLOBALS['DP_AUTH_EXCEPTION_ADAPTER'] = $adapter;
                    $GLOBALS['DP_AUTH_EXCEPTION'] = $e;
                    continue;
                }

                if ($authResult->isValid()) {

                    return array($authResult, $us);
                }
            }
        }

        $failedResult = new Result(Result::FAILURE_INVALID_CREDS);

        return array($failedResult, $us);
    }
}
 