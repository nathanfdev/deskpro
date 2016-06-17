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

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;

/**
 * This is the settings resolver we should use (app-wide) moving forward.
 *
 * It will get brand settings by default, when available, but can gracefully fall back to
 * global settings in the case that:
 *
 * 1. No BrandStack is available in the container, or
 * 2. There are no brands configured in the BrandStack
 *
 * There are no disadvantages to using this service to get settings, and the advantage is that your
 * code will work with the new brand settings as we increase the use of brands in the code base.
 */
class BrandAwareSettingsResolver
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settings_resolver
     * @param BrandStack|null  $brand_stack
     */
    public function __construct(SettingsResolver $settings_resolver, BrandStack $brand_stack = null)
    {
        $this->brand_stack       = $brand_stack;
        $this->settings_resolver = $settings_resolver;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSetting($name, $default = null)
    {
        if ($this->brand_stack && $this->brand_stack->getActive()) {
            if ($default === null) {
                $default = $this->getGlobalSetting($name);
            }

            return $this->getBrandSetting($name, $default);
        }

        return $this->getGlobalSetting($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getBrandSetting($name, $default = null)
    {
        if (!$brand_container = $this->brand_stack->getActive()) {
            throw new \RuntimeException(
                'tried to access the active brand from the brand_stack but there is no active brand configured
            ');
        }

        return $brand_container->getSetting($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getGlobalSetting($name, $default = null)
    {
        return $this->settings_resolver->getGlobalSettings()->get($name, $default);
    }
}
