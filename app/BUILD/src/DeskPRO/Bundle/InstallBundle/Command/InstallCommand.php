<?php

namespace DeskPRO\Bundle\InstallBundle\Command;

use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\InstallFailEvent;
use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\InstallLogEvent;
use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\InstallSuccessEvent;
use DeskPRO\Bundle\InstallBundle\Installer\InstallerContext;
use DeskPRO\Bundle\InstallBundle\Installer\InstallProfile;
use DeskPRO\Bundle\InstallBundle\Installer\InstallStep;
use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use DeskPRO\Bundle\InstallBundle\InstallSession\SessionManager;
use DeskPRO\Component\Exception\Filesystem\FileWriteException;
use DeskPRO\Component\Util\EnvUtils;
use DeskPRO\Component\Util\RandUtils;
use DeskPRO\Component\Util\TypeUtils;
use DpRun\DpEnv;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('install:run')
            ->addOption('skip', 'x', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Skip one or more steps (by name)')
            ->addOption('list-steps', 'l', InputOption::VALUE_NONE, 'List all steps instead of running them')
            ->addOption('restart', null, InputOption::VALUE_NONE, 'Restart an installation (instead of resume)')
            ->addOption('truncate-db', null, InputOption::VALUE_NONE, 'If the db has existing tables, then we will truncate all tables instead of recreating. This can make things slightly faster during testing.')
            ->addOption('recreate-db', null, InputOption::VALUE_NONE, 'If the db name exists, it will be dropped and re-created (the db user must have permission to drop/create dbs).')
            ->addOption('redo-step', 'r', InputOption::VALUE_REQUIRED, 'Redo a specific step even if it is marked as complete')
            ->addOption('profile', 'p', InputOption::VALUE_REQUIRED, 'Get answers from a profile file')
            ->addOption('skip-wizard', null, InputOption::VALUE_NONE, 'Use the existing config files and skip the install wizard (including checks)')
            ->addOption('dev', null, InputOption::VALUE_NONE, 'Shortcut for --restart, --skip-wizard, --opt_skip_recommendations, --install-source dev')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Shortcut for specifying all user info at once. It must be a comma-separated value of "name, email, password" or "email, password". Ex: --user "John Doe, foo@bar.com, mypassword"')
            ->addOption('advanced', null, InputOption::VALUE_NONE, 'If you want to set up advanced settings')
            ->addOption('install-source', null, InputOption::VALUE_REQUIRED, 'From where this installer is being called from (internally used)');

        foreach (InstallProfile::getQuestionIds() as $qid) {
            $this->addOption('opt_'.$qid, null, InputOption::VALUE_REQUIRED, 'Installer option');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $force_restart = false;
        $profile       = new InstallProfile();
        $statService   = $this->getContainer()->get('dp.software_service.stats');

        if ($input->getOption('dev')) {
            $input->setOption('restart', true);
            $input->setOption('skip-wizard', true);
            $input->setOption('opt_skip_recommendations', 'yes');
            $input->setOption('install-source', 'dev');

            if (!$input->getOption('opt_path_php')) {
                $input->setOption('opt_path_php', 'auto');
            }
            if (!$input->getOption('opt_path_mysql')) {
                $input->setOption('opt_path_mysql', 'auto');
            }
            if (!$input->getOption('opt_path_mysqldump')) {
                $input->setOption('opt_path_mysqldump', 'auto');
            }
        }

        if ($opt = $input->getOption('user')) {
            $opt = explode(',', $opt, 3);
            $opt = array_map('trim', $opt);

            if (count($opt) === 3) {
                $input->setOption('opt_user_name', $opt[0]);
                $input->setOption('opt_user_email', $opt[1]);
                $input->setOption('opt_user_password', $opt[2]);
            } elseif (count($opt) === 2) {
                $input->setOption('opt_user_email', $opt[0]);
                $input->setOption('opt_user_password', $opt[1]);

                list($name) = explode('@', $opt[0], 2);

                $name = str_replace('_', ' ', $name);
                $name = str_replace('.', ' ', $name);
                $name = preg_replace('#[ ]{2,}#', ' ', $name); //consec spaces to single space
                $name = ucwords($name);
                $input->setOption('opt_user_name', $name);
            } else {
                $output->writeln('<error>Format must be: name, email, password</error>');
                $output->writeln('<error>Or: email, password</error>');

                return 1;
            }
        }

        if ($input->getOption('profile')) {
            $force_restart = true;
            $profile->readAnswersFile($input->getOption('profile'));
        }
        $profile->readAnswersInput($input);

        /** @var DpEnv $app_env */
        $app_env = $this->getContainer()->get('deskpro.app_env');
        /** @var SessionManager $sm */
        $sm = $this->getContainer()->get('install.session_manager');
        /** @var InstallSession $session */
        $session = $sm->getLastInstallSession($input->getOption('restart') || $force_restart);

        $restart_step = $input->getOption('redo-step');

        $list_mode = $input->getOption('list-steps');
        $skip_list = array_map(function ($step_id) {
            $step_id = preg_replace('/Step$/', '', $step_id);
            $step_id = Strings::camelCaseToUnderscore($step_id);
            $step_id = strtolower($step_id);

            return $step_id;
        }, $input->getOption('skip'));

        //------------------------------
        // Make sure we can save the session ifo
        //------------------------------

        try {
            if ($profile->hasAnswer('install-source')) {
                $session->setSource($profile->getAnswer('install-source'));
            }
            if ($input->getOption('install-source')) {
                $session->setSource($input->getOption('install-source'));
            }

            $sm->saveInstallSession($session);
        } catch (FileWriteException $e) {
            $var_dir = realpath($app_env->getUserTmpDir().'/../');

            $output->writeln('<error>Please ensure that the DeskPRO var/ directory exists and is writable, and all sub-directories are writable.</error>');
            $output->writeln(sprintf('<info>Path to var: %s</info>', $var_dir));
            $output->writeln('');

            if (!EnvUtils::isWindows()) {
                $output->writeln('On Linux, you can recursively chmod the directory with this command:');
                $output->writeln(sprintf('<comment>chmod -R 0777 %s</comment>', $var_dir));
                $output->writeln('');
            }

            $output->writeln('Fix file permissions and then run this tool again.');
            $output->writeln('');

            return 1;
        }

        if (!$DP_ENV->getDatManager()->hasTxtFile('server_info_auth')) {
            $DP_ENV->getDatManager()->writeTxtFile('server_info_auth', Strings::random(30, Strings::CHARS_ALPHANUM_IU));
        }

        register_shutdown_function(function () use ($sm, $session, $DP_ENV) {
            $sm->saveInstallSession($session);
        });

        if ($profile->hasAnswer('session_uuid')) {
            $session->setSessionUuid($profile->getAnswer('session_uuid'));
        }

        if (!$session->getSessionUuid() && $DP_ENV->getDatManager()->hasTxtFile('install_uuid')) {
            $session->setSessionUuid($DP_ENV->getDatManager()->readTxtFile('install_uuid'));
        }

        if (!$session->getSessionUuid()) {
            $session->setSessionUuid(RandUtils::randomStringFormat('%30An'));
        }

        $DP_ENV->getDatManager()->writeTxtFile('install_uuid', $session->getSessionUuid());

        //------------------------------
        // Create the steps
        //------------------------------

        $context = new InstallerContext(
            $DP_ENV,
            $session,
            $profile,
            $output,
            $input,
            $this->getHelperSet()
        );

        if ($session->getSource() === InstallSession::SOURCE_AUTO_INSTALLER) {
            $skip_list[] = 'admin_account';
            $skip_list[] = 'accept_web_url';
        }

        if (
            $session->getSource() === InstallSession::SOURCE_WIN_INSTALLER
            || $session->getSource() === InstallSession::SOURCE_AUTO_INSTALLER
            || $input->getOption('skip-wizard')
        ) {
            $skip_list[] = 'file_integrity';
            $skip_list[] = 'own_requirements';
            $skip_list[] = 'install_cron_command';
            $skip_list[] = 'check_existing';
        }

        if ($input->getOption('skip-wizard')) {
            $skip_list[] = 'admin_account';
            $skip_list[] = 'own_requirements';
            $skip_list[] = 'check_existing';
            $skip_list[] = 'accept_paths';
            $skip_list[] = 'accept_web_url';
            $skip_list[] = 'accept_database';
            $skip_list[] = 'install_config';
            $skip_list[] = 'install_cron_command';
            $skip_list[] = 'op_cache_warm_up';
        }

        if ($this->shouldIgnoreAdminSkip($input) && $key = array_search('admin_account', $skip_list)) {
            unset($skip_list[$key]);
        }

        $dbExistAction = null;
        if ($input->getOption('truncate-db')) {
            $dbExistAction = InstallStep\InstallTablesStep::TRUNCATE_DB;
        } elseif ($input->getOption('recreate-db')) {
            $dbExistAction = InstallStep\InstallTablesStep::RECREATE_DB;
        }

        $steps = [
            new InstallStep\WelcomeStep($context, in_array('admin_account', $skip_list)),
            new InstallStep\FileIntegrityStep($context),
            new InstallStep\OwnRequirementsStep($context),
            new InstallStep\CheckExistingStep($context),
            new InstallStep\AcceptPathsStep($context),
            new InstallStep\AcceptWebUrlStep($context),
            new InstallStep\AcceptDatabaseStep($context),
            new InstallStep\InstallTablesStep($context, $dbExistAction),
            new InstallStep\InstallConfigStep($context),
            new InstallStep\InstallFixturesStep($context),
            new InstallStep\InstallCronCommand($context),
            new InstallStep\AdminAccountStep($context),
            new InstallStep\OpCacheWarmUpStep($context),
            new InstallStep\DoneStep($context),
        ];

        if ($input->getOption('skip-wizard')) {
            array_unshift($steps, new InstallStep\SkipWizardStep($context));
        }

        // Send the log after
        register_shutdown_function(function () use ($statService, $DP_ENV, $session) {
            $logPath = $DP_ENV->getUserLogsDir().DIRECTORY_SEPARATOR.'installer.log';
            if (file_exists($logPath)) {
                $event = InstallLogEvent::create()
                    ->setUuid($session->getSessionUuid())
                    ->setLogFile(new \SplFileInfo($logPath))
                ;

                SystemErrorHandler::tryRun(function () use ($statService, $event) {
                    $statService->sendInstallLog($event);
                });
            }
        });

        /** @var InstallStep\AbstractStep $step */
        foreach ($steps as $num => $step) {
            $step_num = $num + 1;

            $step_id = TypeUtils::getBaseTypeName($step);
            $step_id = preg_replace('/Step$/', '', $step_id);
            $step_id = Strings::camelCaseToUnderscore($step_id);
            $step_id = strtolower($step_id);

            if ($skip_list && in_array($step_id, $skip_list)) {
                if ($list_mode) {
                    $output->writeln(sprintf('Step %02d: %-28s <comment>(skipped)</comment>', $step_num, $step_id));
                }
                continue;
            }

            if (!($restart_step && $restart_step === $step_id) && $step->isComplete()) {
                if ($list_mode) {
                    $output->writeln(sprintf('Step %02d: %-28s <info>(done)</info>', $step_num, $step_id));
                }
                continue;
            }

            if ($list_mode) {
                $output->writeln(sprintf('Step %02d: %s', $step_num, $step_id));
                continue;
            }

            $step->run();
            $output->writeln('');
            $sm->saveInstallSession($session);

            if ($step->isFailed()) {
                $event = InstallFailEvent::create()
                    ->setUuid($session->getSessionUuid())
                    ->setSummary('Failed during: '.TypeUtils::getBaseTypeName($step))
                ;

                SystemErrorHandler::tryRun(function () use ($statService, $event) {
                    $statService->sendInstallFail($event);
                });

                return 1;
            }
        }

        $event = InstallSuccessEvent::create()
            ->setUuid($session->getSessionUuid())
            ->setSummary('Completed')
        ;

        SystemErrorHandler::tryRun(function () use ($statService, $event) {
            $statService->sendInstallSuccess($event);
        });

        return 0;
    }

    private function shouldIgnoreAdminSkip(InputInterface $input)
    {
        return $input->getOption('user')
            || $input->getOption('opt_user_name')
            || $input->getOption('opt_user_email')
            || $input->getOption('opt_user_password');
    }
}
