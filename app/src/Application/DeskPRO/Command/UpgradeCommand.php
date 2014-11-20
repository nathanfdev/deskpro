<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\Command;

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:upgrade')
             ->addOption('info', null, InputOption::VALUE_NONE, 'Set this flag to get info about your current instance')
             ->addOption('dobuildrun', null, InputOption::VALUE_REQUIRED, 'Runs a build script. Usually used internally.')
             ->addOption('runsync', null, InputOption::VALUE_NONE, 'Only runs the post sync scripts')
             ->addOption('reset', null, InputOption::VALUE_NONE, 'Removes status files that tells the system an upgrade is running. Use this if the systme is "stuck" in upgrade mode.')
             ->setHelp("This command executes the upgrader to bring your database to the same version the filesystem is");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        if ($input->getOption('reset')) {
            @unlink(DP_WEB_ROOT.'/auto-update-is-running.trigger');
            @unlink(dp_get_tmp_dir().'/auto-upgrade-started');
            $this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_started', null);
            $this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_error_writeperm', null);
            $this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_time', null);
            $this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_set_at', null);
            $this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_backup_files', null);
            $this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_backup_db', null);
            $this->getContainer()->getSettingsHandler()->setSetting('core.last_auto_upgrade_time', null);

            $output->writeln("Reset done.");

            return 0;
        }

        // Clear caches, including doctrine query caches
        App::getDb()->exec("TRUNCATE TABLE cache");
        @unlink(dp_get_tmp_dir().DIRECTORY_SEPARATOR.'dql.cache');

        $output->setVerbosity(4);

        $logger          = new Logger('upgrade');
        $console_handler = new ConsoleHandler($output);
        $logger->pushHandler($console_handler);

        $stream_handler = new StreamHandler(dp_get_log_dir().'/upgrade.log');
        $logger->pushHandler($stream_handler);

        try {
            $this->getContainer()->getDb()->exec("SET SESSION wait_timeout = 86400");
            $logger->debug("Set wait_timeout to 86400");
        } catch (\Exception $e) {
            $logger->warn("Failed to set wait_timeout: ".$e->getMessage());
        }

        $manager = new \Application\InstallBundle\Upgrade\Manager(
            $this->getContainer(),
            $logger
        );

        #------------------------------
        # Info
        #------------------------------

        if ($input->getOption('info')) {
            $next_id = $manager->getNextBuildId();
            $output->writeln(sprintf("\tInstalled version:   %d (%s)", $manager->getCurrentBuild(),  $manager->formatBuildId($manager->getCurrentBuild())));
            if (!$next_id) {
                $output->writeln(sprintf("\t     Next version:   none", $manager->getNextBuildId(),  $manager->formatBuildId($manager->getNextBuildId())));
            } else {
                $output->writeln(sprintf("\t     Next version:   %d (%s)", $manager->getNextBuildId(),  $manager->formatBuildId($manager->getNextBuildId())));
            }

            $output->writeln(sprintf("\t   Latest version:   %d (%s)", $manager->getLatestBuildId(), $manager->formatBuildId($manager->getLatestBuildId())));

            echo "\n";

            if (!$next_id) {
                $output->writeln("You are all up to date!");
            } else {
                $output->writeln("Builds that need to be executed:");
                foreach ($manager->getWaitingBuildIds() as $build_id) {
                    $output->writeln(sprintf("\t%d (%s)", $build_id, $manager->formatBuildId($build_id)));
                }
            }

            return 0;
        }

        #------------------------------
        # Want to run post scripts only
        #------------------------------

        if ($input->getOption('runsync')) {
            $output->writeln("<info>Running post scripts</info>");
            $manager->postUpgrade();
            $output->writeln("<info>Done All</info>");

            return 0;
        }

        #------------------------------
        # Runs a build script
        #------------------------------

        if (!$manager->getNextBuildId()) {
            $logger->info("All up to date");
        }

        if ($input->getOption('dobuildrun')) {
            $manager->runBuild($input->getOption('dobuildrun'));

            return 0;
        }

        #------------------------------
        # The main executor loop
        #------------------------------

        chdir(DP_ROOT.'/../');

        while ($next_id = $manager->getNextBuildId()) {
            $logger->info("Build #$next_id");

            $cmd = dp_get_php_command('cmd.php', "dp:upgrade --dobuildrun=$next_id");
            $logger->debug("Command: $cmd");
            $ret = null;
            passthru($cmd, $ret);

            if ($ret) {
                $logger->notice("--> Error status: $ret");

                return $ret;
            }

            $manager->reset();
        }

        #------------------------------
        # Post Run
        #------------------------------

        $logger->info("Running post scripts");
        $manager->postUpgrade();

        if (defined('DP_BUILD_TIME')) {
            $logger->info("Setting deskpro_build = ".DP_BUILD_TIME);
            $current = App::getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build'");
            if ($current < DP_BUILD_TIME) {
                App::getDb()->replace('settings', array('value' => DP_BUILD_TIME, 'name' => 'core.deskpro_build'));
                App::getDb()->replace('settings', array('value' => DP_BUILD_NUM, 'name' => 'core.deskpro_build_num'));
            }
        }

        $logger->info("Upgrade complete");

        return 0;
    }
}
