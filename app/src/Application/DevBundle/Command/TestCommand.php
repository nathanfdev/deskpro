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
		$sc = new \Application\InstallBundle\Data\GenerateSchema(App::getOrm());
		$em = App::getOrm();

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->in(DP_ROOT.'/src/Application/DeskPRO/Entity')->name('*.php')->files();

		foreach ($finder as $file) {
			/** @var $file \Symfony\Component\Finder\SplFileInfo */

			$name = Strings::extractRegexMatch('#(.*?)\.php$#', $file->getFilename());
			echo "$name ... ";

			$classname = 'Application\\DeskPRO\\Entity\\' . $name;
			$metadata = $em->getMetadataFactory()->getMetadataFor($classname);
			$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
			$all_sql = $tool->getCreateSchemaSql(array($metadata));

			echo "\n";
		}
	}
}
