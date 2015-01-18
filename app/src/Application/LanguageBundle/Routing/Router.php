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
use Application\LanguageBundle\EventListener\LastLanguageListener;
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
        'serve_blob_sizefit',
        'serve_default_picture',
        'serve_blob',
        '_wdt',
        '_profiler'
    );

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $router;

    /**
     * @var \Application\LanguageBundle\Language\LanguageManager
     */
    private $language_manager;


    public function __construct(BaseRouter $router, LanguageManager $language_manager)
    {
        $this->router = $router;
        $this->language_manager = $language_manager;
    }


    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request)
    {
        $extractor = new UrlMatcher();
        $split = $extractor->extractLanguageCode($request->getPathInfo());
        $code = $split['lang_url_code'];

        if ('GET' !== $request->getMethod()) {
            return $this->matchNonGetRequest($request, $code, $split);
        }

        // always trust our proxy urls, never redirect them
        $path = rawurldecode($split['remaining_pathinfo']);
        if ('/_' === substr($path, 0, 2)) {
            return $this->matchNonGetRequest($request, $code, $split);
        }

        if (!$code) {
            $code = $request->get('lang_url_code', null);
        }

        if ($code) {
            $request->attributes->set('lang_url_code', $code);

            return $this->processUrlLangCode($code, $split, $request);
        }

        return $this->processNoLangCodeInUrl($request, $split);
    }


    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $generated = $this->router->generate($name, $parameters, $referenceType);

        if (!$this->language_manager->isMultiLanguagePortal()) {
            return $generated;
        }

        if (in_array($name, static::$generating_ignored_routes) || '/_' === substr($generated, 0, 2)) {
            return $generated;
        }

        $language_stack = $this->language_manager->getLanguageStack();
        if (!$language_stack->getActive()) {
            $language_stack->pushDefault();
        }

        $urlCode = $this->language_manager->getLanguageStack()->getActive()->getUrlCode();
        switch ($referenceType) {
            case self::ABSOLUTE_PATH:
                return sprintf(
                    '/%s%s',
                    $urlCode,
                    $generated
                );
            case self::ABSOLUTE_URL:
                $url = Url::createFromUrl($generated);
                $url->getPath()->prepend($urlCode);

                return (string) $url;
            default:
                throw new \InvalidArgumentException(
                    'we only support generating ABSOLUTE_PATH or ABSOLUTE_URL urls at this time, see LanguageBundle\'s Router'
                );
        }
    }

    /**
     * @param $code
     * @param $split
     * @return array
     */
    protected function processUrlLangCode($code, $split, Request $request)
    {
        if (!$this->language_manager->isMultiLanguagePortal()) {
            $this->throwRedirectExceptionTo(null, $this->makeRedirectUrl($request, $split));
        }

        if (!($url_language = $this->language_manager->getLanguage($code)) && 'GET' === $request->getMethod()) {
            // no language exists and enabled in this system
            $this->throwRedirectExceptionTo(null, $this->makeRedirectUrl($request, $split));
        }

        $this->language_manager->getLanguageStack()->push($url_language);

        return $this->standardMatch($split);
    }

    /**
     * @param  Request $request
     * @param          $split
     * @return array
     */
    protected function processNoLangCodeInUrl(Request $request, $split)
    {
        $language_stack = $this->language_manager->getLanguageStack();
        if (!$this->language_manager->isMultiLanguagePortal()) {
            $language_stack->pushDefault();

            return $this->standardMatch($split);
        }

        // now we know its a multi lang desk and there was no long code, so we need to decide about where to redirect:
        $this->pushDetectedLanguageToStack($request);

        if ('GET' === $request->getMethod()) {
            $this->throwRedirectExceptionTo($language_stack->getActive(), $this->makeRedirectUrl($request, $split));
        }

        return $this->standardMatch($split);
    }

    /**
     * Given only a request object, detect the language that this user probably is
     * This is used for non-GET requests to set the lang (if not provided by
     *
     * @param Request $request
     */
    public function pushDetectedLanguageToStack(Request $request)
    {
        $language_stack = $this->language_manager->getLanguageStack();

        if ($language = $this->getLastLangFromCookie($request)) {
            $language_stack->push($language);
        } else {
            // 2. authorized persons preference from Person
            // 3. failing that, negotiate with http.lang
            // and finally if we can't find anything:
            $language_stack->pushDefault();
        }
    }

    /**
     * Throw an exception that will be caught by our kernel.exception listener
     *
     * @param  Language               $language
     * @param                         $url
     * @throws RedirectToUrlException
     */
    protected function throwRedirectExceptionTo(Language $language = null, $url)
    {
        $pre = $language ? '/'.$language->getTwoLetterLanguageCode() : '';
        $url = $pre.$url;
        throw new RedirectToUrlException($url);
    }

    /**
     * @param $split
     * @return array
     */
    protected function standardMatch($split)
    {
        return $this->router->match($split['remaining_pathinfo']);
    }

    /**
     * @param  Request       $request
     * @return Language|null
     */
    protected function getLastLangFromCookie(Request $request)
    {
        if ($lang_code = $request->cookies->get(LastLanguageListener::COOKIE_NAME)) {
            return $this->language_manager->getLanguage($lang_code);
        }

        return null;
    }

    /**
     * @param  Request $request
     * @param          $code
     * @param          $split
     * @return array
     */
    protected function matchNonGetRequest(Request $request, $code, $split)
    {
        if (!$url_language = $this->language_manager->getLanguage($code)) {
            $this->pushDetectedLanguageToStack($request);
        } else {
            $this->language_manager->getLanguageStack()->push($url_language);
        }

        return $this->standardMatch($split);
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

    /**
     * @param Request $request
     * @param $split
     * @return string
     */
    protected function makeRedirectUrl(Request $request, $split)
    {
        $qs = $request->getQueryString();
        $redirect_url = $split['remaining_pathinfo'];
        if ($qs) {
            $redirect_url .= '?' . $qs;
        }
        return $redirect_url;
    }

}
