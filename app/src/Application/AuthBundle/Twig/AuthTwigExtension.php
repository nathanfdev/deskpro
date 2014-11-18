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

namespace Application\AuthBundle\Twig;

use Application\AuthBundle\Handler\LogoutHandler;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Adapter\IframeSsoInterface;
use Orb\Auth\Adapter\JsSsoInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthTwigExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Bundle\TwigBundle\TwigEngine
     */
    public function getTemplating()
    {
        return $this->container->get('templating');
    }

    public function getFunctions()
    {
        return array(
            'background_sso' => new \Twig_Function_Method($this, 'getBackgroundSsoLoader', array('is_safe' => array('html'))),
        );
    }

    public function getBackgroundSsoLoader($interface = 'user')
    {
        ///////////////////////////////////////////////////////////////////////
        // Settings
        /** @var \Application\DeskPRO\Auth\AuthSettings $auth_settings */
        $auth_settings = $this->container->get('dp_auth_settings');
        $auth_interface_settings = $interface == 'user' ? $auth_settings->getUserInterfaceSettings() : $auth_settings->getAgentInterfaceSettings();
        /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
        $request_stack = $this->container->get('request_stack');

        ///////////////////////////////////////////////////////////////////////
        // Ensure GET request
        if (!$request = $request_stack->getCurrentRequest()) {
            return '';
        }
        if ('GET' !== $request->getMethod()) {
            return '';
        }

        ///////////////////////////////////////////////////////////////////////
        // If the user just logged out, we don't want to be logging him in immediately
        if ($request->hasSession() && $request->getSession()->get(LogoutHandler::RECENT_LOGOUT) > 0) {
            return '';
        }

        ///////////////////////////////////////////////////////////////////////
        // Get iFrame Output, if any
        $iFrameOutput = '';
        if ($auth_interface_settings->isBackgroundSsoEnabled()) {
            $adapter = $auth_interface_settings->getSsoAuthAdapter(SsoLoginActionInterface::CONTEXT_BACKGROUND);

            if ($adapter instanceof IframeSsoInterface) {
                $vars = array_merge(
                    array(
                        'iframe_url' => '',
                        'render'     => true
                    ),
                    $adapter->getIframeTemplateParams($is_first_page = false)
                );

                $iFrameOutput = $this->getTemplating()->render(
                    'DeskPRO:Auth:_sso_iframe.html.twig',
                    $vars
                );
            }
        }

        ///////////////////////////////////////////////////////////////////////
        // Some old apps use this code for background authentication
        // needs to stay because Magento native app still uses this
        $legacyOutput = $this->legacyMagentoPluginCode($interface);


        return $iFrameOutput.$legacyOutput;
    }

    /**
     * @param $interface
     * @param $person
     * @param $is_first_page
     * @return array
     */
    protected function legacyMagentoPluginCode($interface)
    {
        $person = null;
        if ($security_context = $this->container->get('security.context')) {
            if ($token = $security_context->getToken()) {
                $person = $token->getUser();
            }
        }

        /** @var \Application\DeskPRO\Auth\AuthenticationManager $auth_manager */
        /** @var \Application\DeskPRO\Usersource\UsersourceManager $us_manager */
        $auth_manager = $this->container->get('dp_authentication_manager.user');
        $us_manager = $auth_manager->getUsersourceManager();
        $sources = $us_manager->getAll()->forInterface($interface)->withCapability(
            UsersourceInfo::CAPABILITY_SSO_JS
        );
        $output = array();
        /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $factory */
        $factory = $auth_manager->getAuthAdapterFactory();
        foreach ($sources AS $source) {
            $adapter = $factory->getAuthAdapter($source, SsoLoginActionInterface::CONTEXT_BACKGROUND);

            if ($adapter instanceof JsSsoInterface) {
                $output[] = $adapter->getSsoHtmlLoaderOutput($source, $this, $person, $is_first_page = false);
            }
        }

        return implode("\n\n", $output);
    }

    public function getName()
    {
        return 'auth_twig_extension';
    }
}
 