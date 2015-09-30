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

use Application\ImportBundle\Reader\ZenDesk;
use DateTime;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ZenDesk fixtures command.
 *
 * Class FixturesCommand
 */
class FixturesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:import:fixtures')
            ->setHelp('Import bundle fixtures')
            ->addOption(
                'type',
                null,
                InputOption::VALUE_REQUIRED,
                'Exporter type'
            )
            ->addOption(
                'offset',
                null,
                InputOption::VALUE_REQUIRED,
                'Offset'
            )
            ->addOption(
                'delete',
                'd',
                InputOption::VALUE_NONE,
                'Delete data'
            )
        ;

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $logger = new Logger('exporter');

        $formatter = new ConsoleFormatter();
        $formatter->ignoreEmptyContextAndExtra(true);
        $formatter->allowInlineLineBreaks(true);

        $handler = new ConsoleHandler($output);
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        /** @var ZenDesk\Fixtures\Collection $fixtures */
        $fixtures = ZenDesk\ZenDeskReaderFactory::createFixturesByDeskPROConfig();
        $fixture  = $fixtures
            ->getByType($input->getOption('type'))
            ->setLogger($logger)
        ;

        if ($input->getOption('delete')) {
            if (!$fixture instanceof ZenDesk\Fixtures\FixtureDeleteInterface) {
                throw new \RuntimeException('No delete methods');
            }

            $fixture->delete();
        } else {
            /* @var ZenDesk\Fixtures\FixtureInterface $fixture */
            $fixture->create(
                $input->getOption('offset'),
                new DateTime('-2 year'),
                new DateTime('-1 year')
            );
        }
    }
}
