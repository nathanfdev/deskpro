<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\Entity\Person;
use Carbon\Carbon;
use DeskPRO\Bundle\AppBundle\Routing\RouterUtils;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeView;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Extension designed to make working with templates easier by simplifying
 * expressions or making them look less scary.
 */
class PortalSupportExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        $token_parsers = [
            new TokenParser\ShowParser($this),
            new TokenParser\GroupParser($this),
            new TokenParser\GroupItemParser($this),
        ];

        return $token_parsers;
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        $visitors = [
            new NodeVisitor\GroupVisitor(),
        ];

        return $visitors;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $funcs = [
            new \Twig_SimpleFunction('can_use_*', [$this, 'canUseCheck']),
            new \Twig_SimpleFunction('can_rate_*', [$this, 'canRateCheck']),
            new \Twig_SimpleFunction('can_view_tickets_link', [$this, 'canViewTicketsLink']),
            new \Twig_SimpleFunction('show_tab_*', [$this, 'showTab']),
            new \Twig_SimpleFunction('has_any_*', [$this, 'hasAnyCheck']),
            new \Twig_SimpleFunction('is_user', [$this, 'isUser']),
            new \Twig_SimpleFunction('is_agent', [$this, 'isAgent']),
            new \Twig_SimpleFunction('is_admin', [$this, 'isAdmin']),
            new \Twig_SimpleFunction('is_impersonating', [$this, 'isImpersonating']),
            new \Twig_SimpleFunction('is_guest', [$this, 'isGuest']),
            new \Twig_SimpleFunction('is_page_*', [$this, 'pageIsCheck']),
            new \Twig_SimpleFunction('is_pdf_mode', [$this, 'isPdfMode']),
            new \Twig_SimpleFunction('col_count', [$this, 'countTruthy']),
            new \Twig_SimpleFunction('has_permission', [$this, 'hasPermission']),
            new \Twig_SimpleFunction('get_ordered_tabs', [$this, 'getOrderedTabs']),
            new \Twig_SimpleFunction('url_full', [$this, 'urlFull']),
            new \Twig_SimpleFunction('base_url', [$this, 'baseUrl']),
            new \Twig_SimpleFunction('root_url', [$this, 'rootUrl']),
            new \Twig_SimpleFunction('no_cache_url', [$this, 'noCacheUrl']),
            new \Twig_SimpleFunction('is_multi_lang', [$this, 'isMultLang']),
            new \Twig_SimpleFunction('lang_code', [$this, 'langCode']),
            new \Twig_SimpleFunction('lang_dir', [$this, 'langDir']),
            new \Twig_SimpleFunction('lang_locale', [$this, 'langLocale']),
            new \Twig_SimpleFunction('enabled_languages', [$this, 'enabledLanguages']),
            new \Twig_SimpleFunction('date', [$this, 'date']),
            new \Twig_SimpleFunction('date_ago', [$this, 'dateAgo'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('date_diff', [$this, 'dateDiff'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('theme_option', [$this, 'getThemeSetting']),
        ];

        return $funcs;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        $filters = [
            new \Twig_SimpleFilter('date', [$this, 'date']),
        ];

        return $filters;
    }

    /**
     * @param $permission_to_check
     *
     * @return bool
     */
    public function hasPermission($permission_to_check)
    {
        $permission_manager = $this->container->get('portal_permissions_manager');

        $person = $this->getPerson();
        if ($person instanceof Person) {
            $bag = $permission_manager->getPermissionsBagForPerson($person);
        } else {
            $bag = $permission_manager->getPermissionsBagForGuest();
        }

        return $bag->hasPermission($permission_to_check);
    }

    /**
     * @param $route_name
     * @param array $vars
     *
     * @return string
     */
    public function urlFull($route_name, $vars = [])
    {
        return $this->container->get('router.default')->generate($route_name, $vars, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Check if the current user can see/use a certain feature.
     *
     * @param string $name
     *
     * @return bool
     */
    public function canUseCheck($name)
    {
        $n = strtoupper($name);

        return $this->container->get('security.authorization_checker')->isGranted('USE_'.$n);
    }

    /**
     * Check if the current user can see new ticket link.
     *
     * @return bool
     */
    public function canViewTicketsLink()
    {
        return $this->container->get('security.authorization_checker')->isGranted('VIEW_TICKETS_LINK');
    }

    /**
     * Check if the current user can rate a certain content entity.
     *
     * @param string $name
     * @param $object (Atricle/Download/Feedback/News)
     *
     * @return bool
     */
    public function canRateCheck($name, $object)
    {
        $n = strtoupper($name);

        return $this->container->get('security.authorization_checker')->isGranted('RATE_'.$n, $object);
    }

    /**
     * If a tab should be displayed or not (if enabled by admin).
     *
     * @param string $name
     *
     * @return bool
     */
    public function showTab($name)
    {
        $n = strtolower($name);

        return (bool) $this->container->get('brand_stack')->getActive()->getSetting(sprintf('user.portal_tab_%s', $n));
    }

    /**
     * @return array the order of tabs, from admin settings
     */
    public function getOrderedTabs()
    {
        return explode(',', $this->container->get('brand_stack')->getActive()->getSetting('user.portal_tabs_order'));
    }

    /**
     * Check if there is any content to show for: articles, news, downloads, feedback.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAnyCheck($name)
    {
        $sec = $this->container->get('security.authorization_checker');

        switch ($name) {
            case 'articles':
                if ($sec->isGranted('USE_ARTICLES') && $this->container->get('data.articles')->hasAny()) {
                    return true;
                }
                break;
            case 'news':
                if ($sec->isGranted('USE_NEWS') && $this->container->get('data.news')->hasAny()) {
                    return true;
                }
                break;
            case 'downloads':
                if ($sec->isGranted('USE_DOWNLOADS') && $this->container->get('data.downloads')->hasAny()) {
                    return true;
                }
                break;
            case 'feedback':
                if ($sec->isGranted('USE_FEEDBACK') && $this->container->get('data.feedback')->hasAny()) {
                    return true;
                }
                break;
            case 'guides':
                if ($sec->isGranted('USE_GUIDES') && $this->container->get('data.guides')->hasAny()) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * @return array
     */
    public function enabledLanguages()
    {
        return $this->container->get('language_manager')->getEnabledLanguages();
    }

    /**
     * @return string
     */
    public function langCode()
    {
        if (!$lang = $this->container->get('language_stack')->getActive()) {
            $lang = $this->container->get('language_stack')->getDefaultLanguage();
        }

        return $lang->getUrlCode();
    }

    /**
     * @return string
     */
    public function langLocale()
    {
        if (!$lang = $this->container->get('language_stack')->getActive()) {
            $lang = $this->container->get('language_stack')->getDefaultLanguage();
        }

        return $lang->getLocale();
    }

    /**
     * @return string
     */
    public function langDir()
    {
        if (!$lang = $this->container->get('language_stack')->getActive()) {
            $lang = $this->container->get('language_stack')->getDefaultLanguage();
        }

        return $lang->getDirection();
    }

    /**
     * @return bool
     */
    public function isMultLang()
    {
        return $this->container->get('language_manager')->isMultiLanguagePortal();
    }

    /**
     * @return string
     */
    public function baseUrl()
    {
        $portal_router = $this->container->get('router');

        // $base_url is the base URL to generate API calls to, it includes mode/language info.
        return $portal_router->generate('portal_home', []);
    }

    /**
     * @param bool $trailing_slash
     *
     * @return string
     */
    public function rootUrl($trailing_slash = true)
    {
        $portal_router = $this->container->get('router');

        // one of the rare time we use this $base_symfony_router. This is used to
        // generate the URL without the /mode/lang_code prefix appended to the base url.
        // JS uses this to generate paths to /web/images and such.
        $base_symfony_router = RouterUtils::unwrapDecoratedRouter($portal_router);

        // $root_url is the url that the root index.php lives on
        $root_url = $base_symfony_router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        if (!$trailing_slash) {
            $root_url = rtrim($root_url, '/');
        }

        return $root_url;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function noCacheUrl($url)
    {
        return $url.'?'.time();
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        return $this->container->get('security.authorization_checker')->isGranted('ROLE_USER');
    }

    /**
     * @return bool
     */
    public function isAgent()
    {
        if (!$person = $this->getPerson()) {
            return false;
        }

        return $person->is_agent && $person->can_agent;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        if (!$person = $this->getPerson()) {
            return false;
        }

        return $person->is_agent && $person->can_admin;
    }

    /**
     * @return bool
     */
    public function isImpersonating()
    {
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token instanceof AgentImpersonateToken) {
            // perhaps another twig function will want to get the impersonator agent,
            // can do that something like this:
            //$agent_id = $token->getAttribute(AgentImpersonateToken::ATTR_AGENT_IMPERSONATE);
            //$agent = $this->getPersonDataService()->getPerson($agent_id);
            return true;
        }

        return false;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person|null
     */
    private function getPerson()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return !$this->container->get('security.authorization_checker')->isGranted('ROLE_USER');
    }

    /**
     * @param string $page
     *
     * @return bool
     */
    public function pageIsCheck($page)
    {
        try {
            $r = $this->container->get('request_stack')->getMasterRequest();
        } catch (\Exception $e) {
            $r = null;
        }

        if (!$r || !($route = $r->attributes->get('_route'))) {
            return false;
        }

        switch ($page) {
            case 'home':
                return $route === 'portal_home';
            case 'kb':
                return preg_match('#^portal_kb#', $route);
            case 'news':
                return preg_match('#^portal_news#', $route);
            case 'downloads':
                return preg_match('#^portal_downloads#', $route);
            case 'feedback':
                return preg_match('#^portal_feedback#', $route);
            case 'tickets':
                return preg_match('#^portal_tickets#', $route) || $route === 'portal_new_ticket';
            case 'guides':
                return preg_match('#^portal_guides#', $route);
        }

        return false;
    }

    /**
     * Check if downloading PDF.
     *
     * @return bool
     */
    public function isPdfMode()
    {
        try {
            $r = $this->container->get('request_stack')->getMasterRequest();
        } catch (\Exception $e) {
            $r = null;
        }

        if (!$r || !($route = $r->attributes->get('_route'))) {
            return false;
        }

        // route name example portal_articles_pdf:
        return preg_match('#^portal_.+_pdf$#', $route);
    }

    /**
     * Counts the number of truthy arguments. Typically used when counting columns.
     *
     * @param mixed...
     *
     * @return int
     */
    public function countTruthy()
    {
        $args = func_get_args();

        $x = 0;
        foreach ($args as $v) {
            if ($v) {
                ++$x;
            }
        }

        return $x;
    }

    /**
     * @param $date
     * @param null      $timezone
     * @param bool|true $includeHtmlWrapper
     *
     * @return string
     */
    public function dateAgo($date, $timezone = null, $includeHtmlWrapper = true)
    {
        $date = $this->ensureDateTime($date);

        if (!$date instanceof \DateTime) {
            $date_str = (string) $date;

            return "invalid_date($date_str)";
        }

        $locale   = null;
        $language = $this->container->get('language_manager')->getLanguageStack()->getActiveOrDefault();
        if ($language) {
            $locale = $language->getLocale();
        }

        $carbon = Carbon::createFromTimestamp($date->getTimestamp(), $timezone);
        $carbon->setLocale($locale);
        $agoString = $carbon->diffForHumans();

        if ($includeHtmlWrapper) {
            // a standard <time> element, set $include_html_wrapper to false to get the raw ago string
            return sprintf(
                '<time class="date-ago" datetime="%s" title="%s">%s</time>',
                $carbon->toIso8601String(),
                $this->date($date, 'fulltime'),
                $agoString
            );
        }

        return $agoString;
    }

    /**
     * @param $date1
     * @param $date2
     *
     * @return string
     */
    public function dateDiff($date1, $date2)
    {
        $date1 = $this->ensureDateTime($date1);
        $date2 = $this->ensureDateTime($date2);

        if (!$date1 instanceof \DateTime) {
            $date_str = (string) $date1;

            return "invalid_date($date_str)";
        }

        if (!$date2 instanceof \DateTime) {
            $date_str = (string) $date1;

            return "invalid_date($date_str)";
        }

        $carbon1     = Carbon::createFromTimestamp($date1->getTimestamp());
        $carbon2     = Carbon::createFromTimestamp($date2->getTimestamp());
        $diff_string = $carbon1->diffForHumans($carbon2, true);

        return $diff_string;
    }

    /**
     * @param string|\DateTime $date
     * @param string           $format
     * @param null             $timezone
     *
     * @return string
     */
    public function date($date, $format = 'fulltime', $timezone = null)
    {
        /** @var BrandContainer $brand */
        $brand = $this->container->get('brand_stack')->getActive();
        switch ($format) {
            case 'full':
                //D, jS M Y
                $format = $brand->getSetting('core.date_full');
                break;

            case 'fulltime':
                //D, jS M Y g:ia
                $format = $brand->getSetting('core.date_fulltime');
                break;

            case 'day':
                //M j Y
                $format = $brand->getSetting('core.date_day');
                break;

            case 'day_short':
                //M j
                $format = $brand->getSetting('core.date_day_short');
                break;

            case 'time':
                //g:i a
                $format = $brand->getSetting('core.date_time');
                break;
        }

        $date = $this->ensureDateTime($date);

        if (!($date instanceof \DateTime)) {
            $date_str = (string) $date;

            return "invalid_date($date_str)";
        }

        if ($timezone === null) {
            $token  = $this->container->get('security.token_storage')->getToken();
            $person = $token ? $token->getUser() : null;

            if ($person && $person instanceof Person) {
                $timezone = $person->getTimezone();
            } else {
                try {
                    $timezone = new \DateTimeZone($brand->getSetting('core.default_timezone'));
                } catch (\Exception $e) {
                }
            }
        }
        if (is_string($timezone)) {
            try {
                $timezone = new \DateTimeZone($timezone);
            } catch (\Exception $e) {
            }
        }

        if (!$timezone) {
            $timezone = new \DateTimeZone('UTC');
        }

        $date->setTimezone($timezone);

        $translator = $this->container->get('language_manager')->getTranslator();

        return $translator->date($format, $date, 'user.time.');
    }

    /**
     * @param $date
     *
     * @return \DateTime|null
     */
    public function ensureDateTime($date)
    {
        if ($date instanceof \DateTime) {
            $date = clone $date;
        } else {
            if (ctype_digit((string) $date)) {
                $date = new \DateTime('@'.$date);
            } else {
                try {
                    $date_str = $date;
                    $date     = new \DateTime($date_str);
                } catch (\Exception $e) {
                }
            }
        }

        return $date instanceof \DateTime ? $date : null;
    }

    /**
     * @param array  $context
     * @param string $tag_name
     * @param array  $arguments
     *
     * @return string
     */
    public function processPortalPageTag($context, $tag_name, $arguments = [])
    {
        if ($context && isset($context['page']) && $context['page'] instanceof ThemeView) {
            return $context['page']->$tag_name($arguments);
        } else {
            // fallback on page-less tag
            $theme = $this->getActiveTheme();

            return $this->container->get('theme_resolver')->processTag($theme, $tag_name, $arguments);
        }
    }

    /**
     * @param array  $context
     * @param string $tag_name
     * @param array  $arguments
     *
     * @return string
     */
    public function processPortalTag($context, $tag_name, $arguments = [])
    {
        $theme = $this->getActiveTheme();

        return $this->container->get('theme_resolver')->processTag($theme, $tag_name, $arguments);
    }

    /**
     * @param string $tag_name
     *
     * @return bool
     */
    public function hasTag($tag_name)
    {
        $theme    = $this->getActiveTheme();
        $resolver = $this->container->get('theme_resolver');

        return $resolver->hasTag($theme, $tag_name);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getThemeSetting($name, $default = null)
    {
        $themeSet = $this->getActiveThemeSet();

        return $themeSet->getOption($name, $default);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface
     */
    private function getActiveTheme()
    {
        $brand_container = $this->container->getBrandStack()->getActive();
        $brand_theme     = $this->container->get('portal_brand_theme_loader')->getPortalBrandTheme($brand_container->getBrand());
        $theme           = $brand_theme->getActiveTheme();

        return $theme;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    private function getActiveThemeSet()
    {
        $brand_container = $this->container->getBrandStack()->getActive();
        $brand_theme     = $this->container->get('portal_brand_theme_loader')->getPortalBrandTheme($brand_container->getBrand());
        $themeSet        = $brand_theme->getActiveThemeSet();

        return $themeSet;
    }

    /**
     * @param string $tag_name
     *
     * @return string|null
     */
    public function getTagIncludeTemplate($tag_name)
    {
        return 'ThemeTagTemplate::'.$tag_name.'.html.twig';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'portal_support_extension';
    }
}
