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
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\InstallBundle\Util\GenBuildManifest;
use Orb\Util\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DevCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dpdev');
		$this->addOption('regen-build-manifest', null, InputOption::VALUE_NONE, 'Regenerate build-manifest.php file');
		$this->addOption('preview', null, InputOption::VALUE_NONE, 'Preview');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('regen-build-manifest')) {
			return $this->regenBuildManifestAction($input, $output);
		} else {
			$output->write("<error>Unknown command</error>");
			return 1;
		}
	}


	private function regenBuildManifestAction(InputInterface $input, OutputInterface $output)
	{
		$manifest_path = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php';
		$builds_path   = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';

		$gen = new GenBuildManifest($builds_path);
		$file = $gen->getContents();

		if ($input->getOption('preview')) {
			echo $file;
			return 0;
		} else {
			if (file_put_contents($manifest_path, $file)) {
				echo "Wrote manifest file: $manifest_path\n";
				return 0;
			} else {
				echo "Failed to write manifest file: $manifest_path\n";
				return 1;
			}
		}
	}
}