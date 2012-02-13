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
		/** @var $sm \Doctrine\DBAL\Schema\AbstractSchemaManager */
		$sm = App::getDb()->getSchemaManager();

		$table = 'people';
		$indexes = $sm->listTableIndexes($table);
		$fkeys = $sm->listTableForeignKeys($table);

		$data = array('indexes' => $indexes, 'fkeys' => $fkeys);

		foreach ($data['indexes'] as $x) {
			if ($x->isPrimary()) continue;
			$p = $sm->getDatabasePlatform()->getCreateIndexSQL($x, $table);
			$p = preg_replace('#^ALTER TABLE (.*?) #', '', trim($p));
			$alter_parts[] = $p;
		}
		foreach ($data['fkeys'] as $x) {
			$p = $sm->getDatabasePlatform()->getCreateForeignKeySQL($x, $table);
			$p = preg_replace('#^ALTER TABLE (.*?) #', '', trim($p));
			$alter_parts[] = $p;
		}

		$sql = "ALTER TABLE $table " . implode(', ', $alter_parts);
		echo $sql;
	}
}
