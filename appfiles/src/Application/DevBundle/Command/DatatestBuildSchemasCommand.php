<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class DatatestBuildSchemasCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:datatest-build-schemas');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$super_params = $this->container->get('database_connection')->getParams();
		//$super_params = array_merge($super_params, )

		unset($super_params['dbname']);
		$super_db = \Doctrine\DBAL\DriverManager::getConnection($super_params);

		// Drop if not exist
		$output->write('Drop database if exists ... ');
		$super_db->executeQuery("DROP DATABASE IF EXISTS dp_test_basic");
		$output->write("Done\n");

		// Create database
		$output->write('Create database ... ');
		$super_db->executeQuery("CREATE DATABASE dp_test_basic");
		$output->write("Done\n");

		$basic_db_params = $super_params;
		$basic_db_params['dbname'] = 'dp_test_basic';
		$basic_db = \Doctrine\DBAL\DriverManager::getConnection($basic_db_params);

		$dataset = new \Application\DevBundle\DataTest\DataSet\Basic();
		$basic = new \Application\DevBundle\DataTest\Generator\Basic($dataset);
		$basic->buildSchema($basic_db, $output);

		$basic->run($basic_db, $output);
	}
}