<?php

namespace DeskPRO\Bundle\UpdateBundle\Command;

use DeskPRO\Bundle\UpdateBundle\Console\Helper\DistroExceptionHelper;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroReleaseCollection;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use DeskPRO\Component\Util\DebugUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:status')
            ->setDescription('Check the status of your installation and see if there are updates.')
            ->addOption('session-id', null, InputOption::VALUE_REQUIRED, '(internal)')
            ->addOption('only-auto', null, InputOption::VALUE_NONE, 'Only update to releases approved for automatic updates')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.updater.general');
        $logger->info(
            '********** dp:update:status **********',
            ['keyEvent' => LogKeyEvent::create('StatusCheck.start')]
        );

        $instanceReader = $this->getContainer()->get('dp.updater.instance_reader');
        $distroLoader   = $this->getContainer()->get('dp.updater.distro.manifest_loader');

        try {
            if ($input->getOption('only-auto')) {
                $releases = $distroLoader->loadReleases(['with_flags' => 'auto_enabled']);
            } else {
                $releases = $distroLoader->loadReleases();
            }

            $returnStatus = 0;
        } catch (\Exception $e) {
            $exHelper = new DistroExceptionHelper($output);
            $exHelper->renderManifestFailure($e, $distroLoader);

            $logger->error(
                DebugUtils::getExceptionSummary($e),
                ['keyEvent' => LogKeyEvent::createForException('StatusCheck.error', $e, [
                    'message' => $exHelper->getManifestFailureDescription($e, $distroLoader),
                ])]
            );

            $output->writeln('');
            $output->writeln('');

            $releases     = new DistroReleaseCollection([]);
            $returnStatus = 1;
        }

        $instanceStatus = $instanceReader->getInstanceStatus($releases);

        $table = new Table($output);
        $table->addRow(['Current Build', sprintf('%-10s from %s', $instanceStatus->getCurrentRelease()->getName(), $instanceStatus->getCurrentRelease()->getDate()->format('Y-m-d'))]);
        $table->addRow(['Newest Build', sprintf('%-10s from %s', $instanceStatus->getLatestRelease()->getName(), $instanceStatus->getLatestRelease()->getDate()->format('Y-m-d'))]);

        // dupe data in the log
        $logger->info(sprintf('Current: %s %s', $instanceStatus->getCurrentRelease()->getName(), $instanceStatus->getCurrentRelease()->getDate()->format('Y-m-d')));
        $logger->info(sprintf('Newest: %s %s', $instanceStatus->getCurrentRelease()->getName(), $instanceStatus->getCurrentRelease()->getDate()->format('Y-m-d')));

        $table->addRow(new TableSeparator());
        if ($instanceStatus->isOutdated()) {
            $logger->info(
                'Helpdesk needs update',
                ['keyEvent' => LogKeyEvent::create('StatusCheck.updates_available')]
            );
            $table->addRow(['Status', sprintf('Your helpdesk is behind by %d versions. Your version is %d days old.', $instanceStatus->getNumBetween(), $instanceStatus->getDaysOld())]);
        } else {
            $logger->info(
                'Helpdesk is up to date',
                ['keyEvent' => LogKeyEvent::create('StatusCheck.no_updates_available')]
            );
            $table->addRow(['Status', 'Your helpdesk is up to date.']);
        }

        $table->render();

        return $returnStatus;
    }
}
