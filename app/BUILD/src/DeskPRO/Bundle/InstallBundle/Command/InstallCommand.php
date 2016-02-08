<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\InstallBundle\Command;

use DeskPRO\Bundle\InstallBundle\Installer\InstallerContext;
use DeskPRO\Bundle\InstallBundle\Installer\InstallStep;
use DeskPRO\Component\Exception\Filesystem\FileWriteException;
use DeskPRO\Component\Util\EnvUtils;
use DeskPRO\Component\Util\TypeUtils;
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
            ->addOption('list-steps', null, InputOption::VALUE_NONE, 'List all steps instead of running them')
            ->addOption('restart', null, InputOption::VALUE_NONE, 'Restart an installation (instead of resume)')
            ->addOption('redo-step', 'r', InputOption::VALUE_REQUIRED, 'Redo a specific step even if it is marked as complete')
            ->addOption('install-source', null, InputOption::VALUE_REQUIRED, 'From where this installer is being called from (internally used)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app_env = $this->getContainer()->get('deskpro.app_env');
        $sm      = $this->getContainer()->get('install.session_manager');
        $session = $sm->getLastInstallSession($input->getOption('restart'));

        $restart_step = $input->getOption('redo-step');

        $list_mode = $input->getOption('list-steps');
        $skip_list = array_map(function ($step_id) {
            $step_id = preg_replace('/Step$/', '', $step_id);
            $step_id = Strings::camelCaseToUnderscore($step_id);
            $step_id = strtolower($step_id);

            return $step_id;
        }, $input->getOption('skip'));

        #------------------------------
        # Make sure we can save the session ifo
        #------------------------------

        try {
            if ($input->hasOption('install-source')) {
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

        register_shutdown_function(function () use ($sm, $session) {
            $sm->saveInstallSession($session);
        });

        #------------------------------
        # Create the steps
        #------------------------------

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $context = new InstallerContext(
            $DP_ENV,
            $session,
            $output,
            $input,
            $this->getHelperSet()
        );

        $steps = [
            new InstallStep\WelcomeStep($context),
            new InstallStep\FileIntegrityStep($context),
            new InstallStep\OwnRequirementsStep($context),
            new InstallStep\CheckExistingStep($context),
            new InstallStep\AcceptPathsStep($context),
            new InstallStep\AcceptWebUrlStep($context),
            new InstallStep\AcceptDatabaseStep($context),
            new InstallStep\InstallTablesStep($context),
            new InstallStep\InstallConfigStep($context),
            new InstallStep\InstallFixturesStep($context),
            new InstallStep\InstallCronCommand($context),
            new InstallStep\AdminAccountStep($context),
            new InstallStep\DoneStep($context),
        ];

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
                return 1;
            }
        }

        return 0;
    }
}
