<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter\EntityManagerAwareInterface;
use Orb\Auth\Adapter\CallbackInterface;
use Orb\Auth\Adapter\DisplayContextInterface;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Adapter\SamlAdapterInterface;
use Orb\Auth\Adapter\SessionStateInterface;
use Orb\Auth\Adapter\SsoCapableInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Orb\Auth\StateHandler\SessionWrapper;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Log\Writer\Stream;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

/**
 * Does a bunch of logic (which eventually should be done differently) around getting the AuthAdapter ready to use
 * from a Usersource. Make sure this is instantiated with the same "interface" as AuthenticationManager.
 */
class UsersourceAuthAdapterFactory
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var string "user" or "agent"/"admin"/etc
     */
    private $interface;

    public function __construct(DeskproContainer $container, RouterInterface $router, Session $session, $interface)
    {
        $this->container = $container;
        $this->router    = $router;
        $this->session   = $session;
        $this->interface = $interface;
    }

    /**
     * Logic around preparing an auth adapter for use.
     *
     * @param Usersource $usersource
     * @param string     $displayContext
     * @param string     $useInterface
     *
     * @return \Orb\Auth\Adapter\AdapterInterface
     */
    public function getAuthAdapter(Usersource $usersource, $displayContext = null, $useInterface = null)
    {
        $adapter = $usersource->getAdapter();

        if ($adapter instanceof EntityManagerAwareInterface) {
            $adapter->setEm($this->container->getEm());
        }

        if ($adapter instanceof ContainerAwareInterface) {
            $adapter->setContainer($this->container);
        }

        $adapter = $adapter->getAuthAdapter();

        if ($adapter instanceof EntityManagerAwareInterface) {
            $adapter->setEm($this->container->getEm());
        }

        $appEnv = $this->container->get('deskpro.app_env');
        if (($appEnv->isDebug() || $appEnv->getConfig('logs.enable_usersource_log'))
            && $adapter instanceof Loggable) {
            $adapter->setLogger($this->_getAdapterLogger());
        }

        if ($adapter instanceof FormLoginInterface) {
            $adapter->setFormData($_POST);
        }

        if ($displayContext && $adapter instanceof DisplayContextInterface) {
            $adapter->setDisplayContext($displayContext);
        }

        if ($adapter instanceof CallbackInterface) {
            $routeType = 'portal';
            if ($this->isAgentInterface($useInterface)) {
                $routeType = 'agent';
            }

            if ($adapter instanceof SamlAdapterInterface && $displayContext == SsoLoginActionInterface::CONTEXT_BACKGROUND) {
                $url = $this->router->generate(
                    $routeType.'_login_authenticate', ['usersource_id' => $usersource['id'], 'context' => SamlAdapterInterface::CONTEXT_SAML_REDIRECT_BACKGROUND],
                    RouterInterface::ABSOLUTE_URL
                );
            } elseif ($adapter instanceof SamlAdapterInterface && $displayContext == SamlAdapterInterface::CONTEXT_SAML_REDIRECT_BACKGROUND) {
                $url = $this->router->generate(
                    $routeType.'_login_usersource_sso', ['usersource_id' => $usersource['id']],
                    RouterInterface::ABSOLUTE_URL
                );
            } elseif ($adapter instanceof SsoCapableInterface && $displayContext == SsoLoginActionInterface::CONTEXT_BACKGROUND) {
                $url = $this->router->generate(
                    $routeType.'_login_usersource_sso', ['usersource_id' => $usersource['id']],
                    RouterInterface::ABSOLUTE_URL
                );
            } else {
                $url = $this->router->generate(
                    $routeType.'_login_callback', ['usersource_id' => $usersource['id']],
                    RouterInterface::ABSOLUTE_URL
                );
            }

            $adapter->setCallbackUrl(
                $url
            );
        }

        if ($adapter instanceof SessionStateInterface) {
            $authState = new SessionWrapper($this->session);
            $adapter->setStateHandler($authState);
        }

        if ($adapter instanceof SsoCapableInterface) {
            if ($this->isAgentInterface($useInterface)) {
                $logoutUrl = $usersource->getAdapter()->getAgentLogoutRedirectUrl();
            } else {
                $logoutUrl = $usersource->getAdapter()->getUserLogoutRedirectUrl();
            }

            $adapter->setLogoutRedirectUrl($logoutUrl);
        }

        if ($adapter instanceof SamlAdapterInterface) {
            $adapter->setMetadataXmlUrl(
                $this->router->generate(
                    'user_saml_metadata', ['usersource_id' => $usersource->id], RouterInterface::ABSOLUTE_URL
                )
            );
            $adapter->setSingleLogoutServiceUrl(
                $this->router->generate(
                    'user_saml_sls', ['usersource_id' => $usersource->id], RouterInterface::ABSOLUTE_URL
                )
            );
        }

        return $adapter;
    }

    protected function _getAdapterLogger()
    {
        static $logger = null;

        if ($logger === null) {
            $logger = new Logger();
            $logger->addWriter(new Stream($this->container->getLogDir().'/usersource_log.log'));
        }

        return $logger;
    }

    private function isAgentInterface($useInterface = null)
    {
        if ($useInterface && $useInterface != 'agent') {
            return false;
        }

        return $this->interface && $this->interface != 'user';
    }
}
