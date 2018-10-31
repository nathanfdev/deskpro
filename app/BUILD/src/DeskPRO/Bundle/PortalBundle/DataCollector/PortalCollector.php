<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\DataCollector;

use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class PortalCollector extends DataCollector
{
    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    private $brand_stack;
    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageStack
     */
    private $language_stack;

    /**
     * @var PortalModeStorage
     */
    private $mode_storage;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brand_theme_loader;

    public function __construct(BrandStack $brand_stack, PortalBrandThemeLoader $brand_theme_loader, LanguageStack $language_stack, PortalModeStorage $mode_storage)
    {
        $this->brand_stack        = $brand_stack;
        $this->brand_theme_loader = $brand_theme_loader;
        $this->language_stack     = $language_stack;
        $this->mode_storage       = $mode_storage;
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $brandContainer = $this->brand_stack->getActive();
        $language       = $this->language_stack->getActive();
        $mode           = $this->mode_storage->getMode();

        if ($brandContainer) {
            $brand = $brandContainer->getBrand();
            $theme = $this->brand_theme_loader->getPortalBrandTheme($brand)->getActiveTheme();
        } else {
            $brand = null;
            $theme = null;
        }

        $this->data = [
            'route_name'          => $request->attributes->get('_route'),
            'executed_controller' => $request->attributes->get('_controller'),
            'brand_id'            => $brand ? $brand->id : 'N/A',
            'brand_name'          => $brand ? $brand->name : 'N/A',
            'theme_id'            => $theme ? $theme->getId() : 'N/A',
            'theme_name'          => $theme ? $theme->getName() : 'N/A',
            'language_code'       => $language ? $language->getUrlCode() : 'N/A',
            'language_id'         => $language ? $language->getId() : 'N/A',
            'language_img'        => $language ? $language->flag_image : null,
            'mode'                => (string) $mode,
            'settings'            => $brandContainer ? $brandContainer->getSettings()->toArray() : null,
        ];
    }

    public function getMode()
    {
        return $this->data['mode'];
    }

    public function getLanguageCode()
    {
        return $this->data['language_code'];
    }

    public function getLanguageImage()
    {
        return $this->data['language_img'];
    }

    public function getLanguageId()
    {
        return $this->data['language_id'];
    }

    public function getRoute()
    {
        return $this->data['route_name'];
    }

    public function getController()
    {
        return $this->data['executed_controller'];
    }

    public function getSettings()
    {
        return $this->data['settings'];
    }

    public function getThemeId()
    {
        return $this->data['theme_id'];
    }

    public function getThemeName()
    {
        return $this->data['theme_name'];
    }

    public function getBrandId()
    {
        return $this->data['brand_id'];
    }

    public function getBrandName()
    {
        return $this->data['brand_name'];
    }

    public function getName()
    {
        return 'portal';
    }
}
