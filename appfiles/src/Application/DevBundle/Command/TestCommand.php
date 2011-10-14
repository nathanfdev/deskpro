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

use Orb\Util\Arrays;
use Orb\Util\Strings;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$notfound = array();

		$search_paths = array(
			realpath(DP_ROOT . '/../static/stylesheets-less'),
			realpath(DP_ROOT . '/../static/stylesheets'),
			realpath(DP_ROOT . '/src/Application/AdminBundle/Resources'),
			realpath(DP_ROOT . '/src/Application/AgentBundle/Resources'),
			realpath(DP_ROOT . '/src/Application/UserBundle/Resources'),
		);

		$path = realpath(DP_ROOT . '/../static/images');

		$it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
		foreach ($it as $filename => $file) {

			$filename = $file->getFilename();
			if ($filename[0] == '.') {
				continue;
			}

			$nicepath = str_replace($path, '', $file->getRealPath());
			echo "[Check] $nicepath ... ";

			$found = false;
			foreach ($search_paths as $search_path) {
				exec('grep -m 1 -l -r \'' . $file->getFilename() . '\' ' . $search_path, $out);
				$out = Arrays::removeEmptyString($out);

				if ($out && !empty($out)) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				echo "\tNot Found";
				$notfound[] = $nicepath;
			} else {
				echo "\tFound";
			}

			echo "\n";
		}

		if ($notfound) {
			echo "\n\n";
			echo "These files were not found:\n";
			echo "\t " . implode("\n\t ", $notfound) . "\n";
		}
	}
}
