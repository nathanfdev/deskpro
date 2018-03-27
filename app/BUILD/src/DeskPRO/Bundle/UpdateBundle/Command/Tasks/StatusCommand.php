<?php

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
