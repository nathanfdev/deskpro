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

namespace DeskPRO\Bundle\UpdateBundle\Command;

use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\Timer;
use Orb\Util\Numbers;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbBackupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:database-backup')
            ->setDescription('Wraps mysqldump utility to help make backups of your DeskPRO database.')
            ->addOption('session-id', null, InputOption::VALUE_REQUIRED, '(internal)')
            ->addArgument('targetPath', InputArgument::OPTIONAL, 'The the target dump file. E.g. /mypath/db.sql. Use BACKUPS_DIR as placeholder for the backups dir. If not specified, a random name will be created based on the current time.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.updater.general');
        $logger->info('********** dp:database-backup **********');

        $targetPath = $input->getArgument('targetPath');

        if (!$targetPath) {
            $i = 0;
            do {
                $targetPath = rtrim($this->getContainer()->get('deskpro.app_env')->getUserBackupsDir(), '/\\').DIRECTORY_SEPARATOR.'db-'.date('Y-m-d-His').($i ? "-$i" : '').'.sql';
            } while (file_exists($targetPath));
        }

        $targetPath = str_replace(
            ['BACKUPS_DIR', 'BACKUP_DIR'],
            $this->getContainer()->get('deskpro.app_env')->getUserBackupsDir(),
            $targetPath
        );

        $logger->debug('targetPath: '.$targetPath);

        if (file_exists($targetPath)) {
            $logger->error('targetPath already exists');
            $output->writeln('<error>Target path already exists: '.$targetPath.'</error>');

            return 1;
        }

        $dbBackup = $this->getContainer()->get('dp.updater.backup.db_backup');

        try {
            $t = Timer::start();
            $output->writeln('Backing up the databse ...');

            $dbBackup->backupDatabase(
                $targetPath,
                \DpRun\LowUtil::getMysqlInfoFromConfigArray($this->getContainer()->get('deskpro.app_env')->getConfig('database'))
            );
            $t->end();

            $output->writeln('<info>Backup done</info>');
            $output->writeln('');

            $table = new Table($output);
            $table->addRow(['Status', 'Backup finished successfully in '.$t->formatTotalTime()]);
            $table->addRow(['Path', $targetPath]);
            $table->addRow(['Size', Numbers::filesizeDisplay(filesize($targetPath))]);
            $table->render();

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>FAILED</error>');

            $table = new Table($output);
            $table->addRow(['Status', 'Backup failed']);
            $table->addRow(['Message', DebugUtils::getExceptionSummary($e)]);
            $table->render();

            $logger->error(sprintf('Backup failed: %s (Code: %s, Type: %s)', $e->getMessage(), $e->getCode(), get_class($e)));

            return 1;
        }
    }
}
