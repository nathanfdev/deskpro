<?php

namespace ReportBundle\Tests\Stat\DeskPRO;

use Application\DeskPRO\App;
use ReportBundle\Tests\Stat\Base;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Application\DeskPRO\Entity\Stat;

/**
*
*/
class TestTicketsAwaitingAgent extends WebTestCase
{
	static protected $class = 'DeskPRO\Kernel\TestKernel';

	protected $em = null;

	public static function setUpBeforeClass()
	{
		//self::initDatabase();
	}

	public static function tearDownAfterClass()
	{
	}

	public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()
             ->get('doctrine.orm.entity_manager');
    }

	public function testQuery()
	{
		var_dump("load fixtures");
		$ticket = new Stat();

		$this->em->persist($ticket);

		$this->em->flush();

		$this->assertTrue(true);
	}

	protected function loadFixtures()
	{

	}

	protected static function initDatabase()
	{
		$kernel = static::createKernel();
		$kernel->boot();

		$db = $kernel->getContainer()->get('database_connection');

		$db_params = $db->getParams();

		$originalDbNamae = $db_params['dbname'];
		unset($db_params['dbname']);

		$tempDb = \Doctrine\DBAL\DriverManager::getConnection($params);
		$params['dbname'] = $originalDbNamae. '_test_' . time();

		// Create database
		$tempDb->executeQuery("CREATE DATABASE {$params['dbname']}");

		// We need to reconnect to the database
		$tempDb->close();
		$db = \Doctrine\DBAL\DriverManager::getConnection($params);

		// Get the SQL
		$em 		= $kernel->getContainer()->get('doctrine.orm.entity_manager');
		$metadata 	= $em->getMetadataFactory()->getAllMetadata();
		$tool		= new \Doctrine\ORM\Tools\SchemaTool($em);
		$schemaSql 	= $tool->getCreateSchemaSql($metadata);

		// Execute the SQL
		$total = count($schemaSql);
		$count = 1;
		foreach ($schemaSql as $sql) {
			$sql = str_ireplace('INNODB', 'MyISAM', $sql);
			var_dump("Executing query " . ($count++) . " of {$total} ... ");
			$db->executeQuery($sql);
		}
	}

	protected static function destroyDatabase()
	{
		// Drop if not exist
		$db->executeQuery("DROP DATABASE IF EXISTS {$params['dbname']}");
	}
}