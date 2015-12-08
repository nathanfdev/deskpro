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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use League\Url\Url;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class PortalRouter implements WarmableInterface, RouterInterface, RequestMatcherInterface
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
        'admin',
        'agent',
        'serve_brand_asset',
        '_wdt',
        '_profiler',
    );

    public static $legacy_portal_routes = array(
        'user_admin_rendertpl',
        'user_comment_form_login_partial',
        'user_test',
        'user_saverating',
        'user_newcomment_finishlogin',
        'user_accept_upload',
        'user_validate_email',
        'user_jstell_login',
        'user_login_inline',
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
    );

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $router;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $language_manager;

    /**
     * @var PortalModeStorage
     */
    private $mode_store;

    /**
     * @var PortalModeFactory
     */
    private $mode_factory;

    public function __construct(BaseRouter $router, LanguageManager $language_manager, PortalModeStorage $mode_store, PortalModeFactory $mode_factory)
    {
        $this->router           = $router;
        $this->language_manager = $language_manager;
        $this->mode_store       = $mode_store;
        $this->mode_factory     = $mode_factory;
        $this->router->setOption('matcher_cache_class', 'ProjectUrlMatcher');
    }

    public function getBaseRouter()
    {
        return $this->router;
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        // ignore legacy routes that we delete to prevent random 500s (return a blank string)
        if (in_array($name, self::$legacy_portal_routes)) {
            return '';
        }

        $generated = $this->router->generate($name, $parameters, $referenceType);

        if (false !== strpos($generated, 'index.php//')) {
            $generated = str_replace('index.php//', 'index.php/', $generated);
        }

        if (in_array($name, self::$generating_ignored_routes)) {
            return $generated;
        }

        if (self::ABSOLUTE_URL === $referenceType) {
            // deal with an absolute URL by isolating just the path
            $url  = Url::createFromUrl($generated);
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
        $request_info = new PortalRequestInfo($request, $this->getPortalMode(), $this->mode_factory);
        $request_info->setRouter($this->router);

        // if its not safe, or its a special url, just match it immediately
        if (!$request->isMethodSafe() || $request_info->isSpecialPath()) {
            $routable_path = $request_info->getRoutablePath();
            $params        = $this->router->match($routable_path);

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
        $params        = $this->router->match($routable_path);

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

    /**
     * This has a semantically different meaning from the standard generate() function. Both methods were
     * used in the DpKernel Router, and the difference seems to be that:.
     *
     * generateUrl is absolute
     *
     * @param $name
     * @param array $parameters
     *
     * @return string
     *
     * @deprecated use generate()
     */
    public function generateUrl($name, $parameters = array())
    {
        return $this->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Is used by DpKernel.
     *
     * @return $this
     *
     * @deprecated $this is a generator already
     */
    public function getGenerator()
    {
        return $this;
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
