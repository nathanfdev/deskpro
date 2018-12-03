<?php

namespace DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\BrandBundle\Request\RequestBrandCorrector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LanguageChanger.
 */
class LanguageChanger
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    /**
     * @var LanguageManager
     */
    private $langManager;

    /**
     * @var RequestBrandCorrector
     */
    private $requestBrandCorrector;

    /**
     * Constructor.
     *
     * @param RouterInterface         $router
     * @param RequestMatcherInterface $requestMatcher
     * @param LanguageManager         $langManager
     * @param RequestBrandCorrector   $requestBrandCorrector
     */
    public function __construct(
        RouterInterface         $router,
        RequestMatcherInterface $requestMatcher,
        LanguageManager         $langManager,
        RequestBrandCorrector   $requestBrandCorrector
    ) {
        $this->router                = $router;
        $this->requestMatcher        = $requestMatcher;
        $this->langManager           = $langManager;
        $this->requestBrandCorrector = $requestBrandCorrector;
    }

    /**
     * @param string $newLangCode
     * @param string $httpReferer
     *
     * @return string
     */
    public function changeLanguage($newLangCode, $httpReferer)
    {
        if (!$httpReferer) {
            $httpReferer = $this->router->generate('portal_home');
        }

        // must be a multi lang portal
        if (!$this->langManager->isMultiLanguagePortal()) {
            return $httpReferer;
        }

        // must be supported
        if (!$newLangCode || !$this->langManager->isLanguageSupported($newLangCode)) {
            return $httpReferer;
        }

        $refererRequest = Request::create($httpReferer);
        $this->requestBrandCorrector->patchRequest($refererRequest);

        $match = $this->requestMatcher->matchRequest($refererRequest);
        if (!isset($match['_route'])) {
            return $httpReferer;
        }

        // get the new and old lang objects
        $newLang = $this->langManager->getLanguage($newLangCode);
        $this->langManager->getLanguageStack()->push($newLang);

        $route = $match['_route'];
        unset($match['_route']);

        // replace the language path from the referer with the new lang
        $newReferer = $this->router->generate($route, $match);
        $this->langManager->getLanguageStack()->pop();

        return $newReferer;
    }
}
