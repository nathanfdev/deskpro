<?php

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
