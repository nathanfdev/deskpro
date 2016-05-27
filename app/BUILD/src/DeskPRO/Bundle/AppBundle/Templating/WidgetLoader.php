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
use JMS\Serializer\Serializer;

/**
 * Class WidgetLoader.
 */
class WidgetLoader
{
    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var WidgetSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param AppEnvInterface        $appEnv
     * @param WidgetSettingsResolver $settingsResolver
     * @param Serializer             $serializer
     */
    public function __construct(AppEnvInterface $appEnv, WidgetSettingsResolver $settingsResolver, Serializer $serializer)
    {
        $this->appEnv           = $appEnv;
        $this->settingsResolver = $settingsResolver;
        $this->serializer       = $serializer;
    }

    /**
     * @param bool $dynamicVersion True to make the app source file a dynamic path
     *
     * @return string
     */
    public function getWidgetCode($dynamicVersion = false)
    {
        $assetDir    = $this->appEnv->getAppWwwAssetDir();
        $urlSettings = $this->settingsResolver->getWidgetUrlSettings();
        $loaderPath  = $assetDir.'/pub/build/widget_loader.min.js';

        $appSrc = $urlSettings->getWidgetBundle();
        if ($dynamicVersion) {
            // A request that is routed through HttpJsBootTask to make the backend JS use
            // the proper build
            $appSrc = preg_replace('#/assets/.*?/pub/#', '/dyn-assets/pub/', $appSrc);
        }

        if (!file_exists($loaderPath)) {
            $loaderPath = $assetDir.'/pub/build/widget_loader.js';
        }
        if (!file_exists($loaderPath)) {
            throw new \RuntimeException('widget_loader.js does not exist');
        }

        $script = file_get_contents($loaderPath);

        // override options
        $script = str_replace('__DP_APP_SRC__', '"'.$appSrc.'"', $script);
        $script = str_replace('__DP_URL__', '"'.$urlSettings->getHelpdesk().'"', $script);
        $script = str_replace('__DP_OPTIONS__', $this->serializer->serialize($this->settingsResolver->getWidgetBrandOptions(), 'json'), $script);

        return "<!--DESKPRO_WIDGET_LOADER::BEGIN-->\n<script type=\"text/javascript\">$script</script>\n<!--DESKPRO_WIDGET_LOADER::END-->";
    }
}
