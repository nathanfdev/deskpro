<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class DevGenerateSchemasCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputArgument('mysql-user', InputArgument::OPTIONAL, 'If not the same as config, the superuser (ie root) that can create/drop databases and tables'),
			new InputArgument('mysql-pass', InputArgument::OPTIONAL, 'If not the same as config, the password'),
		))->setName('dpdev:datatest-build-schemas');
	}
	
	protected function _getSchemas()
	{
		return array(
			'Application\DevBundle\DataTest\Schema\Denormalized'
		);
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$db = $this->container->get('database_connection');
		$params = $db->getParams();

		$super_params = $params;
		if ($input->getArgument('mysql-user')) {
			$super_params['user'] = $input->getArgument('mysql-user');
		}
		if ($input->getArgument('mysql-pass')) {
			$super_params['password'] = $input->getArgument('mysql-pass');
		}

		unset($super_params['dbname']);
		
		foreach ($this->_getSchemas as $schema) {
			$ns_parts = explode('\\', $schema);
			$dbname = 'dp4_datatest_' . strtolower(array_pop($ns_parts));
			
			$db_params = $super_params[$db_params];
			$db_params['dbname'] = $dbname;
			
			$schema = new $schema($output, $db_params);
			$schema->buildSchema();
		}
	}
}