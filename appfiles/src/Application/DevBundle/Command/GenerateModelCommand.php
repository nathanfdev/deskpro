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



/**
 * dpdev:generate-model
 *
 * Generates the SQL to create a table for a model.
 */
class GenerateModelCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputArgument('model', InputArgument::REQUIRED, 'The model name'),
		))->setName('dpdev:generate-model');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		#------------------------------
		# Now generate the schema based off our entities
		#------------------------------

		// Get the SQL
		$em = $this->container->get('doctrine.orm.entity_manager');
		$metadata = $em->getMetadataFactory()->getMetadataFor($input->getArgument('model'));
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
		$all_sql = $tool->getCreateSchemaSql(array($metadata));

		foreach ($all_sql as $sql) {
			echo $sql;
			echo "\n";
		}

		echo "\n";
	}
}