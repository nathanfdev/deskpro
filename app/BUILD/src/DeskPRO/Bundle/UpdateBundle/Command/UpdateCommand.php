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

declare (ticks = 100);

namespace DeskPRO\Bundle\UpdateBundle\Command;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\Settings\UpdaterSettingsResolver;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\RandUtils;
use DeskPRO\Component\Util\Timer;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update')
            ->addOption('session-id', null, InputOption::VALUE_REQUIRED, '(internal)')
            ->setDescription('Checks for updates, downloads, and then installs them if they exists')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $sessionId = $input->getOption('session-id');
        if (!$sessionId) {
            $sessionId = date('YmdHis').RandUtils::randomStringFormat('%8An');
        }

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        $DP_ENV->getDatManager()->writeTxtFile('last_updater_session_id', $sessionId);

        $updaterSettings = $this->getContainer()->get('updater_settings_resolver')->getUpdaterSettings();
        if ($updaterSettings->isEnabled()) {
            $nextDate = $updaterSettings->calculateNextTimeUtc();
            $nextDate = $nextDate ? $nextDate->format('Y-m-d H:i:s') : null;
        } else {
            $nextDate = null;
        }

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingRepos */
        $settingRepos = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository(Setting::class);
        $settingRepos->updateSetting(UpdaterSettingsResolver::AUTO_UPDATER_NEXT_TIME,   $nextDate ?: null);

        $output->writeln('Starting automatic update (SessionID: '.$sessionId.')');

        $smf = $this->getContainer()->get('dp.updater.session_manager_factory');
        $smf->enableSessionId($sessionId);

        $logger = $this->getContainer()->get('monolog.logger.updater.general');
        $logger->info(
            '[Auto-Upgrade] Starting session: '.$sessionId,
            ['keyEvent' => LogKeyEvent::create('AutoUpgrade.start')]
        );

        register_shutdown_function(function () use ($logger) {
            if (!defined('DP_DID_END_OK')) {
                $e = new \OutOfBoundsException('Auto-updater ended unexpectedly');
                $logger->error(
                    '[Auto-Upgrade] finished unexpectedly',
                    ['keyEvent' => LogKeyEvent::createForException('AutoUpgrade.error', $e)]
                );
            }
        });

        if (function_exists('pcntl_signal')) {
            $unexpectedFinishHandler = function ($sig) use ($logger) {
                $e = new \OutOfBoundsException("Auto-updater ended unexpectedly with signal $sig");
                $logger->error(
                    '[Auto-Upgrade] finished unexpectedly with signal '.$sig,
                    ['keyEvent' => LogKeyEvent::createForException('AutoUpgrade.error', $e)]
                );
                if (!defined('DP_DID_END_OK')) {
                    define('DP_DID_END_OK', true);
                }
                exit(1);
            };
            pcntl_signal(\SIGINT, $unexpectedFinishHandler);
            pcntl_signal(\SIGTERM, $unexpectedFinishHandler);
        }

        try {
            if ($ret = $this->doExecute($sessionId, $input, $output)) {
                $logger = $this->getContainer()->get('monolog.logger.updater.general');
                $logger->info(
                    '[Auto-Upgrade] Done with error',
                    ['keyEvent' => LogKeyEvent::create('AutoUpgrade.error', ['exitCode' => $ret])]
                );
            } else {
                $logger = $this->getContainer()->get('monolog.logger.updater.general');
                $logger->info(
                    '[Auto-Upgrade] Done success',
                    ['keyEvent' => LogKeyEvent::create('AutoUpgrade.success')]
                );
            }

            if (!defined('DP_DID_END_OK')) {
                define('DP_DID_END_OK', true);
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
            $logger->error(
                '[Auto-Upgrade] Exception: '.DebugUtils::getExceptionSummary($e),
                ['keyEvent' => LogKeyEvent::createForException('AutoUpgrade.error', $e)]
            );
            if (!defined('DP_DID_END_OK')) {
                define('DP_DID_END_OK', true);
            }
        }
    }

    /**
     * @param string          $sessionId
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     *
     * @return int
     */
    private function doExecute($sessionId, InputInterface $input, OutputInterface $output)
    {
        $t = Timer::start();

        $logger = $this->getContainer()->get('monolog.logger.updater.general');

        $logger->info(
            '[Auto-Upgrade] dp:update:status - start',
            ['keyEvent' => LogKeyEvent::create('AutoUpgrade.status.start')]
        );

        $command = $this->getApplication()->find('dp:update:status');
        $args    = new ArrayInput([
            'command'      => 'dp:update:status',
            '--session-id' => $sessionId,
        ]);
        if ($ret = $command->run($args, $output)) {
            $logger->info(
                '[Auto-Upgrade] dp:update:status - error',
                ['keyEvent' => LogKeyEvent::create('AutoUpgrade.status.error', ['exitCode' => $ret])]
            );

            return $ret;
        }

        $logger->info(
            '[Auto-Upgrade] dp:update:status - done ok',
            ['keyEvent' => LogKeyEvent::create('AutoUpgrade.status.success')]
        );

        // This is the only hard-coded event we need to handle in here,
        // to handle when we need to early exit
        $smf     = $this->getContainer()->get('dp.updater.session_manager_factory');
        $session = $smf->getManager()->reloadSession();
        if (!$session->getStatusStep()->doesRequireUpdate()) {
            return 0;
        }

        $skipBackup = false;

        if ($input->isInteractive()) {
            $output->writeln('A new update is available. Do you want to download and install it now?');
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion('[y/N]> ', false);

            if (!$helper->ask($input, $output, $question)) {
                $logger->debug('User answered "no" to confirmation');

                return 0;
            }

            $output->writeln('Do you want to perform a database backup before installing database updates?');
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion('[Y/n]> ', true);

            if (!$helper->ask($input, $output, $question)) {
                $logger->debug('User answered "no" to database backup');
                $skipBackup = true;
            }
        }

        #------------------------------

        $logger->info(
            '[Auto-Upgrade] dp:distro:download-build - start',
            ['keyEvent' => LogKeyEvent::create('AutoUpgrade.download.start')]
        );

        $command = $this->getApplication()->find('dp:distro:download-build');
        $args    = new ArrayInput([
            'command'            => 'dp:distro:download-build',
            '--session-id'       => $sessionId,
            '--replace-existing' => true,
            'zip'                => 'latest',
        ]);

        if ($ret = $command->run($args, $output)) {
            $logger->info(
                '[Auto-Upgrade] dp:distro:download-build - error',
                ['keyEvent' => LogKeyEvent::create('AutoUpgrade.download.error', ['exitCode' => $ret])]
            );

            return $ret;
        }

        $logger->info(
            '[Auto-Upgrade] dp:distro:download-build - done ok',
            ['keyEvent' => LogKeyEvent::create('AutoUpgrade.download.success')]
        );

        #------------------------------

        if (!$skipBackup) {
            $logger->info(
                '[Auto-Upgrade] dp:database-backup - start',
                ['keyEvent' => LogKeyEvent::create('AutoUpgrade.db_backup.start')]
            );

            $command = $this->getApplication()->find('dp:database-backup');
            $args    = new ArrayInput([
                'command'      => 'dp:database-backup',
                '--session-id' => $sessionId,
            ]);

            if ($ret = $command->run($args, $output)) {
                $logger->info(
                    '[Auto-Upgrade] dp:database-backup - error',
                    ['keyEvent' => LogKeyEvent::create('AutoUpgrade.db_backup.error', ['exitCode' => $ret])]
                );

                return $ret;
            }

            $logger->info(
                '[Auto-Upgrade] dp:database-backup - done ok',
                ['keyEvent' => LogKeyEvent::create('AutoUpgrade.db_backup.success')]
            );
        } else {
            $logger->info(
                '[Auto-Upgrade] dp:database-backup - skipped',
                ['keyEvent' => LogKeyEvent::create('AutoUpgrade.db_backup.success')]
            );
        }

        #------------------------------

        $logger->info(
            '[Auto-Upgrade] dp:update:activate-build - start',
            ['keyEvent' => LogKeyEvent::create('AutoUpgrade.activate.start')]
        );

        $command = $this->getApplication()->find('dp:update:activate-build');
        $args    = new ArrayInput([
            'command'      => 'dp:update:activate-build',
            '--session-id' => $sessionId,
            'buildId'      => 'latest',
        ]);

        if ($ret = $command->run($args, $output)) {
            $logger->info(
                '[Auto-Upgrade] dp:update:activate-build - error',
                ['keyEvent' => LogKeyEvent::create('AutoUpgrade.activate.error', ['exitCode' => $ret])]
            );

            return $ret;
        }

        $logger->info(
            '[Auto-Upgrade] dp:update:activate-build - done ok',
            ['keyEvent' => LogKeyEvent::create('AutoUpgrade.activate.success')]
        );

        $output->writeln('Upgrade complete in '.$t->formatTotalTime());

        return 0;
    }
}
