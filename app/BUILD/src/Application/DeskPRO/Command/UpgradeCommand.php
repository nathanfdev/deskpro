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

use Application\DeskPRO\App;
use Application\DeskPRO\Monolog\Logger;
use Application\InstallBundle\Upgrade\Build\PostBuild;
use Application\InstallBundle\Upgrade\Build\PostBuildAlways;
use DeskPRO\Bundle\AppBundle\Util\BinariesPathValidator;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
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
     * @var \Application\InstallBundle\Upgrade\Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $via;

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
            ->addOption('preview', null, InputOption::VALUE_NONE, 'Do not run any queries, just show what will happen')
            ->addOption('via', null, InputOption::VALUE_NONE, '(Internal: How this is being run)')
            ->setHelp('This command executes the upgrader to bring your database to the same version the filesystem is');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $this->input  = $input;
        $this->output = $output;
        $this->via    = $input->getOption('via');

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
            $output->writeln('Please use: bin/console dp:update:reset');

            return 1;
        } elseif ($input->getOption('info')) {
            $action = 'info';
        } else {
            $action = 'run';
        }

        $logger          = new Logger('upgrade');
        $console_handler = new ConsoleHandler($output);
        $logger->pushHandler($console_handler);
        $this->logger = $logger;

        $stream_handler = new StreamHandler(dp_get_log_dir().'/upgrade.log');
        $logger->pushHandler($stream_handler);

        try {
            $this->getContainer()->getDb()->exec('SET SESSION wait_timeout = 86400');
            $logger->debug('Set wait_timeout to 86400');
        } catch (\Exception $e) {
            $logger->warn('Failed to set wait_timeout: '.$e->getMessage());
        }

        $this->manager = new \Application\InstallBundle\Upgrade\Manager(
            $this->getContainer(),
            $logger
        );

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

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // Utils
    //------------------------------------------------------------------------------------------------------------------

    private function envReqCheck()
    {
        global $DP_ENV;

        $output = $this->output;

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
            $output->writeln('<error>One or more paths to system binaries are incorrect</error>');
            $output->writeln('The following paths are incorrect: '.implode(', ', $wrongPaths));
            $output->writeln('');
            $output->writeln('You need to edit your config.paths.php file and correct the paths. The full path to the config fileis:');
            $output->writeln('<info>'.$root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.paths.php</info>');

            return self::ERR_BAD_PATHS;
        }
    }

    private function legacyVersionCheck()
    {
        $input  = $this->input;
        $output = $this->output;

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
            $output->writeln('<error>You must update to DeskPRO #443 before attempting to upgrade</error>');
            $output->writeln('The version of DeskPRO you are currently using is too old to be upgraded directly. You need to update to version #443 first.');
            $output->writeln('Read more: https://manuals.deskpro.com/html/sysadmin/upgrade-new-portal/upgrade-new-portal.html');

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
                $tables = App::getDb()->fetchAllCol('SHOW TABLES');
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

                App::getDb()->update('settings', ['value' => '1459273988'], ['name' => 'core.deskpro_build']);
            }
        }
    }

    //------------------------------------------------------------------------------------------------------------------
    // SHOW INFO
    //------------------------------------------------------------------------------------------------------------------

    private function showInfoAction()
    {
        $output = $this;

        $next_id = $manager->getNextBuildId();
        $output->writeln(sprintf("\tInstalled version:   %d (%s)", $manager->getCurrentBuild(), $manager->formatBuildId($manager->getCurrentBuild())));
        if (!$next_id) {
            $output->writeln(sprintf("\t     Next version:   none", $manager->getNextBuildId(), $manager->formatBuildId($manager->getNextBuildId())));
        } else {
            $output->writeln(sprintf("\t     Next version:   %d (%s)", $manager->getNextBuildId(), $manager->formatBuildId($manager->getNextBuildId())));
        }

        $output->writeln(sprintf("\t   Latest version:   %d (%s)", $manager->getLatestBuildId(), $manager->formatBuildId($manager->getLatestBuildId())));

        echo "\n";

        if (!$next_id) {
            $output->writeln('You are all up to date!');
        } else {
            $output->writeln('Builds that need to be executed:');
            foreach ($manager->getWaitingBuildIds() as $build_id) {
                $output->writeln(sprintf("\t%d (%s)", $build_id, $manager->formatBuildId($build_id)));
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
        App::getDb()->replace('settings', ['value' => $num, 'name' => 'core.deskpro_build']);
        $this->output->writeln('<info>Done</info>');

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // RUN POST SCRIPTS
    //------------------------------------------------------------------------------------------------------------------

    private function runSyncAction()
    {
        $this->output->writeln('<info>Running post scripts</info>');
        $build = new PostBuild($this->manager->getContainer(), $this->manager->getLogger());
        $build->run();
        $this->output->writeln('<info>Done</info>');

        $this->output->writeln('<info>Running post always scripts</info>');
        $build = new PostBuildAlways($this->manager->getContainer(), $this->manager->getLogger());
        $build->run();
        $this->output->writeln('<info>Done</info>');

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // RUN UPGRADE STEP
    //------------------------------------------------------------------------------------------------------------------

    private function runUpgradeStep()
    {
        $this->manager->runBuild($this->input->getOption('dobuildrun'));

        return 0;
    }

    //------------------------------------------------------------------------------------------------------------------
    // RUN FULL UPGRADE
    //------------------------------------------------------------------------------------------------------------------

    private function runUpgrade()
    {
        $manager       = $this->manager;
        $logger        = $this->logger;
        $ignore_errors = $this->input->getOption('ignore-errors');

        if (!$manager->getNextBuildId()) {
            $logger->info('All up to date');
        }

        //------------------------------
        // The main executor loop
        //------------------------------

        chdir(DP_APP_DIR);

        while ($next_id = $manager->getNextBuildId()) {
            $logger->debug("Build #$next_id");

            $cmd = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand("dp:upgrade --dobuildrun=$next_id");
            $logger->debug("Command: $cmd");
            $ret = null;
            passthru($cmd, $ret);

            if ($ret) {
                $logger->notice("--> Error status: $ret");

                if (!$ignore_errors) {
                    return $ret;
                } else {
                    $this->getContainer()->getDb()->update('settings', ['value' => $next_id], ['name' => 'core.deskpro_build']);
                }
            }

            $manager->reset();
        }

        //------------------------------
        // Post Run
        //------------------------------

        $logger->info('Running post scripts');
        $cmd = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand('dp:upgrade --runsync');
        $logger->debug("Command: $cmd");
        $ret = null;
        passthru($cmd, $ret);

        if ($ret) {
            $logger->notice("--> Error status: $ret");

            return $ret;
        }

        if (defined('DP_BUILD_TIME')) {
            $logger->info('Setting deskpro_build = '.DP_BUILD_TIME);
            $db = App::getDb();
            if (DP_BUILD_TIME == '1323444089') {
                // dev mode, the timestamp is the magic time
                $db->update('settings', ['value' => time()], ['name' => 'core.deskpro_build']);
            } else {
                $db->update('settings', ['value' => DP_BUILD_TIME], ['name' => 'core.deskpro_build']);
            }

            $db->update('settings', ['value' => DP_BUILD_NUM], ['name' => 'core.deskpro_build_num']);
        }

        $logger->info('Upgrade complete');
    }
}
