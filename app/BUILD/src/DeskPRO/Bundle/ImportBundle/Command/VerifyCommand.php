<?php

namespace DeskPRO\Bundle\ImportBundle\Command;

use DeskPRO\Bundle\ImportBundle\Model\BatchConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VerifyCommand.
 */
class VerifyCommand extends AbstractImporterCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('dp:import:verify')
            ->setHelp('Verifies the json files from the filesystem.')
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
        $importer    = $this->getContainer()->get('dp.importer');
        $batchConfig = new BatchConfig();

        while ($pointer = $importer->getBatchPointer($batchConfig)) {
            $importer->getImportData($pointer);
            $importer->updateBatchConfig($batchConfig, $pointer, false);
        }

        $output->writeln('All done.');
    }
}
