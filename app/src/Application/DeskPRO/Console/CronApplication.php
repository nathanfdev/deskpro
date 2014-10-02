<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Console;

use Application\DeskPRO\Command\InternalUpgradeRunnerCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Application\DeskPRO\Command\WorkerJobCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class CronApplication extends BaseApplication
{
	private $kernel;
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
		return array();
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
		return new InputDefinition(array(
			new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
			new InputOption('--verbose',        '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
		));
	}
}