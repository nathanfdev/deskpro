<?php

namespace DeskPRO\Bundle\ImportBundle\Command;

use DeskPRO\Bundle\ImportBundle\Event\ProgressEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearCommand.
 */
class CleanCommand extends AbstractImporterCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('dp:import:clean')
            ->setHelp('Deletes json files from the filesystem.')
            ->addOption(
                'input-path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the directory where the exporting files are present'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $container->get('dp.importer.storage_adapter')->clean();
        $container->get('dp.importer.event_dispatcher')->dispatch(ProgressEvent::CLEAN, new ProgressEvent());

        $output->writeln('All done.');
    }
}
