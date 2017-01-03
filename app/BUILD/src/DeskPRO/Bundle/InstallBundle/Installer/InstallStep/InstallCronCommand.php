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
