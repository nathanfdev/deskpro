<?php

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
            ->addOption('skip-diskspace-check', null, InputOption::VALUE_NONE, 'Skip the disk space check')
            ->addOption('output-command', null, InputOption::VALUE_NONE, 'Output the backup command instead of running it')
            ->addOption('with-gzip', null, InputOption::VALUE_NONE, 'Gzip the backup (only available on *nix)')
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

        if ($input->getOption('with-gzip')) {
            $targetPath .= '.gz';
        }

        $logger->debug('targetPath: '.$targetPath);

        if (file_exists($targetPath)) {
            $logger->error('targetPath already exists');
            $output->writeln('<error>Target path already exists: '.$targetPath.'</error>');

            return 1;
        }

        $dbInfo = \DpRun\LowUtil::getMysqlInfoFromConfigArray($this->getContainer()->get('deskpro.app_env')->getConfig('database'));

        if ($input->getOption('output-command')) {
            $cmdBuilder = $this->getContainer()->get('dp.updater.backup.cmd_builder');
            echo $cmdBuilder->getDumpCmd($targetPath, $dbInfo, ['with_gzip' => $input->getOption('with-gzip')]);
            echo "\n";

            return;
        }

        $dbBackup = $this->getContainer()->get('dp.updater.backup.db_backup');

        try {
            $t = Timer::start();
            $output->writeln('Backing up the databse ...');
            $output->writeln('  Target: '.$targetPath);

            $dbBackup->backupDatabase(
                $targetPath,
                $dbInfo,
                [
                    'skip_diskspace_check' => $input->getOption('skip-diskspace-check'),
                    'with_gzip'            => $input->getOption('with-gzip'),
                ]
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
