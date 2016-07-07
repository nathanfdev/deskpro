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

namespace DeskPRO\Bundle\UpgradeBundle\Command;

use DeskPRO\Bundle\UpgradeBundle\Console\Helper\DistroExceptionHelper;
use DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroReleaseCollection;
use DeskPRO\Bundle\UpgradeBundle\Logger\LogKeyEvent;
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
            ->setName('dp:upgrade:status')
            ->setDescription('Check the status of your installation and see if there are updates.')
            ->addOption('session-id', null, InputOption::VALUE_REQUIRED, '(internal)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.upgrader.general');
        $logger->info(
            '********** dp:upgrade:status **********',
            ['keyEvent' => LogKeyEvent::create('StatusCheck.start')]
        );

        $instanceReader = $this->getContainer()->get('dp.upgrader.instance_reader');
        $distroLoader   = $this->getContainer()->get('dp.upgrader.distro.manifest_loader');

        try {
            $releases     = $distroLoader->loadReleases();
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
        $table->addRow(['Current Build', sprintf('%-10s from %s', $instanceStatus->getCurrentRelease()->getId(), $instanceStatus->getCurrentRelease()->getDate()->format('Y-m-d'))]);
        $table->addRow(['Newest Build', sprintf('%-10s from %s', $instanceStatus->getLatestRelease()->getId(), $instanceStatus->getLatestRelease()->getDate()->format('Y-m-d'))]);

        // dupe data in the log
        $logger->info(sprintf('Current: %s %s', $instanceStatus->getCurrentRelease()->getId(), $instanceStatus->getCurrentRelease()->getDate()->format('Y-m-d')));
        $logger->info(sprintf('Newest: %s %s', $instanceStatus->getCurrentRelease()->getId(), $instanceStatus->getCurrentRelease()->getDate()->format('Y-m-d')));

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
