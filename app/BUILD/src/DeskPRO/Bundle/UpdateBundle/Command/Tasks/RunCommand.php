<?php

namespace DeskPRO\Bundle\UpdateBundle\Command\Tasks;

use Application\DeskPRO\Monolog\Logger;
use Application\DeskPRO\ORM\Util\Util as ORMUtil;
use DeskPRO\Bundle\AppBundle\Util\BinariesPathValidator;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:tasks:run')->setAliases(['dp:upgrade', 'dp:update-db'])
            ->setDescription('Runs pending upgrade tasks. If you want to see what needs to be done, use dp:update-db:status')
            ->addOption('partial-online', null, InputOption::VALUE_OPTIONAL, 'Runs pending ONLINE upgrade scripts. This will stop at the first blocking script unless you use --partial-online=FORCE', false)
            ->addOption('fast-sync', null, InputOption::VALUE_OPTIONAL, 'After a full upgrade, attempt to use fast post-build sync scripts. This is just a hint unless you use --fast-sync=FORCE', false)
            ->addOption('preview', null, InputOption::VALUE_NONE, 'Do not run any commands, just show a preview of what will happen')
            ->addOption('ignore-errors', null, InputOption::VALUE_OPTIONAL, 'Continue even if a build task returns an error status.', 'auto')
            ->addOption('skip-fk-checks', null, InputOption::VALUE_NONE, 'Skip foreign keys constraints check')
            ->addOption('skip-refresh-signal', null, InputOption::VALUE_NONE, 'Do not send refresh signal')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($ret = $this->envReqCheck($output)) {
            return $ret;
        }

        /*
        if (
            !$input->getOption('skip-fk-checks')
            && ($ret = $this->FKConstraintsCheck($output))) {
            // nothing -- for now, not preventing, just showing warning
        }
        */

        if ($ret = $this->legacyVersionCheck($output)) {
            return $ret;
        }

        //------------------------------
        // Setup
        //------------------------------

        global $DP_ENV;

        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $this->getContainer()->get('audit_log.doctrine_listener')->disableListener();

        $logger = new Logger('upgrade');
        $h      = new ConsoleHandler($output);
        $h->setFormatter(new ConsoleFormatter(null, null, true));
        $logger->pushHandler($h);
        $logger->pushHandler(new StreamHandler($DP_ENV->getUserLogsDir().DIRECTORY_SEPARATOR.'upgrade.log'));

        $buildStatus    = $this->getContainer()->get('dp.build_tasks.build_status');
        $manifestReader = $this->getContainer()->get('dp.build_tasks.manifest_reader');
        $isPreview      = $input->getOption('preview');
        $ignoreErrors   = true;

        if ($this->getContainer()->get('deskpro.app_env')->getConfig('upgrader.errors_are_fatal')) {
            $ignoreErrors = false;
        }

        $v = $input->getOption('ignore-errors');
        if ($v !== 'auto') {
            if (in_array($v, ['0', 'n', 'no', 'f', 'false', 'off'])) {
                $ignoreErrors = false;
            } elseif ($v === '' || in_array($v, ['1', 'y', 'yes', 't', 'true', 'on'])) {
                $ignoreErrors = true;
            } else {
                $output->writeln('<error>Unknown value for --ignore-errors specified</error>');
            }
        }

        //------------------------------
        // Options
        //------------------------------

        $isPartialOnline = false;
        $isFastSync      = false;

        if ($input->hasParameterOption('--partial-online')) {
            if (strtoupper($input->getOption('partial-online')) === 'FORCE' || strtoupper($input->getOption('partial-online')) === 'F') {
                $logger->info('Running ALL ONLINE scripts due to --partial-online=FORCE');
                $isPartialOnline = 'force';
            } else {
                $logger->info('Running ONLINE scripts due to --partial-online');
                $isPartialOnline = 'normal';
            }
        }

        if (!$isPartialOnline && $input->hasParameterOption('--fast-sync')) {
            if (strtoupper($input->getOption('fast-sync')) === 'FORCE' || strtoupper($input->getOption('fast-sync')) === 'F') {
                $logger->info('Will perform a FAST SYNC due to --fast-sync=FORCE');
                $isFastSync = 'force';
            } else {
                $logger->info('Will attempt a FAST SYNC due to --fast-sync');
                $isFastSync = 'normal';
            }
        }

        $canFastSync = null;

        //------------------------------
        // Run Loop
        //------------------------------

        $currentBuildId = $buildStatus->getSchemaBuild();
        $hasErrors      = false;

        $logger->debug("Current Build #$currentBuildId (".date('Y-m-d', $currentBuildId).')');

        while ($nextBuildId = $manifestReader->getNextBuildId($currentBuildId)) {
            $logger->debug("Build #$nextBuildId (".date('Y-m-d', $nextBuildId).')');
            $buildInfo = $manifestReader->findBuild($nextBuildId);

            // e.g. online builds can be run separately, so we need to check
            if ($buildStatus->hasBuildRun($nextBuildId)) {
                $logger->notice('--> Skipped: Build has already run');
                $currentBuildId = $nextBuildId;
                continue;
            }

            if ($isPartialOnline && !$buildInfo['isOnlineBuild']) {
                if ($isPartialOnline === 'force') {
                    $logger->notice('--> Skipped: Running only online builds, this build is not an online build');
                    $currentBuildId = $nextBuildId;
                    continue;
                } else {
                    // exit loop on first non-online build
                    $logger->notice('--> Stopping now, all online builds have been executed');
                    break;
                }
            }

            if ($canFastSync === null) {
                $canFastSync = $buildInfo['skipPostBuild'];
            }

            if (!$buildInfo['skipPostBuild']) {
                $canFastSync = false;
            }

            $cmdParts = ['dp:update:tasks:run-build', $nextBuildId];
            $cmd      = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand(implode(' ', $cmdParts));
            $logger->debug("Command: $cmd");
            $ret = null;

            if ($isPreview) {
                $currentBuildId = $nextBuildId;
                continue;
            }

            passthru($cmd, $ret);

            if ($ret) {
                $logger->notice("--> Error status: $ret");

                $hasErrors = true;

                if (!$ignoreErrors) {
                    return $ret;
                }
            }

            $buildStatus->markBuildHasRun($nextBuildId);

            // if running real upgrade, we update the schema version in db
            if (!$isPartialOnline) {
                $buildStatus->setSchemaBuild($nextBuildId);
            }

            $currentBuildId = $nextBuildId;
        }

        if ($isPartialOnline) {
            $logger->info('Online upgrade is complete');

            return 0;
        }

        //------------------------------
        // Post Build
        //------------------------------

        $logger->info('Running post scripts');
        $cmdParts = ['dp:update:tasks:run-sync'];
        if ($isFastSync) {
            $logger->info('Fast sync enabled');
            if (!$canFastSync) {
                if ($isFastSync === 'force') {
                    $logger->warn('Fast sync is not explicitly marked as safe, but force was enabled so fast sync will still be used');
                } else {
                    $logger->notice('Fast sync is not explicitly marked as safe, so fast sync is NOT being used');
                }
            }

            if (($canFastSync && $isFastSync) || $isFastSync === 'force') {
                $cmdParts[] = '--fast';
            }
        }
        $cmd = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand(implode(' ', $cmdParts));
        $logger->debug("Command: $cmd");
        $ret = null;
        if (!$isPreview) {
            passthru($cmd, $ret);

            if ($ret) {
                $hasErrors = true;
                $logger->warn("--> dp:update:tasks:run-sync exited with error status: $ret");
            }
        }

        //------------------------------
        // Set schema
        //------------------------------

        if (defined('DP_BUILD_TIME')) {
            $logger->info('Setting deskpro_build = '.DP_BUILD_TIME);
            if (!$isPreview) {
                if (DP_BUILD_TIME == '1323444089') {
                    // dev mode, the timestamp is the magic time
                    $buildStatus->setSchemaBuild(time());
                } else {
                    $buildStatus->setSchemaBuild(DP_BUILD_TIME);
                }
            }
        }

        //------------------------------
        // Send refresh signal
        //------------------------------

        if (!$input->getOption('skip-refresh-signal')) {
            // This is as a command because the logic for deploying the message
            $cmd = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand([
                'dp:utility:refresh-agent-interface',
                '--who', 'Helpdesk Upgrade',
                '--message', 'The helpdesk was upgraded. Your browser will now refresh.',
                '--reason-code', 'upgrade_complete',
            ], false);
            $logger->debug("Refresh signal with command: $cmd");
            $ret = null;
            if (!$isPreview) {
                passthru($cmd, $ret);

                if ($ret) {
                    $logger->warn("--> dp:utility:refresh-agent-interface exited with error status: $ret");
                }
            }
        }

        $logger->info('Upgrade complete');

        if ($hasErrors) {
            $logger->warn('');
            $logger->warn(str_repeat('!', 60));
            $logstr = <<<'LOGSTR'
WARNING: One or more errors were raised during the upgrade process.

The system has finished the upgrade process and your helpdesk has been put back online. However, you should send the
log to us at support@deskpro.com so our agents can review it to determine what the issue was.

If you experience significant issues after this upgrade, you may wish to revert to your pre-upgrade database backup:
https://support.deskpro.com/en/guides/sysadmin-guide/backups/restoring-from-backup
LOGSTR;
            $logger->warn($logstr);
            $logger->warn(str_repeat('!', 60));
            $logger->warn('');
        }

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // Utils
    //------------------------------------------------------------------------------------------------------------------

    private function envReqCheck(OutputInterface $output)
    {
        if (defined('DPC_IS_CLOUD')) {
            return 0;
        }

        global $DP_ENV;

        $validator  = new BinariesPathValidator();
        $wrongPaths = [];

        $root      = $DP_ENV->getDpRoot();
        $phpPath   = $DP_ENV->getConfig('paths.php_path');
        $mysqlPath = $DP_ENV->getConfig('paths.mysql_path');

        try {
            $validator->validatePhpPath($phpPath, $root);
        } catch (\Exception $e) {
            $wrongPaths[] = ($phpPath ?: 'php').': '.$e->getMessage();
        }

        try {
            $validator->validateMysqlPath($mysqlPath);
        } catch (\Exception $e) {
            $wrongPaths[] = ($mysqlPath ?: 'mysql').': '.$e->getMessage();
        }

        if ($wrongPaths) {
            $output->writeln('<error>One or more paths to system binaries are incorrect</error>');
            $output->writeln('The following paths are incorrect: '.implode(', ', $wrongPaths));
            $output->writeln('');
            $output->writeln('You need to edit your config.paths.php file and correct the paths. The full path to the config fileis:');
            $output->writeln('<info>'.$root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.paths.php</info>');

            return 1;
        }

        return 0;
    }

    private function FKConstraintsCheck(OutputInterface $output)
    {
        if (defined('DPC_IS_CLOUD')) {
            return 0;
        }

        /** @var EntityManager[] $entityManagers */
        $entityManagers = [
            'default' => $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            'system'  => $this->getContainer()->get('doctrine.orm.system_entity_manager'),
            'audit'   => $this->getContainer()->get('doctrine.orm.audit_entity_manager'),
        ];

        foreach ($entityManagers as $em) {
            if (!ORMUtil::isAllFKConstraintsExist($em)) {
                $output->writeln('<error>NOTICE</error>');
                $output->writeln(
                    'The system has detected that your database schema has one or more differences from the default.'
                    .'These differences are most likely trivial and can usually be ignored. But in some rare cases,'
                    .'this can be a sign of data corruption.'
                );
                $output->writeln('It is highly recommended you check your schema with the automated schema maintenance tool as described here:');
                $output->writeln('https://support.deskpro.com/kb/articles/665');
                $output->writeln('The upgrade will continue in 10 seconds... You can abort by interrupting this process (e.g. CTRL+C).');
                sleep(10);

                return 1;
            }
        }

        return 0;
    }

    private function legacyVersionCheck(OutputInterface $output)
    {
        if (defined('DPC_IS_CLOUD')) {
            return 0;
        }

        $dbVersion = $this->getContainer()->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build'");

        if ($dbVersion > 1473329441) {
            return 0;
        }

        $dbVersionName = $this->getContainer()->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build_num'");

        if ($dbVersionName && version_compare($dbVersionName, '442.0', '<')) {
            $output->writeln('<error>You must update to DeskPRO #443 before attempting to upgrade</error>');
            $output->writeln('The version of DeskPRO you are currently using is too old to be upgraded directly. You need to update to version #443 first.');
            $output->writeln('Read more: https://support.deskpro.com/en_GB/guides/sysadmin-guide/upgrading-2/upgrade-to-deskpro-v5');

            return 1;
        }

        // 443, we need to reset version back in a time a bit before running the
        // upgrade because the upgrade scripts need to run from the proper position
        // at build Build1464777281
        if ($dbVersionName && ($dbVersionName === '443' || strpos($dbVersionName, '443.') === 0)) {
            $hasStarted = $this->getContainer()->get('database_connection')->fetchColumn("
                SELECT data
                FROM install_data
                WHERE build = '1460678400' AND name = 'has_run'
            ");

            // This only matters if the upgrade hasnt started yet
            if (!$hasStarted) {
                // Sanity check -- make sure someone didnt import a database dump over a new database
                // mysqldump uses 'drop table if exists' by default, so it would work if someone
                // tried to restore a dump into an existing database (e.g. from a fresh install).
                // if the user faield to clean the database, then NEW tables in v5 will still exist,
                // and cause the upgrade scripts to fail.
                $tables = $this->getContainer()->get('database_connection')->fetchAllCol('SHOW TABLES');
                if (in_array('articles_slug_history', $tables)) {
                    $output->writeln('<error>Tables from v5 already exist in your database</error>');
                    $output->writeln('The most common cause of this is if you restored a MySQL dump onto an existing v5 install');
                    $output->writeln('without first performing the clean command.');
                    $output->writeln('');
                    $output->writeln('The process should look something like this:');
                    $output->writeln('<info>$ php bin/console install:clean --keep-config</info>');
                    $output->writeln('<info>$ mysql -uyouruser -p your_db_name < your-dump.sql</info>');
                    $output->writeln('<info>$ php bin/console dp:upgrade</info>');
                    $output->writeln('');

                    return 1;
                }

                $this->getContainer()->getDb()->update('settings', ['value' => '1459273988'], ['name' => 'core.deskpro_build']);
            }
        }

        return 0;
    }
}
