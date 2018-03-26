<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use Symfony\Component\Process\ProcessBuilder;

class InstallCronCommand extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Install Schedueld Task');
        $this->writeln('You need to install the DeskPRO scheduled task that runs every minute.');
        $this->writeln('');

        $builder = new ProcessBuilder([
            $this->getSession()->getPaths()->php_path,
            $this->getContext()->getDpEnv()->getDpRoot().'/bin/cron',
        ]);
        $cmd_line = $builder->getProcess()->getCommandLine();

        $this->writeln('Your task command is:');
        $this->writeln('<info>'.$cmd_line.'</info>');

        $this->writeln('');
        $this->writeln('This command will now wait until the task has been configured and has run at least once.');
        $this->writeln('(Note that you may exit this script and resume it later if you need access to the command line using this terminal.)');
        $this->writeln('');

        if ($this->getContext()->getSession()->getSource() === 'dev'
            || $this->getContext()->getSession()->getSource() === 'buildserver'
        ) {
            $this->writeln('Dev mode. Skipping cron check.');
            $this->getContext()->getDpEnv()->getDatManager()->enableTrigger('cron_has_run');

            return;
        }

        $progress = $this->createProgressBar();
        $progress->setFormat('Waiting for cron ... [%bar%]');
        $progress->setRedrawFrequency(1);
        $progress->setBarWidth(5);

        while (!$this->isComplete()) {
            $progress->advance();
            usleep(600000);
        }

        $progress->clear();
        $this->writeln('');

        $this->writeln('Done!');
    }

    public function isComplete()
    {
        return $this->getContext()->getDpEnv()->getDatManager()->hasTrigger('cron_has_run');
    }
}
