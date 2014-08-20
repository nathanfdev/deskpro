<?php

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
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
	 * @var string
	 */
	private $log_file;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('dp:elastica:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('auto-reset', null, InputOption::VALUE_REQUIRED, 'Internal')
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset index before populating')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Start indexing at offset', 0)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time between persisting iterations (microseconds)', 0)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Index packet size (overrides provider config option)', 500)
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->setDescription('Populates search indexes from providers')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
		$this->log_file = dp_get_log_dir() . '/es-indexer.log';
		if (file_exists($this->log_file)) {
			@unlink($this->log_file);
		}
		@touch($this->log_file);
		@chmod($this->log_file, 0777);

        $this->indexManager = $this->getContainer()->get('fos_elastica.index_manager');
        $this->providerRegistry = $this->getContainer()->get('fos_elastica.provider_registry');
        $this->resetter = $this->getContainer()->get('fos_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		if ($input->getOption('auto-reset') && App::$container->getSetting('elastica.requires_reset_started') != $input->getOption('auto-reset')) {
			return;
		}

		$reset = App::$container->getSetting('elastica.requires_reset');
		if ($input->getOption('reset') || $input->getOption('auto-reset')) {
			$reset = true;
		}

		$this->getContainer()->getDb()->delete('settings', array('name' => 'elastica.requires_reset'));
		$this->getContainer()->getDb()->delete('settings', array('name' => 'elastica.requires_reset_started'));

		if (!App::$container->getSetting('elastica.enabled')) {
			$output->writeln("<error>Elastic Search is not enabled.");
			return 1;
		}

        $indexes = array_keys($this->indexManager->getAllIndexes());
		$em = App::$container->getEm();

		$es_status = $em->getRepository('DeskPRO:DataStore')->getByName('sys.es_indexer', true);
		$es_status->setData('date_created', new \DateTime());
		$es_status->setData('date_last', new \DateTime());
		$es_status->setData('date_completed', null);
		$es_status->setData('status', 'running');

		$all_totals = array();

		foreach ($indexes as $index) {
			/** @var $providers DoctrineProvider[] */
			$providers = $this->providerRegistry->getIndexProviders($index);

			foreach ($providers as $type => $provider) {
				$provider_id = $index . '_' . $type;
				$total = $provider->getCounts();
				$all_totals[$provider_id] = $total;

				$es_status->setData($provider_id . '_total', $total);
				$es_status->setData($provider_id . '_done', 0);
			}
		}

		$em->persist($es_status);
		$em->flush();

        foreach ($indexes as $index) {

            /** @var $providers DoctrineProvider[] */
            $providers = $this->providerRegistry->getIndexProviders($index);

			if ($reset) {
				$output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
				$this->resetter->resetIndex($index);
			}

            foreach ($providers as $type => $provider) {

				$provider_id = $index . '_' . $type;

                $total = $all_totals[$provider_id];
                $offset = $input->getOption('offset');
                $batchSize = $input->getOption('batch-size');

                for (; $offset < $total; $offset += $batchSize) {
                    $arguments = $this->getArguments($input, $index, $type, $offset, ($offset + $batchSize), $batchSize);
                    $this->runCommand($arguments, $output);

					$done = min($total, $offset + $batchSize);
					$es_status->setData($provider_id . '_done', $done);
					$es_status->setData('date_last', new \DateTime());

					$em->persist($es_status);
					$em->flush();
                }
            }
        }

		$es_status->setData('status', 'complete');
		$es_status->setData('date_completed', new \DateTime());
		$em->persist($es_status);
		$em->flush();
    }

    private function runCommand($arguments, OutputInterface $output)
    {
		$php_path = dp_get_php_path(false);
		$file = escapeshellarg(realpath(DP_ROOT . '/../cmd.php'));
        $command = $php_path . ' ' . $file . ' dp:elastica:index ' . implode(' ', $arguments);
        $process = new Process($command);

		$log_file = $this->log_file;
        $process->run(function ($type, $buffer) use ($output, $log_file) {
			$time_prefix = '[' . date('Y-m-d H:i:s') . '] ';
            if (Process::ERR === $type) {
				file_put_contents($log_file, $time_prefix . 'ERROR: ' . trim($buffer). "\n", FILE_APPEND);
				$output->write("<error>$buffer</error>");
            } else {
				file_put_contents($log_file, $time_prefix . trim($buffer) ."\n", FILE_APPEND);
				$output->write($buffer);
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