<?php

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
