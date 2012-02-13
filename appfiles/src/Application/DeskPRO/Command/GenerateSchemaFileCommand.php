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
 * dpdev:generate-schema-file
 */
class GenerateSchemaFileCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputOption('save', 'w', InputOption::VALUE_NONE, 'Save to the InstallBundle directory instead of outputting'),
		))->setName('dp:generate-schema-file');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');

		$sc = new \Application\InstallBundle\Data\GenerateSchema($em);
		$php = $sc->getPhpFile();

		$do_save = $input->getOption('save');
		if ($do_save) {
			file_put_contents(DP_ROOT.'/src/Application/InstallBundle/Data/schema.php', $php);
		} else {
			echo $php;
		}
	}
}
