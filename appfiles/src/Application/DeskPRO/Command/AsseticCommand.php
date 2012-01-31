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

namespace Application\DeskPRO\Command;

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
			new InputOption('regex', 'p', InputOption::VALUE_NONE, 'Pack name is interpretted as a regex'),
			new InputOption('reload', 'r', InputOption::VALUE_NONE, 'Files are regenerated even if they arent stale'),
		))->setName('dp:assetic');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$packs = $input->getArgument('pack');
		$assetic_manager = $this->getContainer()->getSystemService('assetic_manager');

		$bundles = array();

		if ($packs == 'ALL' || $input->getOption('regex')) {
			if ($input->getOption('regex')) {
				foreach ($assetic_manager->getAllBundleNames() as $k) {
					if (preg_match('#' . $packs . '#', $k)) {
						$bundles[] = $k;
					}
				}
			} else {
				$bundles = $assetic_manager->getAllBundleNames();
			}
		} else {
			foreach (explode(',', $packs) as $p) {
				$p = trim($p);
				$bundles[] = $p;
			}
		}

		$reload = $input->getOption('reload');

		foreach ($bundles as $name) {
			echo "[PROCESSING] $name ... ";
			if ($reload) {
				echo 'reload ';
				$assetic_manager->writeBuildFile($name);
			} else {
				$assetic_manager->writeBuildFileIfStale($name);
			}
			echo " Done\n";
		}
	}
}
