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
     * @param bool $withOptions
     *
     * @return string
     */
    public function getWidgetCode($withOptions = false)
    {
        $urlSettings = $this->settingsResolver->getWidgetUrlSettings();

        $loaderSrc = $urlSettings->getWidgetLoader();
        $loaderSrc = preg_replace('#/assets/.*?/pub/#', '/dyn-assets/pub/', $loaderSrc);
        $loaderSrc = preg_replace('#\?.*?$#', '', $loaderSrc);

        $assetsUrl = str_replace('/pub/build/widget_loader.min.js', '', $loaderSrc);
        $assetsUrl = rtrim($assetsUrl, '/');

        if ($withOptions) {
            $options = array_merge($this->serializer->toArray($this->settingsResolver->getWidgetBrandOptions()), [
                'noFetchOptions' => true,
            ]);
        } else {
            $options = [];
        }

        $options['helpdeskUrl'] = $urlSettings->getHelpdesk();

        $encodedOptions = json_encode($options, \JSON_PRETTY_PRINT);

        $code   = [];
        $code[] = '<!--DESKPRO_WIDGET_LOADER::BEGIN-->';
        $code[] = "<script type=\"text/javascript\">\nwindow.DESKPRO_WIDGET_OPTIONS = $encodedOptions;\n</script>";

        if ($this->appEnv->getEnvId() === 'dev') {
            $code[] = "<script type=\"text/javascript\">\nwindow.DESKPRO_ASSETS_URL = '$assetsUrl';\n</script>";
        }

        $code[] = '<script type="text/javascript" id="dp-widget-loader" src="'.$loaderSrc.'"></script>';
        $code[] = '<!--DESKPRO_WIDGET_LOADER::END-->';

        return implode("\n", $code);
    }
}
