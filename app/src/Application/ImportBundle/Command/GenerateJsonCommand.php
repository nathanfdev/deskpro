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
 * @package Importer
 */

namespace Application\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJsonCommand extends ContainerAwareCommand
{
	protected $generators_map = array(
		'osticket'	=> 'Application\\ImportBundle\\Generator\\OsTicket'
	);
	/**
	 * {@inheritDoc}
	 */
	protected function configure()
	{
		$this->setName('dp:import:generate-json');
		$this->setHelp("This goes through a dry-run of the import process. You will only see output if there are errors. Use -v to see verbose output.");
		$this->addArgument('script', InputArgument::REQUIRED, 'The target script to use');
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public function getContainer()
	{
		return parent::getContainer();
	}


	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$script = $input->getArgument('script');
		
		$script = strtolower($script);
		
		if (!isset($this->generators_map[$script])) {
			throw new \Exception("Invalid generator :" . $script);
		}
		
		$generator_class = $this->generators_map[$script];
		
		$config = dp_get_config('osticket_import');
		
		$generator = new $generator_class($config);
		
		$generator->generateJson();
	}
}