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
namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeView;
use League\Url\Url;
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
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @param ContainerInterface $continer
     */
    public function __construct(ContainerInterface $continer)
    {
        $this->container   = $continer;
        $this->brand_stack = $continer->get('brand_stack');
    }

    public function getTokenParsers()
    {
        $token_parsers = array(
            new TokenParser\ShowParser($this),
            new TokenParser\GroupParser($this),
            new TokenParser\GroupItemParser($this),
        );

        return $token_parsers;
    }

    public function getNodeVisitors()
    {
        $visitors = array(
            new NodeVisitor\GroupVisitor(),
        );

        return $visitors;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        $funcs = array(
            new \Twig_SimpleFunction('can_use_*', array($this, 'canUseCheck')),
            new \Twig_SimpleFunction('has_any_*', array($this, 'hasAnyCheck')),
            new \Twig_SimpleFunction('is_user', array($this, 'isUser')),
            new \Twig_SimpleFunction('is_agent', array($this, 'isAgent')),
            new \Twig_SimpleFunction('is_admin', array($this, 'isAdmin')),
            new \Twig_SimpleFunction('is_impersonating', array($this, 'isImpersonating')),
            new \Twig_SimpleFunction('is_guest', array($this, 'isGuest')),
            new \Twig_SimpleFunction('is_page_*', array($this, 'pageIsCheck')),
            new \Twig_SimpleFunction('col_count', array($this, 'countTruthy')),
            new \Twig_SimpleFunction('has_permission', array($this, 'hasPermission')),
            new \Twig_SimpleFunction('url_full', array($this, 'urlFull')),
            new \Twig_SimpleFunction('base_url', array($this, 'baseUrl')),
            new \Twig_SimpleFunction('root_url', array($this, 'rootUrl')),
            new \Twig_SimpleFunction('is_multi_lang', array($this, 'isMultLang')),
            new \Twig_SimpleFunction('lang_code', array($this, 'langCode')),
            new \Twig_SimpleFunction('enabled_languages', array($this, 'enabledLanguages')),
            new \Twig_SimpleFunction('date', array($this, 'date')),
        );

        return $funcs;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('date', array($this, 'date')),
        );

        return $filters;
    }

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

    public function urlFull($route_name, $vars = array())
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
        }

        return false;
    }

    public function enabledLanguages()
    {
        return $this->container->get('language_manager')->getEnabledLanguages();
    }

    public function langCode()
    {
        if (!$lang = $this->container->get('language_stack')->getActive()) {
            $lang = $this->container->get('language_stack')->getDefaultLanguage();
        }

        return $lang->getTwoLetterLanguageCode();
    }

    public function isMultLang()
    {
        return $this->container->get('language_manager')->isMultiLanguagePortal();
    }

    public function baseUrl()
    {
        $portal_router = $this->container->get('router');

        // $base_url is the base URL to generate API calls to, it includes mode/language info.
        $base_url = $portal_router->generate(
            'portal_home',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $base_url;
    }

    public function rootUrl()
    {
        $portal_router = $this->container->get('router');

        // one of the rare time we use this $base_symfony_router. This is used to
        // generate the URL without the /mode/lang_code prefix appended to the base url.
        // JS uses this to generate paths to /web/images and such.
        $base_symfony_router = $portal_router->getBaseRouter();

        // $root_url is the url that the root index.php lives on
        $root_url = $base_symfony_router->generate(
            'portal_home',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $root_url;
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
        if ($token = $this->container->get('security.token_storage')->getToken()) {
            if ($token instanceof AgentImpersonateToken) {
                // perhaps another twig function will want to get the impersonator agent,
                // can do that something like this:
                //$agent_id = $token->getAttribute(AgentImpersonateToken::ATTR_AGENT_IMPERSONATE);
                //$agent = $this->getPersonDataService()->getPerson($agent_id);
                return true;
            }
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
        }

        return false;
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
     * @param string|\DateTime $date
     * @param string           $format
     * @param null             $timezone
     *
     * @return string
     */
    public function date($date, $format = 'fulltime', $timezone = null)
    {
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

        if (!($date instanceof \DateTime)) {
            $date_str = (string) $date;

            return "invalid_date($date_str)";
        }

        if ($timezone === null) {
            $person = $this->container->get('security.token_storage')->getToken()->getUser();
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
            };
        }

        if (!$timezone) {
            $timezone = new \DateTimeZone('UTC');
        }

        $date->setTimezone($timezone);

        return $date->format($format);
    }

    /**
     * @param array  $context
     * @param string $tag_name
     * @param array  $arguments
     *
     * @return string
     */
    public function processPortalPageTag($context, $tag_name, $arguments = array())
    {
        if ($context && isset($context['page']) && $context['page'] instanceof ThemeView) {
            return $context['page']->$tag_name($arguments);
        } else {
            // fallback on page-less tag
            return $this->brand_stack->getActive()->renderTag($tag_name, $arguments);
        }
    }

    /**
     * @param array  $context
     * @param string $tag_name
     * @param array  $arguments
     *
     * @return string
     */
    public function processPortalTag($context, $tag_name, $arguments = array())
    {
        return $this->brand_stack->getActive()->renderTag($tag_name, $arguments);
    }

    /**
     * @param string $tag_name
     *
     * @return bool
     */
    public function hasTag($tag_name)
    {
        $theme    = $this->brand_stack->getActive()->getTheme();
        $resolver = $this->container->get('theme_resolver');

        return $resolver->hasTag($theme, $tag_name);
    }

    /**
     * @param string $tag_name
     *
     * @return string|null
     */
    public function getTagIncludeTemplate($tag_name)
    {
        $theme    = $this->brand_stack->getActive()->getTheme();
        $resolver = $this->container->get('theme_resolver');

        return $resolver->templatePath($theme, 'ThemeTagTemplate::'.$tag_name.'.html.twig');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'portal_support_extension';
    }
}
