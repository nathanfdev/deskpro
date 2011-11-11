<?php

namespace ReportBundle\Tests\Stat\Base;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
*
*/
abstract class BaseStatTest extends WebTestCase
{
	public static function setUpBeforeClass()
	{
		// Create the database
		$this->createDatabase;
	}

	public static function tearDownAfterClass()
	{
		// Destroy the database
	}

	protected function createDatabase()
	{
		$kernel = static::createKernel();
        $kernel->boot();
die();

		// We can get params really easy without conencting since its lazy-connect
		$db = $this->container->get('database_connection');
		$params = $db->getParams();

		$super_params = $params;
		if ($input->getArgument('mysql-user')) {
			$super_params['user'] = $input->getArgument('mysql-user');
		}
		if ($input->getArgument('mysql-pass')) {
			$super_params['user'] = $input->getArgument('mysql-pass');
		}

		var_dump($params);
		var_dump($super_params);

		die();

		unset($super_params['dbname']);
		$super_db = \Doctrine\DBAL\DriverManager::getConnection($super_params);

		#------------------------------
		# Recreate database
		# - Since we have FK's to worry about, it's hard to just drop tables. Easier
		#   to simply drop the database and recreate it.
		#------------------------------

		// Drop if not exist
		$output->write('Drop database if exists ... ');
		$super_db->executeQuery("DROP DATABASE IF EXISTS {$params['dbname']}");
		$output->write("Done\n");

		// Create database
		$output->write('Create database ... ');
		$super_db->executeQuery("CREATE DATABASE {$params['dbname']}");
		$output->write("Done\n");

		// We need to reconnect to the database
		$super_params['dbname'] = $params['dbname'];
		$super_db->close();
		$super_db = \Doctrine\DBAL\DriverManager::getConnection($super_params);


		#------------------------------
		# Now generate the schema based off our entities
		#------------------------------

		// Get the SQL
		$em = $this->container->get('doctrine.orm.entity_manager');
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
		$all_sql = $tool->getCreateSchemaSql($metadata);

		// Execute the SQL
		$total = count($all_sql);
		$count = 1;
		foreach ($all_sql as $sql) {
		$sql = str_replace('INNODB', 'MyISAM', $sql);
			$output->write("Executing query " . ($count++) . " of {$total} ... ");
			$super_db->executeQuery($sql);
			$output->write("Done\n");
		}

		$output->write("\n\nALL DONE\n\n");

		$output->write("You can now fill in some example data by using the dpdev:new-install-data command.\n\n");
	}

	protected function destroyDatabase()
	{

	}
}