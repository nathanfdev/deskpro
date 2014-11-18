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

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter\EntityManagerAwareInterface;
use Orb\Auth\StateHandler\SessionWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Orb\Auth\Adapter\SamlAdapterInterface;
use Orb\Auth\Adapter\SsoCapableInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;
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

    /**
     * @var Request
     */
    private $request;


    public function __construct(DeskproContainer $container, RouterInterface $router, Request $request, Session $session, $interface)
    {
        $this->container = $container;
        $this->router = $router;
        $this->session = $session;
        $this->interface = $interface;
        $this->request = $request;
    }


    /**
     * Logic around preparing an auth adapter for use
     *
     * @param  Usersource                         $usersource
     * @param  null                               $displayContext
     * @return \Orb\Auth\Adapter\AdapterInterface
     */
    public function getAuthAdapter(Usersource $usersource, $displayContext = null, $useInterface = null)
    {
        $adapter = $usersource->getAdapter();

        if ($adapter instanceof EntityManagerAwareInterface) {
            $adapter->setEm($this->container->getEm());
        }

        $adapter = $adapter->getAuthAdapter();

        if ($adapter instanceof EntityManagerAwareInterface) {
            $adapter->setEm($this->container->getEm());
        }

        if (App::getConfig('debug.enable_usersource_log') && $adapter instanceof \Orb\Log\Loggable) {
            $adapter->setLogger($this->_getAdapterLogger());
        }

        if ($adapter instanceof \Orb\Auth\Adapter\FormLoginInterface) {
            $adapter->setFormData($_POST);
        }

        if ($displayContext && $adapter instanceof \Orb\Auth\Adapter\DisplayContextInterface) {
            $adapter->setDisplayContext($displayContext);
        }

        if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
			$route_type = 'portal';
            if ($this->isAgentInterface($useInterface)) {
                $route_type = 'agent';
            }

            if ($adapter instanceof SamlAdapterInterface && $displayContext == SsoLoginActionInterface::CONTEXT_BACKGROUND) {
                $url = $this->router->generate(
                    $route_type . '_login_authenticate', array('usersource_id' => $usersource['id'], 'context' => SamlAdapterInterface::CONTEXT_SAML_REDIRECT_BACKGROUND),
                    RouterInterface::ABSOLUTE_URL
                );
            } elseif ($adapter instanceof SamlAdapterInterface && $displayContext == SamlAdapterInterface::CONTEXT_SAML_REDIRECT_BACKGROUND) {
                $url = $this->router->generate(
                    $route_type . '_login_usersource_sso', array('usersource_id' => $usersource['id']),
                    RouterInterface::ABSOLUTE_URL
                );
            } elseif ($adapter instanceof SsoCapableInterface && $displayContext == SsoLoginActionInterface::CONTEXT_BACKGROUND) {
                $url = $this->router->generate(
                    $route_type . '_login_usersource_sso', array('usersource_id' => $usersource['id']),
                    RouterInterface::ABSOLUTE_URL
                );
            } else {
                $url = $this->router->generate(
                    $route_type . '_login_callback', array('usersource_id' => $usersource['id']),
                    RouterInterface::ABSOLUTE_URL
                );
            }

            $adapter->setCallbackUrl(
                $url
            );
        }

        if ($adapter instanceof \Orb\Auth\Adapter\SessionStateInterface) {
			$auth_state = new SessionWrapper($this->session);
            $adapter->setStateHandler($auth_state);
        }

        if ($adapter instanceof \Orb\Auth\Adapter\SsoCapableInterface) {
            if ($this->isAgentInterface($useInterface)) {
                $logout_url = $usersource->getAdapter()->getAgentLogoutRedirectUrl();
            } else {
                $logout_url = $usersource->getAdapter()->getUserLogoutRedirectUrl();
            }

            $adapter->setLogoutRedirectUrl($logout_url);
        }

        if ($adapter instanceof SamlAdapterInterface) {
            $adapter->setMetadataXmlUrl(
                $this->router->generate(
                    'user_saml_metadata', array('usersource_id' => $usersource->id), RouterInterface::ABSOLUTE_URL
                ));
            $adapter->setSingleLogoutServiceUrl(
                $this->router->generate(
                    'user_saml_sls', array('usersource_id' => $usersource->id), RouterInterface::ABSOLUTE_URL
                )
            );
        }

        return $adapter;
    }


    protected function _getAdapterLogger()
    {
        static $logger = null;

        if ($logger === null) {
            $logger = new \Orb\Log\Logger();
            $logger->addWriter(new \Orb\Log\Writer\Stream($this->container->getLogDir() . '/usersource_log.log'));
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
