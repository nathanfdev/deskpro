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
            ->addOption('restart', null, InputOption::VALUE_NONE, 'Restart an installation (instead of resume)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app_env = $this->getContainer()->get('deskpro.app_env');
        $sm      = $this->getContainer()->get('install.session_manager');
        $session = $sm->getLastInstallSession($input->getOption('restart'));

        #------------------------------
        # Make sure we can save the session ifo
        #------------------------------

        try {
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

        #------------------------------
        # Create the steps
        #------------------------------

        $context = new InstallerContext(
            $session,
            $output,
            $input,
            $this->getHelperSet()
        );

        $steps = [
            new InstallStep\WelcomeStep($context),
            new InstallStep\OwnRequirementsStep($context),
            new InstallStep\DoneStep($context),
        ];

        while ($steps) {
            /** @var InstallStep\AbstractStep $step */
            $step = array_shift($steps);

            if ($step->isComplete()) {
                continue;
            }

            $step->run();
            $sm->saveInstallSession($session);

            if ($step->isFailed()) {
                return 1;
            }
        }

        return 0;
    }
}
