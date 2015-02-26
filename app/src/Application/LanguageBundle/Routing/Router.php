<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package    DeskPRO
 * @subpackage Portal
 */

namespace Application\LanguageBundle\Routing;

use Application\DeskPRO\Entity\Language;
use Application\LanguageBundle\Language\LanguageManager;
use Application\PortalBundle\Mode\PortalModeStorage;
use League\Url\Url;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class Router implements WarmableInterface, RouterInterface, RequestMatcherInterface
{
    public static $generating_ignored_routes = array(
        'saml_sls',
        'saml_metadata',
        'portal_agent_login',
        'user_saml_sls',
        'saml_sls',
        'user_saml_metadata',
        'saml_metadata',
        'portal_logout',
        'portal_login_usersource_sso',
        'portal_login_callback',
        'portal_login_authenticate',
        'portal_login_submit',
        'serve_blob_sizefit',
        'serve_default_picture',
        'serve_blob',
        'admin_interface',
        'agent_interface',
        '_wdt',
        '_profiler',
    );

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $router;

    /**
     * @var \Application\LanguageBundle\Language\LanguageManager
     */
    private $language_manager;

    /**
     * @var PortalModeStorage
     */
    private $mode_store;

    public function __construct(BaseRouter $router, LanguageManager $language_manager, PortalModeStorage $mode_store)
    {
        $this->router = $router;
        $this->language_manager = $language_manager;
        $this->mode_store = $mode_store;
        $this->router->setOption('matcher_cache_class', 'ProjectUrlMatcher');
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $generated = $this->router->generate($name, $parameters, $referenceType);

        if (in_array($name, self::$generating_ignored_routes)) {
            return $generated;
        }

        if (self::ABSOLUTE_URL === $referenceType) {
            // deal with an absolute URL by isolating just the path
            $url = Url::createFromUrl($generated);
            $path = (string) ($url->getPath());

            // build with just the path
            $built_path = $this->buildUrl($path);

            // add the path back to the original generated url
            $url->setPath($built_path);

            return (string) $url;
        }

        return $this->buildUrl($generated);
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request)
    {
        $request_info = new PortalRequestInfo($request, $this->getPortalMode());
        $request_info->setRouter($this->router);

        // if its not safe, or its a special url, just match it immediately
        if (!$request->isMethodSafe() || $request_info->isSpecialPath()) {
            $routable_path = $request_info->getRoutablePath();
            $params = $this->router->match($routable_path);

            return $params;
        }

        // if there is or isn't a lang code in url when should be, redirect
        if (
            ($this->isMultiLanguage() && !$request_info->getLanguageUrlCode())
            || (!$this->isMultiLanguage() && $request_info->getLanguageUrlCode())
        ) {
            throw new RedirectToUrlException(
                $this->buildUrl($request_info->getRoutablePath())
            );
        }

        $routable_path = $request_info->getRoutablePath();
        $params = $this->router->match($routable_path);

        return $params;
    }

    protected function buildUrl($path)
    {
        $url_builder = new PortalUrlBuilder(
            $path,
            $this->isMultiLanguage() ? $this->getActiveLanguage() : null,
            $this->getPortalMode()
        );

        return (string) $url_builder;
    }

    protected function getActiveLanguage()
    {
        return $this->language_manager->getLanguageStack()->getActive();
    }

    protected function getPortalMode()
    {
        return $this->mode_store->getMode();
    }

    protected function isMultiLanguage()
    {
        return $this->language_manager->isMultiLanguagePortal();
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function match($path_info)
    {
        return $this->matchRequest(Request::create($path_info));
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->router->warmUp($cacheDir);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }
}
