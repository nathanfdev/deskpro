<?php

namespace Application\DeskPRO\Command;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use FOS\ElasticaBundle\Resetter;
use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Provider\ProviderRegistry;

use Application\DeskPRO\NewSearch\Provider\Doctrine as DoctrineProvider;

/**
 * Populate Elasticsearch Command
 *
 * The primary command for populating Elasticsearch with
 * data from the DB. Uses the dp:elastica:index command
 * to achieve the actual result.
 *
 * @package DeskPRO
 */
class PopulateElasticsearchCommand extends ContainerAwareCommand
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var ProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var Resetter
     */
    private $resetter;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('dp:elastica:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset index before populating')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Start indexing at offset', 0)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time between persisting iterations (microseconds)', 0)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Index packet size (overrides provider config option)', 100)
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->setDescription('Populates search indexes from providers')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->providerRegistry = $this->getContainer()->get('fos_elastica.provider_registry');
        $this->resetter = $this->getContainer()->get('fos_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexes = array_keys($this->indexManager->getAllIndexes());

        foreach ($indexes as $index) {

            /** @var $providers DoctrineProvider[] */
            $providers = $this->providerRegistry->getIndexProviders($index);

            foreach ($providers as $type => $provider) {

                if ($input->getOption('reset')) {
                    $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
                    $this->resetter->resetIndexType($index, $type);
                }

                $total = $provider->getCounts();
                $offset = $input->getOption('offset');
                $batchSize = $input->getOption('batch-size');

                for (; $offset < $total; $offset += $batchSize) {
                    $arguments = $this->getArguments($input, $index, $type, $offset, ($offset + $batchSize), $batchSize);
                    $this->runCommand($arguments);
                }

            }

        }
    }

    private function runCommand($arguments)
    {
        $command = 'php cmd.php dp:elastica:index ' . implode(' ', $arguments);
        $process = new Process($command);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer;
            }
        });
    }

    private function getArguments($input, $index, $type, $offset, $limit, $batchSize)
    {
        $arguments = array();

        $arguments[] = '--index="' . $index . '"';
        $arguments[] = '--type="' . $type . '"';
        $arguments[] = '--offset="' . $offset . '"';
        $arguments[] = '--limit="' . $limit . '"';
        $arguments[] = '--batch-size="' . $batchSize . '"';
        $arguments[] = '--no-reset ';

        if ($input->hasOption('sleep')) {
            $arguments[] = '--sleep="' . $input->getOption('sleep') . '"';
        }

        if ($input->hasOption('ignore-errors')) {
            $arguments[] = '--ignore-errors="' . $input->getOption('ignore-errors') . '"';
        }

        return $arguments;
    }
} 