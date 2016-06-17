<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use Symfony\Component\Process\ProcessBuilder;

class DataSyncStep extends AbstractStep
{
    public function run()
    {
        ini_set('memory_limit', '512M');

        $this->writeBigTitle('Syncing data');

        $this->writeln('We will now sync additional data');

        $is_dev = $this->getSession()->getSource() === InstallSession::SOURCE_DEV
            || $this->getSession()->getSource() === InstallSession::SOURCE_BUILDSERVER;

        $builder = new ProcessBuilder([
            $this->getSession()->getPaths()->php_path,
            $this->getContext()->getDpEnv()->getDpRoot().'/bin/console',
            'dp:sync-data',
            '--sync-all',
        ]);

        $builder->setTimeout(10 * 60);

        $progress = $this->createProgressBar();
        $progress->setFormat('Syncing ... [%bar%]');
        $progress->setRedrawFrequency(1);
        $progress->setBarWidth(5);

        $proc = $builder->getProcess();

        $proc->start();
        $proc->wait(function () use ($progress) {
            $progress->advance();
        });

        $progress->clear();
        $this->writeln('');

        if (!$proc->isSuccessful()) {
            $this->writeln('<error>Failed to sync data</error>');
            $this->markAsFailed();

            $this->writeln('<info>'.$proc->getCommandLine().'</info>');
            $this->writeln($proc->getOutput());
            $this->writeln($proc->getErrorOutput());

            // Failure here means we need to reinstall db
            $this->getSession()->enableFlag('reset_db_details');

            return;
        } elseif ($is_dev) {
            $this->writeln('<info>'.$proc->getCommandLine().'</info>');
            $this->writeln($proc->getOutput());
        }

        $this->writeln('Done!');

        $this->getSession()->enableFlag('sync_data_ok');
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('sync_data_ok');
    }
}
