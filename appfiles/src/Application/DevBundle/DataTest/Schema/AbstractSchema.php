<?php

namespace Application\DevBundle\DataTest\Schema;

use \Orb\Util\Util;

class AbstractSchema
{	
	public function loadSchemaQueries()
	{
		$file = DP_ROOT.'/src/Application/DevBundle/Resources/data-test-schema/' . Util::getBaseClassname($this)  . '.php';
		include($file);
		
		if (!isset($queries)) {
			throw new \Exception('No queries in schema file');
		}
		
		return $queries;
	}
	
	public function buildSchema(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$queries = $this->loadSchemaQueries();
		
		// Run through queries
		$total = count($queries);
		$count = 1;
		foreach ($queries as $sql) {
			$output->write("Executing query " . ($count++) . " of {$total} ... ");
			$db->executeQuery($sql);
			$output->write("Done\n");
		}
		
		$output->write("\n\nDone building schema\n\n");
	}
}