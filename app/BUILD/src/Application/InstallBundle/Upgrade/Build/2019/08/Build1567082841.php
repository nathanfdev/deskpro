<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1567082841 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $enabled = $this->readSetting('elastica.enabled');
        $isCloud = defined('DPC_IS_CLOUD');
        if ($enabled && !$isCloud) {
            $this->out('Elasticsearch is enabled and it is not Cloud account. We are marking it to reset');
            $this->saveSetting('elastica.requires_reset', true);
        }
    }
}
