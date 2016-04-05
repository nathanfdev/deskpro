<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Templating;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WidgetLoader.
 *
 * @JMS\ExclusionPolicy("all")
 */
class WidgetLoader
{
    private $assetDir;
    private $widgetSettings;
    private $deskproUrl;
    private $widgetJsUrl;

    /**
     * WidgetLoaderGenerator constructor.
     *
     * @param string $assetDir
     * @param array  $widgetSettings
     * @param string $deskproUrl
     * @param string $widgetJsUrl
     */
    public function __construct($assetDir, array $widgetSettings, $deskproUrl, $widgetJsUrl)
    {
        $this->assetDir       = $assetDir;
        $this->widgetSettings = $widgetSettings;
        $this->deskproUrl     = $deskproUrl;
        $this->widgetJsUrl    = $widgetJsUrl;
    }

    /**
     * @param AppEnvInterface        $appEnv
     * @param WidgetSettingsResolver $widgetSettingsResolver
     * @param Request                $request
     * @param Packages               $assetPackages
     *
     * @return WidgetLoader
     */
    public static function createPortalLoader(AppEnvInterface $appEnv, WidgetSettingsResolver $widgetSettingsResolver, Request $request, Packages $assetPackages)
    {
        $widgetSettings = array_merge(
            $widgetSettingsResolver->getPortalBrandSettings(),
            ['company' => $widgetSettingsResolver->getCompanySettings()]
        );

        return new self(
            $appEnv->getAppWwwAssetDir(),
            $widgetSettings,
            $request->getUriForPath(''),
            $assetPackages->getUrl('DeskPRO_WidgetBundle.js', 'app_assets')
        );
    }

    /**
     * @param AppEnvInterface $appEnv
     * @param array           $widgetSettings
     * @param string          $deskproUrl
     * @param Packages        $assetPackages
     *
     * @return WidgetLoader
     */
    public static function createLoader(AppEnvInterface $appEnv, array $widgetSettings, $deskproUrl, Packages $assetPackages)
    {
        $widgetJsUrl = $assetPackages->getUrl('DeskPRO_WidgetBundle.js', 'app_assets');
        if (!preg_match('#^https?://#i', $widgetJsUrl)) {
            $widgetJsUrl = $deskproUrl.'/'.$widgetJsUrl;
        }

        return new self(
            $appEnv->getAppWwwAssetDir(),
            $widgetSettings,
            $deskproUrl,
            $widgetJsUrl
        );
    }

    /**
     * Get just the raw JS for the loader.
     *
     * @return string
     */
    public function getJs()
    {
        $loaderPath = $this->assetDir.'/pub/build/widget_loader.min.js';
        if (!file_exists($loaderPath)) {
            $loaderPath = $this->assetDir.'/pub/build/widget_loader.js';
        }
        if (!file_exists($loaderPath)) {
            throw new \RuntimeException('widget_loader.js does not exist');
        }

        $script = file_get_contents($loaderPath);

        // override options
        $script = str_replace('__DP_APP_SRC__', '"'.$this->widgetJsUrl.'"', $script);
        $script = str_replace('__DP_URL__', '"'.$this->deskproUrl.'"', $script);
        $script = str_replace('__DP_OPTIONS__', json_encode($this->widgetSettings), $script);

        return $script;
    }

    /**
     * Get the script tag.
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("script_html")
     *
     * @return string
     */
    public function getScriptHtml()
    {
        $js = $this->getJs();

        return "<!--DESKPRO_WIDGET_LOADER::BEGIN-->\n<script type=\"text/javascript\">$js</script>\n<!--DESKPRO_WIDGET_LOADER::END-->";
    }
}
