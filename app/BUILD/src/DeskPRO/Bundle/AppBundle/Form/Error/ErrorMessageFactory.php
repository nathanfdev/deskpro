<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use Symfony\Component\Form\FormError;

/**
 * Class ErrorMessageFactory.
 */
class ErrorMessageFactory
{
    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var PortalBrandThemeLoader
     */
    private $portalBrandThemeLoader;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param Translate $translate
     * @param string $prefix
     * @param PortalBrandThemeLoader $portalBrandThemeLoader
     * @param BrandStack $brandStack
     */
    public function __construct(
        Translate $translate,
        $prefix,
        PortalBrandThemeLoader $portalBrandThemeLoader = null,
        BrandStack $brandStack = null
    ) {
        $this->portalBrandThemeLoader  = $portalBrandThemeLoader;
        $this->brandStack              = $brandStack;
        $this->translate               = $translate;

        if ($this->isHelpcenter()) {
            $this->prefix    = str_replace('portal.', 'helpcenter.', $prefix);
        } else {
            $this->prefix    = $prefix;
        }
    }

    /**
     * @param string $errorCode
     * @param array  $params
     *
     * @return string
     */
    public function createMessage($errorCode, array $params = [])
    {
        return $this->translate->phrase($this->prefix.$errorCode, $params) ?: $errorCode;
    }

    /**
     * @param string    $errorCode
     * @param FormError $formError
     *
     * @return string
     */
    public function createFormErrorMessage($errorCode, FormError $formError)
    {
        return $this->createMessage($errorCode, $this->parseParams($formError->getMessageParameters()));
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function parseParams(array $array = [])
    {
        $new = [];
        foreach ($array as $key => $val) {
            preg_match('#\{\{\s*([a-zA-Z0-9_]+)\s*\}\}#', $key, $matches);
            if (isset($matches[1])) {
                $key = $matches[1];
            }

            $new[$key] = $val;
        }

        return $new;
    }

    private function isHelpcenter()
    {
        if ($this->portalBrandThemeLoader) {
            return $this->portalBrandThemeLoader->getPortalBrandTheme($this->brandStack->getActive()->getBrand())->getActiveThemeSet()->getThemeId() === 'helpcenter';
        }

        return false;
    }
}
