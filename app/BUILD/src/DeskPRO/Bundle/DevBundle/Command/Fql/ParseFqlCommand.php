<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Fql;

use DeskPRO\Component\FilterQueryLanguage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ParseFqlCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:fql:parse');
        $this->addOption('format', 'm', InputOption::VALUE_REQUIRED, 'Format: json (default), debug', 'json');
        $this->addArgument('fqlQuery', InputArgument::REQUIRED, 'The FQL query to parse');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser = new FilterQueryLanguage\Parser();

        $fqlQuery = $input->getArgument('fqlQuery');
        $query    = $parser->parseQuery($fqlQuery);

        $format = $input->getOption('format');

        switch ($format) {
            case 'json':
                echo json_encode($query->toArray(), \JSON_PRETTY_PRINT);
                echo "\n";
                break;

            case 'debug':
                echo "QUERY: {$fqlQuery}\n\n";
                print_r($query);
                break;

            default:
                $output->writeln('<error>Unknown output format</error>');

                return 1;
        }

        return 0;
    }
}
