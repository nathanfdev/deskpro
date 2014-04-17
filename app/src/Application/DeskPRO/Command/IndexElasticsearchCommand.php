<?php

namespace Application\DeskPRO\Command;

use FOS\ElasticaBundle\Command\PopulateCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Index Elasticsearch Command
 *
 * Extends the populate command from FOS_Elastica bundle and
 * adds an extra argument for limiting the indexing up to a
 * certain number.
 *
 * @package DeskPRO
 */
class IndexElasticsearchCommand extends PopulateCommand
{
    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('dp:elastica:index')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not reset index before populating')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Start indexing at offset', 0)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Stop indexing at limit')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time between persisting iterations (microseconds)', 0)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Index packet size (overrides provider config option)')
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->setDescription('Populates search indexes from providers of a specific index and type')
        ;
    }
}
