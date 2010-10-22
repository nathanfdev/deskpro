<?php

namespace Application\DevBundle\DataTest\Schema;

class AbstractSchema
{
	protected $output;
	protected $db;
	
	public function __construct($output, array $db_info)
	{
		$this->output = $output;
		$this->db = \Doctrine\DBAL\DriverManager::getConnection($db_info);
	}
	
	public function loadSchemaQueries()
	{
		$file = DP_ROOT.'/Application/Resources/data-test/' . get_class($this) . '.php';
		include($file);
		
		if (!isset($queries)) {
			throw new \Exception('No queries in schema file');
		}
		
		return $queries;
	}
	
	public function buildSchema()
	{
		$queries = $this->loadSchemaQueries();
		
		$this->output->write("\n\nBUILDING " . get_class($this) . " SCHEMA\n\n");
		
		// Drop if not exist
		$this->output->write('Drop database if exists ... ');
		$this->db->executeQuery("DROP DATABASE IF EXISTS {$params['dbname']}");
		$this->output->write("Done\n");

		// Create database
		$this->output->write('Create database ... ');
		$this->db->executeQuery("CREATE DATABASE {$params['dbname']}");
		$this->output->write("Done\n");
		
		// Run through queries
		$total = count($queries);
		$count = 1;
		foreach ($queries as $sql) {
			$this->output->write("Executing query " . ($count++) . " of {$total} ... ");
			$this->db->executeQuery($sql);
			$this->output->write("Done\n");
		}
		
		$this->output->write("\n\nDone building schema\n\n");
	}
}