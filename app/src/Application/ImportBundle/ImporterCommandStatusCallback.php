<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle;

use Application\ImportBundle\ValueImporter\AbstractValueImporter;
use Orb\Util\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ImporterCommandStatusCallback extends ImporterStatusCallback
{
	/**
	 * @var \Symfony\Component\Console\Command\Command
	 */
	private $command;

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	private $output;

	/**
	 * @var \Symfony\Component\Console\Helper\ProgressBar
	 */
	private $current_progress;


	/**
	 * @param Command         $command
	 * @param OutputInterface $output
	 */
	public function __construct(Command $command, OutputInterface $output)
	{
		$this->command = $command;
		$this->output = $output;

	}


	public function preStep(Importer $importer, AbstractValueImporter $value_importer, $dir)
	{
		if ($this->current_progress) {
			$this->current_progress->finish();
			$this->current_progress = null;
		}

		$this->current_progress = new ProgressBar($this->output);
		$this->current_progress->start();
		$this->current_progress->setMessage("Running step: " . Util::getBaseClassname($value_importer));
	}


	public function postStep(Importer $importer, AbstractValueImporter $value_importer, $dir, $count, $time)
	{
		$this->current_progress->finish();
		$this->current_progress = null;
	}

	public function preImportValue(Importer $importer, AbstractValueImporter $value_importer, \SplFileInfo $file, $count, array $data)
	{
		$this->current_progress->advance();
	}
}