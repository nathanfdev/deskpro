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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

namespace Application\DeskPRO\Command;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Monolog\Logger;
use Application\InstallBundle\Upgrade\Build\PostBuild;
use Application\InstallBundle\Upgrade\Build\PostBuildAlways;
use Application\InstallBundle\Upgrade\BuildFactory;
use Application\InstallBundle\Upgrade\BuildRunner;
use Application\InstallBundle\Upgrade\ManifestReader;
use DeskPRO\Bundle\AppBundle\Util\BinariesPathValidator;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ManifestReader
     */
    private $manifestReader;

    /**
     * @var BuildFactory
     */
    private $buildFactory;

    /**
     * @var \Application\InstallBundle\Upgrade\BuildRunner
     */
    private $buildRunner;

    /**
     * Special exit code used to indicate bad paths.
     */
    const ERR_BAD_PATHS = 190;

    protected function configure()
    {
        $this->setName('dp:upgrade')
            ->addOption('info', null, InputOption::VALUE_NONE, 'Set this flag to get info about your current instance')
            ->addOption('dobuildrun', null, InputOption::VALUE_REQUIRED, 'Runs a build script. Usually used internally.')
            ->addOption('runsync', null, InputOption::VALUE_NONE, 'Only runs the post sync scripts')
            ->addOption('setbuild', null, InputOption::VALUE_NONE, 'Sets the build number to now')
            ->addOption('reset', null, InputOption::VALUE_NONE, '(Legacy; ignored)')
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Does not halt the upgrade loop when an error happens')
            ->addOption('run-online', null, InputOption::VALUE_NONE, 'Run the upgrade in online mode (if possible)')
            ->addOption('fast', null, InputOption::VALUE_NONE, 'Avoid running slow post build scripts if possible')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force running even if the system thinks its a bad idea')
            ->setHelp('This command executes the upgrader to bring your database to the same version the filesystem is');
    }

    /**
     * @return DeskproContainer
     */
    public function getContainer()
    {
        /** @var DeskproContainer $container */
        $container = parent::getContainer();

        return $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $this->input  = $input;
        $this->output = $output;

        global $DP_ENV;

        if (!$DP_ENV->getConfig('env.skip_req_check')) {
            if ($ret = $this->envReqCheck()) {
                return $ret;
            }
        }

        if ($ret = $this->legacyVersionCheck()) {
            return $ret;
        }

        if ($input->getOption('dobuildrun')) {
            $action = 'dobuildrun';
        } elseif ($input->getOption('runsync')) {
            $action = 'runsync';
        } elseif ($input->getOption('setbuild')) {
            $action = 'setbuild';
        } elseif ($input->getOption('reset')) {
            $this->output->writeln('Please use: bin/console dp:update:reset');

            return 1;
        } elseif ($input->getOption('info')) {
            $action = 'info';
        } else {
            $action = 'run';
        }

        $this->logger = new Logger('upgrade');
        $this->logger->pushHandler(new ConsoleHandler($this->output));
        $this->logger->pushHandler(new StreamHandler($DP_ENV->getUserLogsDir().DIRECTORY_SEPARATOR.'upgrade.log'));

        $this->manifestReader = new ManifestReader($DP_ENV->getAppDir().'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php');
        $this->buildFactory   = new BuildFactory(
            $this->manifestReader,
            $this->getContainer(),
            $this->logger
        );
        $this->buildRunner = new BuildRunner(
            $this->buildFactory,
            $this->manifestReader,
            $this->logger
        );

        if ($action === 'runsync' || $action === 'dobuildrun' || $action === 'run') {
            try {
                $this->getContainer()->getDb()->exec('SET SESSION wait_timeout = 86400');
                $this->logger->debug('Set wait_timeout to 86400');
            } catch (\Exception $e) {
                $this->logger->warn('Failed to set wait_timeout: '.$e->getMessage());
            }
        }

        switch ($action) {
            case 'info':
                return $this->showInfoAction();

            case 'setbuild':
                return $this->setBuildNowAction();

            case 'runsync':
                return $this->runSyncAction();

            case 'dobuildrun':
                return $this->runUpgradeStep();

            default:
                return $this->runUpgrade();
        }
    }

    //------------------------------------------------------------------------------------------------------------------
    // Utils
    //------------------------------------------------------------------------------------------------------------------

    private function envReqCheck()
    {
        global $DP_ENV;

        $validator  = new BinariesPathValidator();
        $wrongPaths = [];

        $root          = $DP_ENV->getDpRoot();
        $phpPath       = $DP_ENV->getConfig('paths.php_path');
        $mysqlPath     = $DP_ENV->getConfig('paths.mysql_path');
        $mysqldumpPath = $DP_ENV->getConfig('paths.mysqldump_path');

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

        try {
            $validator->validateMysqldumpPath($mysqldumpPath);
        } catch (\Exception $e) {
            $wrongPaths[] = ($mysqldumpPath ?: 'mysqldump').': '.$e->getMessage();
        }

        if ($wrongPaths) {
            $this->output->writeln('<error>One or more paths to system binaries are incorrect</error>');
            $this->output->writeln('The following paths are incorrect: '.implode(', ', $wrongPaths));
            $this->output->writeln('');
            $this->output->writeln('You need to edit your config.paths.php file and correct the paths. The full path to the config fileis:');
            $this->output->writeln('<info>'.$root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.paths.php</info>');

            return self::ERR_BAD_PATHS;
        }

        return 0;
    }

    private function legacyVersionCheck()
    {
        $input = $this->input;

        $versionError = false;

        $this->getContainer()->get('audit_log.doctrine_listener')->disableListener();

        $dbVersion     = $this->getContainer()->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build'");
        $dbVersionName = $this->getContainer()->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build_num'");

        if ($dbVersion && $dbVersion <= 1463676536) {
            if (!$input->getOption('info') && !$input->getOption('dobuildrun') && !$input->getOption('runsync') && !$input->getOption('reset')) {
                $versionError = true;
            }
        }

        if ($versionError) {
            $this->output->writeln('<error>You must update to DeskPRO #443 before attempting to upgrade</error>');
            $this->output->writeln('The version of DeskPRO you are currently using is too old to be upgraded directly. You need to update to version #443 first.');
            $this->output->writeln('Read more: https://manuals.deskpro.com/html/sysadmin/upgrade-new-portal/upgrade-new-portal.html');

            return 1;
        }

        // 443, we need to reset version back in a time a bit before running the
        // upgrade because the upgrade scripts need to run from the proper position
        // at build Build1464777281
        if (!$input->getOption('info') && !$input->getOption('dobuildrun') && !$input->getOption('runsync') && !$input->getOption('reset')) {
            if ($dbVersion == '1470650875' || $dbVersion == '1471618600' || strpos($dbVersionName, '443.') === 0) {
                // Sanity check -- make sure someone didnt import a database dump over a new database
                // mysqldump uses 'drop table if exists' by default, so it would work if someone
                // tried to restore a dump into an existing database (e.g. from a fresh install).
                // if the user faield to clean the database, then NEW tables in v5 will still exist,
                // and cause the upgrade scripts to fail.
                $tables = $this->getContainer()->getDb()->fetchAllCol('SHOW TABLES');
                if (in_array('articles_slug_history', $tables)) {
                    $this->output->writeln('<error>Tables from v5 already exist in your database</error>');
                    $this->output->writeln('The most common cause of this is if you restored a MySQL dump onto an existing v5 install');
                    $this->output->writeln('without first performing the clean command.');
                    $this->output->writeln('');
                    $this->output->writeln('The process should look something like this:');
                    $this->output->writeln('<info>$ php bin/console install:clean --keep-config</info>');
                    $this->output->writeln('<info>$ mysql -uyouruser -p your_db_name < your-dump.sql</info>');
                    $this->output->writeln('<info>$ php bin/console dp:upgrade</info>');
                    $this->output->writeln('');

                    return 1;
                }

                $this->getContainer()->getDb()->update('settings', ['value' => '1459273988'], ['name' => 'core.deskpro_build']);
            }
        }

        return 0;
    }

    /**
     * @return int
     */
    private function getCurrentBuildId()
    {
        $dbVersion = $this->getContainer()->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build'");

        return $dbVersion;
    }

    /**
     * @param int $buildId
     *
     * @return string
     */
    private function formatBuildId($buildId)
    {
        return date('Y-m-d', $buildId);
    }

    /**
     * @param int $buildId
     *
     * @return bool
     */
    private function hasBuildRun($buildId)
    {
        return $this->getContainer()->get('database_connection')->fetchColumn('
            SELECT COUNT(*)
            FROM install_data
            WHERE build = ? AND name = ?
        ', [$buildId, 'has_run']) >= 1;
    }

    /**
     * @param int $buildId
     */
    private function markBuildHasRun($buildId)
    {
        $this->getContainer()->get('database_connection')->delete('install_data', [$buildId, 'has_run']);
        $this->getContainer()->get('database_connection')->insert('install_data', [$buildId, 'has_run', 1]);
    }

    //------------------------------------------------------------------------------------------------------------------
    // SHOW INFO
    //------------------------------------------------------------------------------------------------------------------

    private function showInfoAction()
    {
        $currentBuildId = $this->getCurrentBuildId();
        $nextBuildId    = $this->manifestReader->getNextBuildId($currentBuildId);
        $latestBuildId  = $this->manifestReader->getLatestBuildId();

        $this->output->writeln(sprintf('Database schema version:     %d (%s)', $currentBuildId, $this->formatBuildId($currentBuildId)));
        $this->output->writeln(sprintf('Filesystem schema version:   %d (%s)', $latestBuildId, $this->formatBuildId($latestBuildId)));

        echo "\n";

        if (!$nextBuildId) {
            $this->output->writeln('Your database is all up to date!');
        } else {
            $this->output->writeln('Builds that need to be executed:');

            $canRunOnline     = true;
            $canSkipPostBuild = true;

            foreach ($this->manifestReader->getWaitingBuildIds($currentBuildId) as $buildId) {
                $this->output->writeln(sprintf("\t%d (%s)", $buildId, $this->formatBuildId($buildId)));
                $buildInfo = $this->manifestReader->findBuild($buildId);
                if (!$buildInfo['isOnlineBuild']) {
                    $canRunOnline = false;
                }
                if (!$buildInfo['skipPostBuild']) {
                    $canSkipPostBuild = false;
                }
            }

            if ($canRunOnline || $canSkipPostBuild) {
                $this->output->writeln('');

                if ($canRunOnline) {
                    $this->output->writeln('<info>--> All of these builds can be run ONLINE (can_run_online)</info>');
                }
                if ($canSkipPostBuild) {
                    $this->output->writeln('<info>--> The upgrade can SKIP POST BUILD (skip_post_build)</info>');
                }
                if ($canRunOnline && $canSkipPostBuild) {
                    $this->output->writeln('<info>--> This upgrade can be run entirely online</info>');
                }
            }
        }

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // SET BUILD NOW
    //------------------------------------------------------------------------------------------------------------------

    private function setBuildNowAction()
    {
        $num = time();
        $this->logger->info('(Via --setbuild) Setting deskpro_build = '.$num);
        $this->getContainer()->getDb()->replace('settings', ['value' => $num, 'name' => 'core.deskpro_build']);
        $this->output->writeln('<info>Done</info>');

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // RUN POST SCRIPTS
    //------------------------------------------------------------------------------------------------------------------

    private function runSyncAction()
    {
        if (!$this->input->getOption('fast')) {
            $this->output->writeln('<info>Running PostBuild</info>');
            $postBuild = $this->buildFactory->makeBuildClass(PostBuild::class);
            $postBuild->run();
            $this->output->writeln('<info>.. done</info>');
        } else {
            $this->output->writeln('<info>Skipping PostBuild because of --fast flag</info>');
        }

        $this->output->writeln('<info>Running PostBuildAlways</info>');
        $postBuildALways = $this->buildFactory->makeBuildClass(PostBuildAlways::class);
        $postBuildALways->run();
        $this->output->writeln('<info>.. done</info>');

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // RUN UPGRADE STEP
    //------------------------------------------------------------------------------------------------------------------

    private function runUpgradeStep()
    {
        $this->buildRunner->runBuild($this->input->getOption('dobuildrun'));

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // RUN FULL UPGRADE
    //------------------------------------------------------------------------------------------------------------------

    private function runUpgrade()
    {
        $currentBuildId = $this->getCurrentBuildId();
        $db             = $this->getContainer()->get('database_connection');
        $runOnline      = $this->input->getOption('run-online');
        $ignoreErrors   = $this->input->getOption('ignore-errors');
        $force          = $this->input->getOption('force');

        if (!$this->manifestReader->getNextBuildId($currentBuildId)) {
            $this->logger->info('All up to date');
        }

        if ($runOnline) {
            $waitingBuilds = $this->manifestReader->getWaitingBuildIds($currentBuildId);
            $fail          = false;
            foreach ($waitingBuilds as $buildId) {
                $buildInfo = $this->manifestReader->findBuild($buildId);
                if (!$buildInfo['canRunOnline']) {
                    $this->output->writeln("<warn><{$buildInfo['classname']}> Build cannot be run online</warn>");
                    $fail = true;
                }
            }
            if ($fail) {
                if ($force) {
                    $this->output->writeln('<warn>Continuing with online run because of --force</warn>');
                } else {
                    $this->output->writeln('<error>Aborting: The --run-online option only works if every build between current and latest can run online</error>');

                    return 1;
                }
            }
        }

        //------------------------------
        // The main executor loop
        //------------------------------

        $canSkipPost = true;

        while ($nextBuildId = $this->manifestReader->getNextBuildId($currentBuildId)) {
            $this->logger->debug("Build #$nextBuildId");
            $buildInfo = $this->manifestReader->findBuild($nextBuildId);

            // e.g. online builds can be run separately, so we need to check
            if ($this->hasBuildRun($nextBuildId)) {
                $this->logger->notice('--> Skipped: Build has already run');
                $currentBuildId = $nextBuildId;
                continue;
            }

            if ($runOnline && !$buildInfo['isOnlineBuild']) {
                $this->logger->notice('--> Skipped: Running only online builds, this build is not an online build');
                $currentBuildId = $nextBuildId;
                continue;
            }

            if (!$buildInfo['skipPostBuild']) {
                $canSkipPost = false;
            }

            $cmdParts = ['dp:upgrade', "--dobuildrun=$nextBuildId"];
            $cmd      = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand(implode(' ', $cmdParts));
            $this->logger->debug("Command: $cmd");
            $ret = null;
            passthru($cmd, $ret);

            if ($ret) {
                $this->logger->notice("--> Error status: $ret");

                if (!$ignoreErrors) {
                    return $ret;
                }
            }

            $this->markBuildHasRun($nextBuildId);

            // We only increase database version when running real upgrades
            if (!$runOnline) {
                $this->getContainer()->getDb()->update('settings', ['value' => $nextBuildId], ['name' => 'core.deskpro_build']);
            }

            $currentBuildId = $nextBuildId;
        }

        if ($runOnline) {
            $this->logger->info('Online upgrade complete');

            return 0;
        }

        //------------------------------
        // Post Run
        //------------------------------

        $this->logger->info('Running post scripts');
        $cmdParts = ['dp:upgrade', '--runsync'];
        if ($canSkipPost && $this->input->getOption('fast')) {
            $cmdParts[] = '--fast';
        }
        $cmd = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand(implode(' ', $cmdParts));
        $this->logger->debug("Command: $cmd");
        $ret = null;
        passthru($cmd, $ret);

        if ($ret) {
            $this->logger->notice("--> Error status: $ret");

            return $ret;
        }

        if (defined('DP_BUILD_TIME')) {
            $this->logger->info('Setting deskpro_build = '.DP_BUILD_TIME);
            if (DP_BUILD_TIME == '1323444089') {
                // dev mode, the timestamp is the magic time
                $db->update('settings', ['value' => time()], ['name' => 'core.deskpro_build']);
            } else {
                $db->update('settings', ['value' => DP_BUILD_TIME], ['name' => 'core.deskpro_build']);
            }

            $db->update('settings', ['value' => DP_BUILD_NUM], ['name' => 'core.deskpro_build_num']);
        }

        $this->logger->info('Upgrade complete');

        return 0;
    }
}
