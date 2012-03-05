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

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$finder = new \Symfony\Component\Finder\Finder();
		$finder->in(array(DP_ROOT . '/src/Application/DeskPRO/Entity'))->name('*.php')->files();

		foreach ($finder as $file) {

			$name = Strings::extractRegexMatch('#([^\.]+)\.php$#', $file->getFilename());
			echo "Processing $name ... ";

			/** @var \Symfony\Component\Finder\SplFileInfo $file */

			$code = file($file->getRealPath(), \FILE_IGNORE_NEW_LINES);
			$code_end = array();

			foreach ($code as $l) {
				if (strpos($l, '@ORM_Mapping') === false) {
					$code_end[] = $l;
				}
			}

			$code_end = implode("\n", $code_end);
			file_put_contents(DP_ROOT . '/src/Application/DeskPRO/Entity/' . $name . '.php', $code_end);

			echo "[DONE]\n";
		}
	}
}
