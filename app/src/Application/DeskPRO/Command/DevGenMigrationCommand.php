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
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Symfony\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Symfony\Bundle\DoctrineMigrationsBundle\Command\DoctrineCommand;

use Doctrine\DBAL\Migrations\Configuration\Configuration;

use Doctrine\ORM\Tools\SchemaTool;

use Orb\Util\Util;
use Orb\Util\Numbers;

class DevGenMigrationCommand extends \Symfony\Bundle\DoctrineMigrationsBundle\Command\MigrationsDiffDoctrineCommand
{
	private static $_template =
			'<?php

namespace <namespace>;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version<version> extends AbstractMigration
{
	public function up(Schema $schema)
	{
<up>
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
';

	protected function configure()
	{
		parent::configure();
		$this->setName('dpdev:gen-migration');
		$this->setHelp("This performs a 'diff' of the current database and the entities to generate a migration class.");
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		if (!dp_get_config('debug.dev')) {
			$output->write("Dev mode is not enabled. Did you mean to use the upgrade.php command?");
			return 0;
		}

		DoctrineCommandHelper::setApplicationEntityManager($this->getApplication(), $input->getOption('em'));

		$configuration = $this->getMigrationConfiguration($input, $output);
		DoctrineCommand::configureMigrations($this->getApplication()->getKernel()->getContainer(), $configuration);

		$configuration = $this->getMigrationConfiguration($input, $output);

		$em = $this->getHelper('em')->getEntityManager();
		$conn = $em->getConnection();
		$platform = $conn->getDatabasePlatform();
		$metadata = $em->getMetadataFactory()->getAllMetadata();

		if (empty($metadata)) {
			$output->writeln('No mapping information to process.', 'ERROR');
			return;
		}

		$tool = new SchemaTool($em);

		$fromSchema = $conn->getSchemaManager()->createSchema();
		$toSchema = $tool->getSchemaFromMetadata($metadata);
		$up = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateToSql($toSchema, $platform));
		$down = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateFromSql($toSchema, $platform));

		if ( ! $up && ! $down) {
			$output->writeln('No changes detected in your mapping information.', 'ERROR');
			return;
		}

		$version = date('YmdHis');
		$path = $this->generateMigration($configuration, $input, $version, $up, $down);

		$output->writeln(sprintf('Generated new migration class to "<info>%s</info>" from schema differences.', $path));
	}


	/**
	 * Builds the PHP exec code for SQL for a certain migration step.
	 *
	 * @param \Doctrine\DBAL\Migrations\Configuration\Configuration $configuration
	 * @param array $sql
	 * @return string
	 */
	protected function buildCodeFromSql(Configuration $configuration, array $sql)
	{
		$currentPlatform = $configuration->getConnection()->getDatabasePlatform()->getName();
		$code = array();

		$ignore_tables = array(
			'content_search',
			'install_data',
			'tickets_search_active',
			'tickets_search_message',
			'tickets_search_message_active',
			'tickets_search_subject',
			'dev_migration_versions'
		);

		$regex = '#\b(' . implode($ignore_tables, '|') . ')\b#';

		foreach ($sql as $query) {
			if (preg_match($regex, $query)) {
				continue;
			}
			$code[] = "\$this->addSql(\"$query\");";
		}

		return implode("\n", $code);
	}


	/**
	 * Writes the migration file
	 *
	 * @param \Doctrine\DBAL\Migrations\Configuration\Configuration $configuration
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param $version
	 * @param null $up
	 * @param null $down
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	protected function generateMigration(Configuration $configuration, InputInterface $input, $version, $up = null, $down = null)
	{
		#----------------------------------------
		# Write the file
		#----------------------------------------

		$placeHolders = array(
			'<namespace>',
			'<version>',
			'<up>',
			'<down>'
		);
		$replacements = array(
			$configuration->getMigrationsNamespace(),
			$version,
			$up ? "        " . implode("\n        ", explode("\n", $up)) : null,
			$down ? "        " . implode("\n        ", explode("\n", $down)) : null
		);
		$code = str_replace($placeHolders, $replacements, self::$_template);
		$dir = $configuration->getMigrationsDirectory();
		$dir = $dir ? $dir : getcwd();
		$dir = rtrim($dir, '/');
		$path = $dir . '/Version' . $version . '.php';

		if (!file_exists($dir)) {
			throw new \InvalidArgumentException(sprintf('Migrations directory "%s" does not exist.', $dir));
		}

		file_put_contents($path, $code);

		file_put_contents(DP_ROOT.'/sys/VERSION', $version);

		if ($editorCmd = $input->getOption('editor-cmd')) {
			shell_exec($editorCmd . ' ' . escapeshellarg($path));
		}

		return $path;
	}
}