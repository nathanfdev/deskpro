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

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryUsageProcessor;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractImporterCommand.
 */
abstract class AbstractImporterCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            'memory-usage',
            'm',
            InputOption::VALUE_NONE,
            'Shows memory usage'
        );
    }

    /**
     * Configure a logger.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function setLoggerHandlers(InputInterface $input, OutputInterface $output)
    {
        $appEnv = $this->getContainer()->get('deskpro.app_env');
        $logger = $this->getContainer()->get('dp.importer_logger');

        $formatter = new LineFormatter();
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        if ($appEnv->getUserLogsDir()) {
            $handler = new StreamHandler($this->getLogFilePath());
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
        }

        if ($input->hasOption('verbose')) {
            $formatter = new ConsoleFormatter();
            $formatter->ignoreEmptyContextAndExtra(true);
            $formatter->allowInlineLineBreaks(true);

            $handler = new ConsoleHandler($output);
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);
        }

        if ($input->getOption('memory-usage')) {
            $logger->pushProcessor(new MemoryUsageProcessor());
        }
    }

    /**
     * Override container to set correct type hinting.
     *
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * @return string
     */
    protected function getLogFilePath()
    {
        return $this->getContainer()->get('deskpro.app_env')->getUserLogsDir().'/importer.log';
    }

    /**
     * @return string
     */
    protected function getImporterDefaultOutputPath()
    {
        return rtrim($this->getContainer()->get('deskpro.app_env')->getUserTmpDir(), '/').'/importer';
    }
}
