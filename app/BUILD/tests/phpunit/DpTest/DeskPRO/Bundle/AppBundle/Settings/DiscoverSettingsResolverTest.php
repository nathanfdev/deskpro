<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\AppBundle\Settings;

use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\DiscoverSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings;
use DpTest\ApiTestCase;

class DiscoverSettingsResolverTest extends ApiTestCase
{
    public static function setUpBeforeClass()
    {
        if (!defined('DP_BUILD_TIME')) {
            define('DP_BUILD_TIME', 1);
        }
//        $isCloud = defined('DPC_IS_CLOUD') ? DPC_IS_CLOUD : false;
//        if (!defined('DPC_IS_CLOUD')) {
//            define('DPC_IS_CLOUD', false);
//        }
    }

    public function testGetSettingsReturnsDefaultSettings()
    {
        $expectedSettings = new DiscoverSettings();
        $expectedSettings
            ->setIsDeskpro(true)
            ->setIsCloud(false)
            ->setHelpdeskUrl('https://www.deskpro-dev.co.uk/')
            ->setBaseApiUrl('https://www.deskpro-dev.co.uk/api/v2/')
            ->setAppsHttpProxyUrl('https://www.deskpro-dev.co.uk/api/v2/apps/proxy-http')
            ->setAppsOauthProxyUrl('https://www.deskpro-dev.co.uk/api/v2/apps/proxy-oauth')
            ->setBuild(1)
        ;

        $settingsResolver = $this->getMockBuilder(BrandAwareSettingsResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSetting'])
            ->getMock()
        ;
        $settingsResolver->expects($this->at(0))->method('getSetting')->with('core.deskpro_url')->willReturn('https://www.deskpro-dev.co.uk/');
        $settingsResolver->expects($this->at(1))->method('getSetting')->with('apps.oauth_proxy_url')->willReturn(null);


        $discoverResolver = new DiscoverSettingsResolver($settingsResolver);
        $actualSettings  = $discoverResolver->getSettings();
        if (DP_BUILD_TIME !== 1) {
            $actualSettings->setBuild(1);
        }

        $serializer = $this->getContainer()->get('jms_serializer');
        $serializedActualSettings = $serializer->serialize($actualSettings, 'json');
        $serializedExpectedSettings = $serializer->serialize($expectedSettings, 'json');

        $this->assertEquals($serializedExpectedSettings, $serializedActualSettings, 'unexpected default discovery settings');
    }

    public function testGetSettingsReturnsCustomSettings()
    {
        $expectedSettings = new DiscoverSettings();
        $expectedSettings
            ->setIsDeskpro(true)
            ->setIsCloud(false)
            ->setHelpdeskUrl('https://www.deskpro-dev.co.uk/')
            ->setBaseApiUrl('https://www.deskpro-dev.co.uk/api/v2/')
            ->setAppsHttpProxyUrl('https://www.deskpro-dev.co.uk/api/v2/apps/proxy-http')
            ->setAppsOauthProxyUrl('https://oauth.deskpro.com')
            ->setBuild(1)
        ;

        $settingsResolver = $this->getMockBuilder(BrandAwareSettingsResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSetting'])
            ->getMock()
        ;

        $settingsResolver
            ->expects($this->at(0))
            ->method('getSetting')
            ->with('core.deskpro_url')
            ->willReturn('https://www.deskpro-dev.co.uk/');

        $settingsResolver->expects($this->at(1))->method('getSetting')
            ->with('apps.oauth_proxy_url')
            ->willReturn('https://oauth.deskpro.com')
        ;

        $discoverResolver = new DiscoverSettingsResolver($settingsResolver);
        $actualSettings  = $discoverResolver->getSettings();
        if (DP_BUILD_TIME !== 1) {
            $actualSettings->setBuild(1);
        }


        $serializer = $this->getContainer()->get('jms_serializer');
        $serializedActualSettings = $serializer->serialize($actualSettings, 'json');
        $serializedExpectedSettings = $serializer->serialize($expectedSettings, 'json');

        $this->assertEquals($serializedExpectedSettings, $serializedActualSettings, 'unexpected default discovery settings');
    }
}
