<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings;

/**
 * Class DiscoverSettingsResolver.
 */
class DiscoverSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    /**
     * @return DiscoverSettings
     */
    public function getSettings()
    {
        $helpdeskUrl = rtrim($this->getSetting('core.deskpro_url'), '/').'/';
        $baseApiUrl = $helpdeskUrl.'api/v2/';
        $appsHttpProxyUrl = $baseApiUrl.'apps/proxy-http';


        $isCloud = defined('DPC_IS_CLOUD') ? DPC_IS_CLOUD : false;
        $appsOauthProxyUrl = $this->getSetting('apps.oauth_proxy_url');

        if (empty($appsOauthProxyUrl)) {
            $appsOauthProxyUrl = $baseApiUrl.'apps/proxy-oauth';
        } else {
            $appsOauthProxyUrl = rtrim($appsOauthProxyUrl, '/');
        }


        $model = new DiscoverSettings();
        $model
            ->setIsDeskpro(true)
            ->setIsCloud($isCloud)
            ->setHelpdeskUrl($helpdeskUrl)
            ->setBaseApiUrl($baseApiUrl)
            ->setAppsHttpProxyUrl($appsHttpProxyUrl)
            ->setAppsOauthProxyUrl($appsOauthProxyUrl)
            ->setBuild(DP_BUILD_TIME)
        ;

        return $model;
    }
}
