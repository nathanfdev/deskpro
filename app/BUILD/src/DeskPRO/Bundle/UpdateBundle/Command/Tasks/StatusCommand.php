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

namespace DeskPRO\Bundle\UpdateBundle\Command\Tasks;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:tasks:status')->setAliases(['dp:update-db:status'])
            ->setDescription('Check the database against the filesystem to see the status of any pending build scripts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildStatus    = $this->getContainer()->get('dp.build_tasks.build_status');
        $manifestReader = $this->getContainer()->get('dp.build_tasks.manifest_reader');

        $currentBuildId = $buildStatus->getSchemaBuild();
        $nextBuildId    = $manifestReader->getNextBuildId($currentBuildId);
        $latestBuildId  = $manifestReader->getLatestBuildId();

        $output->writeln(sprintf('Database schema version:     %d (%s)', $currentBuildId, $buildStatus->formatBuildId($currentBuildId)));
        $output->writeln(sprintf('Filesystem schema version:   %d (%s)', $latestBuildId, $buildStatus->formatBuildId($latestBuildId)));

        echo "\n";

        if (!$nextBuildId) {
            $output->writeln('Your database is all up to date!');
        } else {
            $output->writeln('Build tasks in this release:');

            $table = new Table($output);
            $table->setHeaders(['Build', 'Mode', 'Attr', 'Status']);

            foreach ($manifestReader->getWaitingBuildIds($currentBuildId) as $buildId) {
                $buildInfo = $manifestReader->findBuild($buildId);

                $cols = [sprintf('%d (%s)', $buildId, $buildStatus->formatBuildId($buildId))];

                if ($buildInfo['isOnlineBuild']) {
                    $cols[] = 'Online';
                } else {
                    $cols[] = 'Blocking';
                }
                if ($buildInfo['skipPostBuild']) {
                    $cols[] = 'skip-sync';
                } else {
                    $cols[] = 'None';
                }

                if ($buildStatus->hasBuildRun($buildId)) {
                    $cols[] = 'DONE';
                } else {
                    $cols[] = 'Pending';
                }

                $table->addRow($cols);
            }

            $table->render();
        }
    }
}
