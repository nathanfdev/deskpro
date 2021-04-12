<?php

namespace DeskPRO\Bundle\AppBundle\Security\Authentication\Provider;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\Auth\AuthenticationManager as DpAuthManager;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter\DeskPRO;
use DeskPRO\Bundle\AppBundle\Security\DpFormLoginToken;
use DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider;
use DeskPRO\Bundle\PortalBundle\Routing\PasswordResetException;
use DpSys\LowError\SystemErrorHandler;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;

/**
 * Class DpFormLoginProvider.
 */
class DpFormLoginProvider implements AuthenticationProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Security\DpPersonUserProvider
     */
    private $dpPersonProvider;

    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor.
     *
     * @param ContainerInterface   $container
     * @param DpPersonUserProvider $dpPersonProvider
     * @param Session              $session
     */
    public function __construct(ContainerInterface $container, DpPersonUserProvider $dpPersonProvider, Session $session)
    {
        $this->container        = $container;
        $this->dpPersonProvider = $dpPersonProvider;
        $this->session          = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if ($token->isAuthenticated()) {
            return $token;
        }

        $this->session->set('last_username', $token->getUsername());

        /* @var Usersource $usersource */
        /* @var Result $authResult */
        $auth_manager                  = $this->container->get('dp_authentication_manager.user');
        list($authResult, $usersource) = $this->getDpAuthResultForGivenUsersources($token, $auth_manager);

        // if its the user interface and we failed, please try agent usersources as well
        if (!$authResult->isValid() && 'user' === $auth_manager->getInterface()) {
            $auth_manager                  = $auth_manager->cloneForInterface('agent');
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
            $person          = $login_processor->getPerson();

            if ($this->dpPersonProvider->personHasBannedEmail($person)) {
                $errorMessage = $this->isHelpcenter() ? 'helpcenter.account.login_disabled' : 'portal.account.login-disabled';

                throw new DisabledException($errorMessage);
            }
            if (!$this->container->get('dp_limit_email_domains_checker')->checkPerson($person)) {
                $errorMessage = $this->isHelpcenter() ? 'helpcenter.account.login_invalid' : 'portal.account.login-invalid';

                throw new BadCredentialsException($errorMessage);
            }

            // check if user has this brand
            $brand = $this->container->get('brand_stack')->getActive()->getBrand();
            if ($brand && !$person->hasBrand($brand)) {
                // reg for this brand is enabled
                // add person to this brand and continue log in
                if ($usersource->getSourceType() === DeskPRO::class && !$this->container->get('dp_authentication_manager.user')->isRegistrationFormVisible()) {
                    // no way to log in, show incorrect credentials message
                    $errorMessage = $this->isHelpcenter() ? 'helpcenter.account.login_invalid' : 'portal.account.login-invalid';

                    throw new BadCredentialsException($errorMessage);
                }

                $person->addBrand($brand);

                $em = $this->container->get('doctrine.orm.default_entity_manager');
                $em->persist($person);
                $em->flush();
            }

            if ($person->isAgent() && $usersource->getSourceType() === DeskPRO::class && $usersource->getType() === Usersource::TYPE_USER) {
                $deskproAgentsource = $this->container->get('doctrine.orm.entity_manager')->getRepository(Usersource::class)->findOneBy([
                    'title'      => 'Deskpro',
                    'type'       => Usersource::TYPE_AGENT,
                    'is_enabled' => false,
                ]);

                if ($deskproAgentsource) {
                    $errorMessage = $this->isHelpcenter() ? 'helpcenter.account.login_invalid' : 'portal.account.login-invalid';

                    throw new BadCredentialsException($errorMessage);
                }
            }

            $authenticatedToken = new DpFormLoginToken($person, $person->getPassword(), array_merge(['ROLE_USER'], $person->getRoles()));
            $authenticatedToken->setAttributes($token->getAttributes());

            $this->session->set('auth_person_id', $person->getId());
            $this->session->set('dp_interface', DP_INTERFACE);
            $this->session->set('auth_usersource_id', $usersource ? $usersource->getId() : null);
            $this->session->set('auth_usersource_type', $usersource ? $usersource->getSourceType() : null);
            $this->session->save();

            return $authenticatedToken;
        }

        $errorMessage = $this->isHelpcenter() ? 'helpcenter.account.login_invalid' : 'portal.account.login-invalid';

        throw new BadCredentialsException($errorMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof DpFormLoginToken;
    }

    /**
     * @param TokenInterface $token
     * @param DpAuthManager  $auth_manager
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function getDpAuthResultForGivenUsersources(TokenInterface $token, AuthenticationManager $auth_manager)
    {
        foreach ($auth_manager->getFormLoginUsersources() as $us) {
            $adapter = $auth_manager->getAuthAdapterFactory()->getAuthAdapter($us);

            if ($adapter instanceof FormLoginInterface) {
                $adapter->setFormData(
                    [
                        'username' => $token->getUsername(),
                        'password' => $token->getCredentials(),
                    ]
                );

                try {
                    $authResult = $adapter->authenticate();
                } catch (AuthenticationException $e) {
                    throw $e;
                } catch (PasswordResetException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e, false);
                    $GLOBALS['DP_AUTH_EXCEPTION_ADAPTER'] = $adapter;
                    $GLOBALS['DP_AUTH_EXCEPTION']         = $e;

                    continue;
                }

                if ($authResult->isValid()) {
                    return [$authResult, $us];
                }
            }
        }

        return [new Result(Result::FAILURE_INVALID_CREDS), isset($us) ? $us : null];
    }

    protected function isHelpcenter()
    {
        $portalBrandThemeLoader = $this->container->get('portal_brand_theme_loader');
        if ($portalBrandThemeLoader) {
            return $portalBrandThemeLoader->getPortalBrandTheme($this->container->get('brand_stack')->getActive()->getBrand())->getActiveThemeSet()->getThemeId() === 'helpcenter';
        }
    }
}
