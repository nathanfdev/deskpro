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

use DeskPRO\Bundle\UpgradeBundle\Distro\DistroManifestLoader;
use DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroReleaseCollection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:upgrade:status')
            ->setDescription('Check the status of your installation and see if there are updates.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $instanceReader = $this->getContainer()->get('dp.upgrader.instance_reader');
        $distroLoader   = $this->getContainer()->get('dp.upgrader.distro.manifest_loader');

        try {
            $releases = $distroLoader->loadReleases();
        } catch (\Exception $e) {
            $output->writeln('<error>Could not load version data from the DeskPRO distribution server.</error>');
            $output->writeln('The two most common reasons for this error are:');
            $output->writeln('  * You have a firewall that is preventing the network connection to the server.');
            $output->writeln('  * The distribution server is temporarily unavailable.');
            $output->writeln('');
            $output->writeln('This is the URL the system is trying to access:');
            $output->writeln('  '.$distroLoader->getApiUrl().DistroManifestLoader::MANIFEST_ENDPOINT);
            $output->writeln('');

            do {
                $table = new Table($output);
                $table->addRow(['Type', get_class($e)]);
                $table->addRow(['Code', $e->getCode()]);
                $table->addRow(['Message', get_class($e->getMessage())]);
                $table->render();
            } while ($e = $e->getPrevious());

            $output->writeln('');
            $output->writeln('');

            $releases = new DistroReleaseCollection([]);
        }

        $instanceStatus = $instanceReader->getInstanceStatus($releases);

        $table = new Table($output);
        $table->addRow(['Current Build', sprintf('%-10s from %s', $instanceStatus->getCurrentRelease()->getId(), $instanceStatus->getCurrentRelease()->getDate()->format('Y-m-d'))]);
        $table->addRow(['Newest Build', sprintf('%-10s from %s', $instanceStatus->getLatestRelease()->getId(), $instanceStatus->getLatestRelease()->getDate()->format('Y-m-d'))]);

        $table->addRow(new TableSeparator());
        if ($instanceStatus->isOutdated()) {
            $table->addRow(['Status', sprintf('Your helpdesk is behind by %d versions. Your version is %d days old.', $instanceStatus->getNumBetween(), $instanceStatus->getDaysOld())]);
        } else {
            $table->addRow([new TableCell('Your helpdesk is up to date.', ['colspan' => 2])]);
        }

        $table->render();
    }
}
