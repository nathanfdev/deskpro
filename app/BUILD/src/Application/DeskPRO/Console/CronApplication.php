<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Console;

use Application\DeskPRO\Command\InternalUpgradeRunnerCommand;
use Application\DeskPRO\Command\WorkerJobCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class CronApplication extends BaseApplication
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    private $kernel;
    /** @var bool */
    private $commandsRegistered = false;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        parent::__construct('Symfony', Kernel::VERSION.' - '.$kernel->getName().'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));
    }

    public function getKernel()
    {
        return $this->kernel;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->kernel->boot();

        if (!$this->commandsRegistered) {
            $this->registerCommands();

            $this->commandsRegistered = true;
        }

        $container = $this->kernel->getContainer();

        foreach ($this->all() as $command) {
            if ($command instanceof ContainerAwareInterface) {
                $command->setContainer($container);
            }
        }

        $this->setDispatcher($container->get('event_dispatcher'));

        return parent::doRun($input, $output);
    }

    protected function registerCommands()
    {
        $this->add(new WorkerJobCommand());
        $this->add(new InternalUpgradeRunnerCommand());
    }

    protected function getDefaultCommands()
    {
        return [];
    }

    public function find($name)
    {
        return $this->get($name);
    }

    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('-v') || $input->hasParameterOption('--verbose=1') || $input->hasParameterOption('--verbose') || $input->getParameterOption('--verbose')) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }
    }

    protected function getDefaultHelperSet()
    {
        return new HelperSet();
    }

    protected function getDefaultInputDefinition()
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--verbose',        '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
        ]);
    }
}
