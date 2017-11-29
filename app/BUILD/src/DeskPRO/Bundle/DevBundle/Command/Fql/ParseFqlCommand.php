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
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output options: expr for expression engine, debug for debug out, json for JSON', 'debug');
        $this->addArgument('query', InputArgument::REQUIRED, 'The FQL query to parse');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser = new FilterQueryLanguage\Parser();

        $query = $input->getArgument('query');
        $parts = $parser->parseQuery($query);

        switch ($input->getOption('output')) {
            case 'debug':
                $debugc = new FilterQueryLanguage\DebugCompiler();
                echo $debugc->compile($parts);
                echo "\n";
                break;

            case 'json':
                echo json_encode($parts, \JSON_PRETTY_PRINT);
                echo "\n";
                break;

            case 'expr':
                $eec = new FilterQueryLanguage\ExpressionCompiler();
                echo $eec->compile($parts);
                echo "\n";
                break;

            default:
                $output->writeln('<error>Unknown output format</error>');

                return 1;
        }

        return 0;
    }
}
