<?php

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\NewSearch\Provider\Doctrine as DoctrineProvider;
use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Provider\ProviderRegistry;
use FOS\ElasticaBundle\Resetter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Populate Elasticsearch Command.
 *
 * The primary command for populating Elasticsearch with
 * data from the DB. Uses the dp:elastica:index command
 * to achieve the actual result.
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
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->setDescription('Populates search indexes from providers');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->log_file = dp_get_log_dir().'/es-indexer.log';
        if (file_exists($this->log_file)) {
            @unlink($this->log_file);
        }
        @touch($this->log_file);
        @chmod($this->log_file, 0777);

        $this->indexManager     = $this->getContainer()->get('fos_elastica.index_manager');
        $this->providerRegistry = $this->getContainer()->get('fos_elastica.provider_registry');
        $this->resetter         = $this->getContainer()->get('fos_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        @set_time_limit(0);
        if ($input->getOption('auto-reset') && App::$container->getSetting('elastica.requires_reset_started') != $input->getOption('auto-reset') && $input->getOption('auto-reset') != 'requires_reset') {
            return;
        }

        $reset = App::$container->getSetting('elastica.requires_reset');
        if ($input->getOption('reset') || $input->getOption('auto-reset')) {
            $reset = true;
        }

        if ($input->getOption('auto-reset') == 'requires_reset' && !$reset) {
            return;
        }

        $this->getContainer()->getDb()->delete('settings', ['name' => 'elastica.requires_reset']);
        $this->getContainer()->getDb()->delete('settings', ['name' => 'elastica.requires_reset_started']);

        if (!App::$container->getSetting('elastica.enabled')) {
            $output->writeln('<error>Elasticsearch is not enabled.');

            return 1;
        }

        $indexes = array_keys($this->indexManager->getAllIndexes());
        $em      = App::$container->getEm();

        /** @var DataStore $es_status */
        $es_status = $em->getRepository(DataStore::class)->getByName('sys.es_indexer', true);
        $es_status->setData('date_created', new \DateTime());
        $es_status->setData('date_last', new \DateTime());
        $es_status->setData('date_completed', null);
        $es_status->setData('status', 'running');

        $all_totals = [];

        foreach ($indexes as $index) {
            /** @var $providers DoctrineProvider[] */
            $providers = $this->providerRegistry->getIndexProviders($index);

            foreach ($providers as $type => $provider) {
                $provider_id              = $index.'_'.$type;
                $total                    = $provider->getCounts();
                $all_totals[$provider_id] = $total;

                $es_status->setData($provider_id.'_total', $total);
                $es_status->setData($provider_id.'_done', 0);
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
                $provider_id = $index.'_'.$type;

                $total     = $all_totals[$provider_id];
                $offset    = 0;
                $batchSize = 500;

                for (; $offset < $total; $offset += $batchSize) {
                    $arguments = $this->getArguments($input, $index, $type, $offset, $batchSize, $reset);
                    $this->runCommand($arguments, $output);

                    $done = min($total, $offset + $batchSize);
                    $es_status->setData($provider_id.'_done', $done);
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

    /**
     * @param                 $arguments
     * @param OutputInterface $output
     */
    private function runCommand($arguments, OutputInterface $output)
    {
        $command = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand('dp:elastica:index '.implode(' ', $arguments));
        $process = new Process($command);
        $process->setTimeout(600);

        $log_file = $this->log_file;
        $process->run(function ($type, $buffer) use ($output, $log_file) {
            $time_prefix = '['.date('Y-m-d H:i:s').'] ';
            if (Process::ERR === $type) {
                file_put_contents($log_file, $time_prefix.'ERROR: '.trim($buffer)."\n", FILE_APPEND);
                $output->write("<error>$buffer</error>");
            } else {
                file_put_contents($log_file, $time_prefix.trim($buffer)."\n", FILE_APPEND);
                $output->write($buffer);
            }
        });
    }

    /**
     * @param InputInterface $input
     * @param                $index
     * @param                $type
     * @param int            $offset
     * @param int            $batchSize
     * @param int            $doReset
     *
     * @return array
     */
    private function getArguments($input, $index, $type, $offset, $batchSize, $doReset)
    {
        $arguments = [];

        $arguments[] = '--index="'.$index.'"';
        $arguments[] = '--type="'.$type.'"';
        $arguments[] = '--offset="'.$offset.'"';
        $arguments[] = '--batch-size="'.$batchSize.'"';
        $arguments[] = '--single-batch';
        $arguments[] = '--no-reset';

        if ($input->hasOption('ignore-errors')) {
            $arguments[] = '--ignore-errors';
        }

        return $arguments;
    }
}
