<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

class DoneStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Done');
        $this->writeln('');
        $this->writeln('DeskPRO has been installed successfully.');
        $this->writeln('');

        $this->getSession()->enableFlag('installer_done');
    }

    public function isComplete()
    {
        return false;
    }
}
