<?php

namespace Application\DeskPRO\Command;

use Application\DeskPRO\Elastica\ProgressClosureBuilder;
use FOS\ElasticaBundle\Event\IndexPopulateEvent;
use FOS\ElasticaBundle\Event\TypePopulateEvent;
use FOS\ElasticaBundle\IndexManager;
use FOS\ElasticaBundle\Provider\ProviderRegistry;
use FOS\ElasticaBundle\Resetter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index Elasticsearch Command.
 *
 * Extends the populate command from FOS_Elastica bundle and
 * adds an extra argument for limiting the indexing up to a
 * certain number.
 */
class IndexElasticsearchCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var ProgressClosureBuilder
     */
    private $progressClosureBuilder;

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
            ->setName('dp:elastica:index')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not reset index before populating')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Start indexing at offset', 0)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time between persisting iterations (microseconds)', 0)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Index packet size (overrides provider config option)')
            ->addOption('single-batch', null, InputOption::VALUE_NONE, 'Only do one batch per process')
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Do not stop on errors')
            ->addOption('no-overwrite-format', null, InputOption::VALUE_NONE, 'Prevent this command from overwriting ProgressBar\'s formats')
            ->setDescription('Populates search indexes from providers of a specific index and type')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->dispatcher             = $this->getContainer()->get('event_dispatcher');
        $this->indexManager           = $this->getContainer()->get('fos_elastica.index_manager');
        $this->providerRegistry       = $this->getContainer()->get('fos_elastica.provider_registry');
        $this->resetter               = $this->getContainer()->get('fos_elastica.resetter');
        $this->progressClosureBuilder = new ProgressClosureBuilder();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index   = $input->getOption('index');
        $type    = $input->getOption('type');
        $reset   = !$input->getOption('no-reset');
        $options = [
            'ignore_errors' => $input->getOption('ignore-errors'),
            'offset'        => $input->getOption('offset'),
            'sleep'         => $input->getOption('sleep'),
        ];
        if ($input->getOption('batch-size')) {
            $options['batch_size'] = (int) $input->getOption('batch-size');
        }
        if ($input->getOption('single-batch')) {
            $options['single_batch'] = true;
        }

        if ($input->isInteractive() && $reset && $input->getOption('offset')) {
            /** @var DialogHelper $dialog */
            $dialog = $this->getHelperSet()->get('dialog');
            if (!$dialog->askConfirmation($output, '<question>You chose to reset the index and start indexing with an offset. Do you really want to do that?</question>', true)) {
                return;
            }
        }

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if (null !== $index) {
            if (null !== $type) {
                $this->populateIndexType($output, $index, $type, $reset, $options);
            } else {
                $this->populateIndex($output, $index, $reset, $options);
            }
        } else {
            $indexes = array_keys($this->indexManager->getAllIndexes());

            foreach ($indexes as $index) {
                $this->populateIndex($output, $index, $reset, $options);
            }
        }
    }

    /**
     * Recreates an index, populates its types, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param bool            $reset
     * @param array           $options
     */
    private function populateIndex(OutputInterface $output, $index, $reset, $options)
    {
        $event = new IndexPopulateEvent($index, $reset, $options);
        $this->dispatcher->dispatch(IndexPopulateEvent::PRE_INDEX_POPULATE, $event);

        if ($event->isReset()) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            $this->resetter->resetIndex($index, true);
        }

        $types = array_keys($this->providerRegistry->getIndexProviders($index));
        foreach ($types as $type) {
            $this->populateIndexType($output, $index, $type, false, $event->getOptions());
        }

        $this->dispatcher->dispatch(IndexPopulateEvent::POST_INDEX_POPULATE, $event);

        $this->refreshIndex($output, $index);
    }

    /**
     * Deletes/remaps an index type, populates it, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param string          $type
     * @param bool            $reset
     * @param array           $options
     */
    private function populateIndexType(OutputInterface $output, $index, $type, $reset, $options)
    {
        $event = new TypePopulateEvent($index, $type, $reset, $options);
        $this->dispatcher->dispatch(TypePopulateEvent::PRE_TYPE_POPULATE, $event);

        if ($event->isReset()) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
            $this->resetter->resetIndexType($index, $type);
        }

        $provider = $this->providerRegistry->getProvider($index, $type);

        $lastStep = null;
        $current  = $options['offset'];
        $action   = 'Populating';

        $loggerClosure = function ($increment, $totalObjects, $message = null) use ($output, $action, $index, $type, &$lastStep, &$current) {
            if ($current + $increment > $totalObjects) {
                $increment = $totalObjects - $current;
            }

            if (null !== $message) {
                $output->writeln(sprintf('<info>%s</info> <error>%s</error>', $action, $message));
            }

            $currentTime      = microtime(true);
            $timeDifference   = $currentTime - $lastStep;
            $objectsPerSecond = $lastStep ? ($increment / $timeDifference) : $increment;
            $lastStep         = $currentTime;
            $current += $increment;
            $percent = 100 * $current / $totalObjects;

            $output->writeln(sprintf(
                '<info>%s</info> <comment>%s/%s</comment> %0.1f%% (%d/%d), %d objects/s (RAM: current=%uMo peak=%uMo)',
                $action,
                $index,
                $type,
                $percent,
                $current,
                $totalObjects,
                $objectsPerSecond,
                round(memory_get_usage() / (1024 * 1024)),
                round(memory_get_peak_usage() / (1024 * 1024))
            ));
        };

        $provider->populate($loggerClosure, $event->getOptions());
        $this->dispatcher->dispatch(TypePopulateEvent::POST_TYPE_POPULATE, $event);

        $this->refreshIndex($output, $index, false);
    }

    /**
     * Refreshes an index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param bool            $postPopulate
     */
    private function refreshIndex(OutputInterface $output, $index, $postPopulate = true)
    {
        if ($postPopulate) {
            $this->resetter->postPopulate($index);
        }
    }
}
