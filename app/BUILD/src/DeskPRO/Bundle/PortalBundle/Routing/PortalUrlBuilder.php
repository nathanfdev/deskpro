<?php

namespace DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PortalUrlBuilder.
 */
class PortalUrlBuilder
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var PortalModeStorage
     */
    private $modeStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param LanguageManager   $languageManager
     * @param PortalModeStorage $modeStorage
     * @param RouterInterface   $router
     */
    public function __construct(LanguageManager $languageManager, PortalModeStorage $modeStorage, RouterInterface $router)
    {
        $this->languageManager = $languageManager;
        $this->modeStorage     = $modeStorage;
        $this->router          = $router;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function buildUrl($path)
    {
        $mode     = $this->modeStorage->getMode();
        $path     = trim($path);
        $baseUrl  = $this->router->getContext()->getBaseUrl();
        $language = $this->languageManager->isMultiLanguagePortal() ? $this->languageManager->getLanguageStack()->getActive() : null;

        $parts = [];
        // remove base url from the url path to set it in the proper order
        if ($baseUrl) {
            if (strpos($path, $baseUrl) === 0) {
                $path = substr($path, strlen($baseUrl));
            }
            if (strpos($path, ltrim($baseUrl, '/')) === 0) {
                $path = substr($path, strlen(ltrim($baseUrl, '/')));
            }

            $parts[] = trim($baseUrl, '/');
        }

        if (!preg_match('#^/?(?:agent|admin|reports)(/|$)#', $path)) {
            // ignore brand mode, we set it in base url
            if ($mode) {
                $modePath = trim($mode->getModePath(), '/');
                if (strlen($modePath) > 0) {
                    $parts[] = $modePath;
                }
            }

            if ($language) {
                $langPart = trim($language->getUrlCode(), '/');
                if (strlen($langPart) > 0) {
                    $parts[] = $langPart;
                }
            }
        }

        if ((string) $path !== '/') {
            $pathPart = trim($path, '/');
            if (strlen($pathPart) > 0) {
                $parts[] = $pathPart;
            }
        }

        return '/'.implode('/', $parts);
    }
}
