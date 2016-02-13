<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DpTestSrc\TestBundle\NewSettings;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsLoaderInterface;
use Application\DeskPRO\NewSettings\SettingsResolver as BaseResolver;

class TestSettingsResolver extends BaseResolver
{
    protected $test_set_settings;

    public function __construct(
        array $loaders,
        CacheAdapterInterface $cache,
        SettingsLoaderInterface $brand_settings_loader
    ) {
        parent::__construct($loaders, $cache, $brand_settings_loader);
        $this->test_set_settings = [];
    }

    public function setSetting($name, $value)
    {
        $this->test_set_settings[$name] = $value;
    }

    protected function wrapSettingsBag(SettingsBag $bag)
    {
        $settings = new TestSettingsBag($bag->toArray());
        foreach ($this->test_set_settings as $setting => $name) {
            $settings->set($setting, $name);
        }

        return $settings;
    }

    public function getGlobalSettings($force = false)
    {
        return $this->wrapSettingsBag(parent::getGlobalSettings($force));
    }

    public function getBrandSettings($brand_id, $force = false)
    {
        return $this->wrapSettingsBag(parent::getBrandSettings($brand_id, $force));
    }

    public function getDefaultSettings($force = false)
    {
        return $this->wrapSettingsBag(parent::getDefaultSettings($force));
    }
}
