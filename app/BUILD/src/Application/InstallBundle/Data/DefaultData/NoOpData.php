<?php

namespace Application\InstallBundle\Data\DefaultData;

/**
 * Special no-op class.
 */
class NoOpData extends AbstractDefaultData
{
    public function runInstall()
    {
        $this->getLogger()->info('NoOpData');
    }

    public function runInstallViaUpgrade()
    {
        $this->getLogger()->info('NoOpData');
    }

    public function runSync()
    {
        $this->getLogger()->info('NoOpData');
    }

    public function runReset()
    {
        $this->getLogger()->info('NoOpData');
    }
}
