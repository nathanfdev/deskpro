<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 * @package Generator
 */

namespace Application\ImportBundle;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\Generator\GeneratorInterface;
use Orb\Util\OptionsArray;
use Symfony\Component\Console\Input\InputInterface;
use Psr\Log\LoggerInterface;

class GeneratorFactory
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var \Symfony\Component\Console\Input\InputInterface|null
	 */
	private $input;
	
	protected $generators_map = array(
		'osticket'	=> 'Application\\ImportBundle\\Generator\\OsTicket',
		'csv'		=> 'Application\\ImportBundle\\Generator\\Csv',
	);


	/**
	 * @param DeskproContainer $container
	 * @param InputInterface   $input
	 */
	public function __construct(DeskproContainer $container, InputInterface $input = null)
	{
		$this->container = $container;
		$this->input = $input;
	}


	/**
	 * @return GeneratorConfig
	 */
	public function createGeneratorConfig()
	{
		$config = new GeneratorConfig();

		$import_config = new OptionsArray(dp_get_config('import', array()));
		$config->output_path	= $import_config->get('output_path');
		$config->log_path	= $import_config->get('log_path', dp_get_log_dir() . '/export');
		$config->mode		= $import_config->get('mode', 'test');
		$config->mark_done	= $import_config->get('mark_done', true);

		if ($this->input) {
			if ($this->input->hasArgument('script')) {
				$config->script = $this->input->getArgument('script');
			}
			
			if ($this->input->hasOption('output-path')) {
				$config->output_path = $this->input->getOption('output-path');
			}
			if ($this->input->hasOption('input-path')) {
				$config->input_path = $this->input->getOption('input-path');
			}
			if ($this->input->hasOption('log-path')) {
				$config->log_path = $this->input->getOption('log-path');
			}
			if ($this->input->hasOption('mode')) {
				$config->mode = $this->input->getOption('mode');
			}
			if ($this->input->hasOption('live')) {
				$config->mode = 'live';
			}
			if ($this->input->hasOption('mark-done')) {
				$config->mark_done = (bool)$this->input->getOption('mark-done');
			}
		}

		if (!is_dir($config->output_path)) {
			throw new \InvalidArgumentException(sprintf("Invalid configuration: data_path is invalid (got %s)", $config->output_path));
		}

		return $config;
	}


	/**
	 * @param LoggerInterface $logger
	 * @return Generator
	 */
	public function createGenerator(GeneratorConfig $config, LoggerInterface $logger = null)
	{
		$generator_class = $this->generators_map[$config->script];
		
		$generator = new $generator_class($config, $logger);
		
		if (!$generator instanceof GeneratorInterface) {
			throw new \Exception($generator_class . ' is not a valid generator');
		}
		
		if (!method_exists($generator,'generateJson')) {
			throw new \Exception($generator_class . ' does not have a "generateJson" method');
		}
		
		return $generator;
	}
}