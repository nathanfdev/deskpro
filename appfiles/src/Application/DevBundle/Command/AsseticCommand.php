<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;


/**
 * dpdev:compile-js
 *
 * Compiles and minifies JS source files.
 *
 * NOTE: This command assumes default file structure, where assets are stored in
 * /static
 */
class AsseticCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputArgument('pack', InputArgument::REQUIRED, 'The packs to compile separated by comma. Example: agent_vendors. Or ALL for everything'),
		))->setName('dpdev:assetic');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$packs = $input->getArgument('pack');
		$assetic_manager = $this->getContainer()->getSystemService('assetic_manager');

		$bundles = array();

		if ($packs == 'ALL') {
			$bundles = $assetic_manager->getAllBundleNames();
		} else {
			foreach (explode(',', $packs) as $p) {
				$p = trim($p);
				$bundles[] = $p;
			}
		}

		foreach ($bundles as $name) {
			echo "[PROCESSING] $name ... ";
			$assetic_manager->writeBuildFileIfStale($name);
			echo " Done\n";
		}
	}
}
