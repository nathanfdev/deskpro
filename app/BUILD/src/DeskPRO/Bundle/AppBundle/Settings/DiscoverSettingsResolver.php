<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings;

/**
 * Class DiscoverSettingsResolver.
 */
class DiscoverSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    /**
     * @var AppEnv
     */
    private $appEnv;

    public function __construct(AppEnv $appEnv, BrandAwareSettingsResolver $settingsResolver)
    {
        parent::__construct($settingsResolver);
        $this->appEnv = $appEnv;
    }

    /**
     * @return DiscoverSettings
     */
    public function getSettings()
    {
        $helpdeskUrl      = rtrim($this->getSetting('core.deskpro_url'), '/').'/';
        $baseApiUrl       = $helpdeskUrl.'api/v2/';
        $appsHttpProxyUrl = $baseApiUrl.'apps/proxy-http';

        $isCloud           = defined('DPC_IS_CLOUD') ? DPC_IS_CLOUD : false;
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
            ->setHelpdeskUuid($this->getSetting('core.helpdesk_uuid'))
            ->setHelpdeskUrl($helpdeskUrl)
            ->setBaseApiUrl($baseApiUrl)
            ->setAppsHttpProxyUrl($appsHttpProxyUrl)
            ->setAppsOauthProxyUrl($appsOauthProxyUrl)
            ->setBuild($this->appEnv->getBuildTime())
            ->setBuildId($this->appEnv->getBuildId())
            ->setBuildName($this->appEnv->getVersionName())
        ;

        return $model;
    }
}
