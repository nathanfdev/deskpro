<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1495206448 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $hostChecker = $this->container->get('url_host_checker');
        $deskproUrl  = $hostChecker->simplifyUrl($this->readSetting('core.deskpro_url'), true);
        $siteUrl     = $hostChecker->simplifyUrl($this->readSetting('core.site_url'), true);

        $this->saveSetting('core.deskpro_url', rtrim($deskproUrl, '/').'/');
        $this->saveSetting('core.site_url', rtrim($siteUrl, '/').'/');
    }
}
