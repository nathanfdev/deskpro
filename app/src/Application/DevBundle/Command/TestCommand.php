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
		$meta_path = DP_ROOT . '/../data/tmp';
		$ent_path = DP_ROOT . '/src/Application/DeskPRO/Entity';

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->in(array($meta_path))->name('*.php')->files();

		foreach ($finder as $file) {

			$name = Strings::extractRegexMatch('#\.([^\.]+)\.php$#', $file->getFilename());
			echo "Processing $name ... ";

			/** @var \Symfony\Component\Finder\SplFileInfo $file */

			$code = php_strip_whitespace($file->getRealPath());

			// Strip off the <?php and use statement
			$code = substr($code, strpos($code, '$metadata'));

			// Put each declaration on its own line
			$code = str_replace('$metadata', "\n" . '$metadata', $code);
			$code = trim($code);

			$ent_code = file_get_contents($ent_path . '/' . $name . '.php');
			if (strpos($ent_code, 'public static function loadMetadata') !== false) {
				echo "[SKIP] Appears to have been processed already\n";
				continue;
			}

			$ent_code = str_replace('use Doctrine\ORM\Mapping as ORM_Mapping;', "use Doctrine\\ORM\\Mapping\\ClassMetadata;\nuse Doctrine\\ORM\\Mapping\\ClassMetadataInfo;", $ent_code);
			$ent_code = trim($ent_code);

			// Strip off last } that closes the class
			$ent_code = rtrim($ent_code, '}');
			$ent_code = rtrim($ent_code);

			// Indent each line
			$code = explode("\n", $code);
			foreach ($code as &$l) {
				$l = "\t\t" . $l;
			}
			$code = implode("\n", $code);

			// Add our new method
			$ent_code .= "



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata \$metadata)
	{
$code
	}
}

";

			file_put_contents($ent_path . '/' . $name . '.php', $ent_code);

			echo "[DONE]\n";
		}
	}
}
