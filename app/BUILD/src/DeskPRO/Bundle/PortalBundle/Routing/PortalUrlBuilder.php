<?php

namespace DeskPRO\Bundle\PortalBundle\Routing;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\PortalBundle\Mode\PortalMode;

/**
 * Class PortalUrlBuilder.
 */
class PortalUrlBuilder
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var Language
     */
    private $language;

    /**
     * @var PortalMode
     */
    private $mode;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Constructor.
     *
     * @param string          $path
     * @param Language|null   $language
     * @param PortalMode|null $mode
     * @param string          $baseUrl
     */
    public function __construct($path, Language $language = null, PortalMode $mode = null, $baseUrl = '')
    {
        $this->path     = trim($path);
        $this->language = $language;
        $this->mode     = $mode;
        $this->baseUrl  = $baseUrl;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $parts = [];
        $path  = $this->path;

        // remove base url from the url path to set it in the proper order
        if ($this->baseUrl && strpos($path, $this->baseUrl) === 0) {
            $path    = substr($path, strlen($this->baseUrl));
            $parts[] = trim($this->baseUrl, '/');
        }

        if (!preg_match('#^/?(?:agent|admin|reports)(/|$)#', $path)) {
            if ($this->mode) {
                $mode_path = trim($this->mode->getModePath(), '/');
                if (strlen($mode_path) > 0) {
                    $parts[] = $mode_path;
                }
            }

            if ($this->language) {
                $lang_part = trim($this->language->getUrlCode(), '/');
                if (strlen($lang_part) > 0) {
                    $parts[] = $lang_part;
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
