<?php

namespace DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Request\OriginalUrlGenerator;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DeskPRO\Bundle\AppBundle\Routing\RouterDecorator;
use DeskPRO\Bundle\AppBundle\Routing\RouterUtils;
use DeskPRO\Bundle\AppBundle\Routing\RouterWithDynamicContext;
use DeskPRO\Bundle\BrandBundle\Request\OriginalRequestStorage;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use League\Url\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PortalRouter.
 */
class PortalRouter implements WarmableInterface, RouterInterface, RequestMatcherInterface, RouterDecorator
{
    public static $generatingIgnoredRoutes = [
        'saml_sls',
        'saml_metadata',
        'portal_agent_login',
        'user_saml_sls',
        'saml_sls',
        'user_saml_metadata',
        'saml_metadata',
        'serve_blob_sizefit',
        'serve_default_picture',
        'serve_blob',
        'admin',
        'user_context_hash',
        'agent',
        'serve_brand_asset',
        '_wdt',
        '_profiler',
    ];

    public static $nonBrandRoutes = [
        'agent',
        '_profiler',
        '_wdt',
        'serve_blob',
        'serve_blob_sizefit',
        'serve_default_picture',
        'serve_org_picture_default',
    ];

    public static $legacyPortalRoutes = [
        'user_admin_rendertpl',
        'user_comment_form_login_partial',
        'user_test',
        'user_saverating',
        'user_newcomment_finishlogin',
        'user_accept_upload',
        'user_validate_email',
        'user_login_resetpass_send',
        'user_login_resetpass_newpass',
        'user_profile_setlang',
        'user_profile_associate_twitter',
        'user_profile_twitter_remove',
        'user_profile_changepassword',
        'user_profile_emails_new',
        'user_profile_emails_remove',
        'user_profile_emails_validate_remove',
        'user_profile_emails_validate_sendlink',
        'user_profile_emails_setdefault',
        'user_search_omnisearch',
        'user_tickets_new_finishlogin',
        'user_tickets_new_simple',
        'user_tickets_new_savestatus',
        'user_tickets_new_contentsolved_save',
        'user_tickets_new_contentsolved',
        'user_tickets_new_thanks',
        'user_tickets_new_thanks_simple',
        'user_tickets_view',
        'user_tickets_addreply',
        'user_tickets_participants',
        'user_tickets_participants_add',
        'user_tickets_participants_remove',
        'user_tickets_resolve',
        'user_tickets_unresolve',
        'user_tickets_feedback',
        'user_tickets_feedback_save',
        'user_tickets_feedback_closeticket',
        'user_articles_article_togglesub',
        'user_articles_cat_togglesub',
        'user_articles_unsub_all',
        'user_articles_article_agent_iframe',
        'user_articles_newcomment',
        'user_downloads_file_download',
        'user_downloads_newcomment',
        'user_news_newcomment',
        'user_feedback',
        'user_feedback_new',
        'user_feedback_newfeedback_finishlogin',
        'user_feedback_newcomment',
        'user_feedback_vote',
        'user_chat_initsession',
        'user_chat_widgetisavail',
        'user_chat_poll',
        'user_chat_sendmessage',
        'user_chat_sendmessage_attach',
        'user_chat_sendusertyping',
        'user_chat_chatended',
        'user_chat_chatended_feedback',
        'user_chatlogs',
        'user_chatlogs_view',
        'user_widget_overlay',
        'user_widget_newticket',
        'user_widget_newfeedback',
        'user_widget_chat',
        'user_long_tweet_view',
        'agent_ticket_chargeform',
        'agent_ticket_close_problem',
        'agent_ticket_reopen_problem',
        'admin_portaleditor_updateblockorder',
        'admin_portaleditor_blocktoggle',
        'admin_portaleditor_custom_block_delete',
        'admin_portaleditor_custom_sideblock_save',
        'admin_portaleditor_custom_sideblock_simple_get',
        'admin_portaleditor_custom_sideblock_simple_save',
        'admin_portaleditor_custom_sideblock_simple_delete',
        'admin_portaleditor_toggle',
        'admin_portaleditor_get_editor',
        'admin_portaleditor_save_editor',
        'admin_portaleditor_twitter_oauth',
        'admin_portaleditor_accept_upload',
    ];

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var OriginalUrlGenerator
     */
    private $originalUrlGenerator;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $languageManager;

    /**
     * @var PortalModeStorage
     */
    private $portalModeStorage;

    /**
     * @var PortalModeFactory
     */
    private $modeFactory;

    /**
     * @var PortalUrlBuilder
     */
    private $portalUrlBuilder;

    /**
     * @var Request
     */
    private $originalRequestStorage;

    /**
     * Constructor.
     *
     * @param RouterInterface        $router
     * @param OriginalUrlGenerator   $originalUrlGenerator
     * @param LanguageManager        $languageManager
     * @param PortalModeStorage      $portalModeStorage
     * @param PortalModeFactory      $modeFactory
     * @param PortalUrlBuilder       $portalUrlBuilder
     * @param OriginalRequestStorage $originalRequestStorage
     */
    public function __construct(
        RouterInterface        $router,
        OriginalUrlGenerator   $originalUrlGenerator,
        LanguageManager        $languageManager,
        PortalModeStorage      $portalModeStorage,
        PortalModeFactory      $modeFactory,
        PortalUrlBuilder       $portalUrlBuilder,
        OriginalRequestStorage $originalRequestStorage
    ) {
        $this->router                 = $router;
        $this->originalUrlGenerator   = $originalUrlGenerator;
        $this->languageManager        = $languageManager;
        $this->portalModeStorage      = $portalModeStorage;
        $this->modeFactory            = $modeFactory;
        $this->portalUrlBuilder       = $portalUrlBuilder;
        $this->originalRequestStorage = $originalRequestStorage;
    }

    /**
     * @return RouterInterface
     */
    public function getBaseRouter()
    {
        return $this->router;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        // ignore legacy routes that we delete to prevent random 500s (return a blank string)
        if (in_array($name, self::$legacyPortalRoutes)) {
            return '';
        }

        $portalMode = $this->portalModeStorage->getMode();
        if (
            $portalMode
            && $portalMode->isAdminPreview()
            && $this->router instanceof RouterWithDynamicContext
        ) {
            $this->router = RouterUtils::unwrapDecoratedRouter($this->router);
        }

        // we hack request if we're in brand slug mode, i.e. if we have `my-brand` slug
        // then we add `/b/my-brand` to the request's base url
        // but some of generating urls on the portal are non-brandable (e.g. `/agent` or `_profiler`)
        // so generate them using original (unhacked) request
        if ($this->originalRequestStorage->getOriginalRequest() && in_array($name, self::$nonBrandRoutes)) {
            $generated = $this->originalUrlGenerator->generate($this->originalRequestStorage->getOriginalRequest(), $name, $parameters, $referenceType);
        } else {
            $generated = $this->router->generate($name, $parameters, $referenceType);
        }

        if (false !== strpos($generated, 'index.php//')) {
            $generated = str_replace('index.php//', 'index.php/', $generated);
        }

        if (in_array($name, self::$generatingIgnoredRoutes)) {
            return $generated;
        }

        if (self::ABSOLUTE_URL === $referenceType) {
            // deal with an absolute URL by isolating just the path
            $url  = Url::createFromUrl($generated);
            $path = (string) ($url->getPath());

            // build with just the path
            $built_path = $this->portalUrlBuilder->buildUrl($path);

            // add the path back to the original generated url
            $url->setPath($built_path);

            return (string) $url;
        }

        return $this->portalUrlBuilder->buildUrl($generated);
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request)
    {
        $requestInfo = new PortalRequestInfo($request, $this->getPortalMode(), $this->modeFactory);
        $requestInfo->setRouter($this->router);

        // if its not safe, or its a special url, just match it immediately
        if (!$request->isMethodSafe() || $requestInfo->isSpecialPath()) {
            $routablePath = $requestInfo->getRoutablePath();
            $params       = $this->router->match($routablePath);

            return $params;
        }

        // if there is or isn't a lang code in url when should be, redirect
        if (
            !RequestUtils::isLowRequest($request)
            && !RequestUtils::isPortalApiRequest($request)
            && !RequestUtils::isSysRequest($request)
            && !RequestUtils::isProxyRequest($request)
            && (
                ($this->isMultiLanguage() && !$requestInfo->getLanguageUrlCode())
                || (!$this->isMultiLanguage() && $requestInfo->getLanguageUrlCode())
            )
        ) {
            $url = $this->portalUrlBuilder->buildUrl($requestInfo->getRoutablePath());

            $queryParams = $request->query->all();
            if ($queryParams) {
                $url .= '?'.http_build_query($queryParams);
            }

            if (($this->isMultiLanguage() && !$requestInfo->getLanguageUrlCode())) {
                $message = 'MultiLanguage and missing lang code';
            } else {
                $message = 'Not MultiLanguage but have lang code';
            }

            $message .= ' (lang code: '.($requestInfo->getLanguageUrlCode() ?: 'unset').')';

            throw new RedirectToUrlException($url, $message);
        }

        $routablePath = $requestInfo->getRoutablePath();
        $params       = $this->router->match($routablePath);

        return $params;
    }

    /**
     * Is used by DpKernel.
     *
     * @return $this
     *
     * @deprecated This is not part of RouterInterface, dont use this method
     */
    public function getGenerator()
    {
        return $this;
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Mode\PortalMode
     */
    protected function getPortalMode()
    {
        return $this->portalModeStorage->getMode();
    }

    /**
     * @return bool
     */
    protected function isMultiLanguage()
    {
        return $this->languageManager->isMultiLanguagePortal();
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
    public function match($pathInfo)
    {
        return $this->matchRequest(Request::create($pathInfo));
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        if ($this->router instanceof WarmableInterface) {
            $this->router->warmUp($cacheDir);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }
}
