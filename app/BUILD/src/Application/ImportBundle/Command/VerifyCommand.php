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

namespace Application\ImportBundle\Command;

use Application\ImportBundle\Importer\ImporterContext;
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
            ->setName('import:verify')
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkDbCredentials();

        // set an input path
        $context = new ImporterContext();

        if ($input->getOption('input-path')) {
            $context->setInputPath($input->getOption('input-path'));
        } else {
            $context->setInputPath($this->getImporterDefaultOutputPath());
        }

        $importer = $this->getContainer()->get('dp.importer');
        $this->setLoggerHandlers($input, $output);
        $context->getBatchConfig()->setHasRemaining(true);

        while ($context->getBatchConfig()->hasRemaining()) {
            $dataCollection = $importer->getImportData($context);
            $importer->validateData($dataCollection);
            $context->setBatchConfig($importer->getNextBatchConfig($context));
        }

        $output->writeln('All done.');
    }
}
