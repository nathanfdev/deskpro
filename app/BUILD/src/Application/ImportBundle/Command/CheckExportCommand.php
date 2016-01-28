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

namespace Application\ImportBundle\Command;

use Application\ImportBundle\Generator;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check export command
 * Read and parse an external data to check if it's valid.
 *
 * Class CheckExportCommand
 */
class CheckExportCommand extends AbstractExportCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:export:check');
        $this->setHelp('Check export validation process');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(Generator\GeneratorConfig $config, LoggerInterface $logger, InputInterface $input, OutputInterface $output)
    {
        $generator = $this->createGenerator($config, $logger);
        $this->createAndSetProgressBar($generator, $input, $output);

        $exceptions = $generator->validate();
        foreach ($exceptions as $exception) {
            /* @var Generator\Validator\ValidatorConstraintException $exception */
            $logger->critical($exception);
        }

        $output->writeln('');
        if ($config->isVerbose()) {
            if ($exceptions->count() > 0) {
                $output->writeln(sprintf('Done. Errors found `%d`.', $exceptions->count()));
            } else {
                $output->writeln('Done. Checking was successful.');
            }
        } else {
            if ($exceptions->count() > 0) {
                $output->writeln(sprintf(
                    'Done. Errors found `%d`. Look at the log file `%s` to see details.',
                    $exceptions->count(), $config->getLogPath()
                ));
            } else {
                $output->writeln(sprintf(
                    'Done. Checking was successful. Look at the log file `%s` to see details.',
                    $config->getLogPath()
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkConfiguration(Generator\GeneratorConfig $config)
    {
        if ($config->needInputPath() &&  !$config->getInputPath()) {
            throw new RuntimeException('Input path must be specified');
        }
    }
}
