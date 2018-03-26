<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use League\Url\Url;
use Symfony\Component\Routing\RouterInterface;

class LanguageChanger
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var LanguageManager
     */
    private $lang_manager;

    /**
     * @var PortalModeStorage
     */
    private $mode_storage;

    public function __construct(
        RouterInterface $router,
        LanguageManager $lang_manager,
        PortalModeStorage $mode_storage
    ) {
        $this->router       = $router;
        $this->lang_manager = $lang_manager;
        $this->mode_storage = $mode_storage;
    }

    public function changeLanguage($new_lang_code, $http_referer)
    {
        $router           = $this->router;
        $language_manager = $this->lang_manager;
        $language_stack   = $language_manager->getLanguageStack();
        $mode             = $this->mode_storage->getMode();
        $isMode           = $mode && strlen(trim($mode->getModePath(), '/')) > 0;

        $referer_or_home = function () use ($http_referer, $router) {
            if (!$http_referer) {
                return $router->generate('portal_home');
            }

            return $http_referer;
        };

        // must be a multi lang portal
        if (!$language_manager->isMultiLanguagePortal()) {
            return $referer_or_home();
        }

        // must be supported
        if (!$new_lang_code || !$language_manager->isLanguageSupported($new_lang_code)) {
            return $referer_or_home();
        }

        // get the new and old lang objects
        $new_lang = $language_manager->getLanguage($new_lang_code);
        $old_lang = $language_stack->getActive();
        $language_stack->push($new_lang);

        if (!$http_referer) {
            return $this->router->generate('portal_home');
        }

        // replace the language path from the referer with the new lang
        $url        = Url::createFromUrl($http_referer);
        $path       = $url->getPath();
        $path_array = $path->toArray();
        if ($isMode) {
            array_shift($path_array);
        }
        array_shift($path_array);
        array_unshift($path_array, $new_lang->getUrlCode());
        if ($isMode) {
            array_unshift($path_array, trim($mode->getModePath(), '/'));
        }
        $url->setPath($path_array);

        return (string) $url;
    }
}
