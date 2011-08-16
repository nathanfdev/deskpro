<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class DatatestTestSchemasCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputArgument('run', InputArgument::OPTIONAL, 'Only run this specific test'),
		))->setName('dpdev:datatest-test-schemas');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$super_params = $this->container->get('database_connection')->getParams();
		//$super_params = array_merge($super_params, )

		unset($super_params['dbname']);
		$super_db = \Doctrine\DBAL\DriverManager::getConnection($super_params);

						#------------------------------
		# Basic
		#------------------------------

//		$basic_db_params = $super_params;
//		$basic_db_params['dbname'] = 'test_database';
//		$basic_db = \Doctrine\DBAL\DriverManager::getConnection($basic_db_params);
//
//		$schema = new \Application\DevBundle\DataTest\Schema\Book();
//
//		$this->_testSchema($basic_db, $schema, $input, $output);
//return;

				#------------------------------
		# Basic
		#------------------------------

		$basic_db_params = $super_params;
		$basic_db_params['dbname'] = 'dp_working_01';
		$basic_db = \Doctrine\DBAL\DriverManager::getConnection($basic_db_params);

		$schema = new \Application\DevBundle\DataTest\Schema\Working01();

		$this->_testSchema($basic_db, $schema, $input, $output);
return;

		#------------------------------
		# Basic
		#------------------------------

		$basic_db_params = $super_params;
		$basic_db_params['dbname'] = 'dp_test_basic';
		$basic_db = \Doctrine\DBAL\DriverManager::getConnection($basic_db_params);

		$schema = new \Application\DevBundle\DataTest\Schema\Basic();

		$this->_testSchema($basic_db, $schema, $output);
return;
		#------------------------------
		# Denormalized
		#------------------------------

		$denormalized_db_params = $super_params;
		$denormalized_db_params['dbname'] = 'dp_test_denormalized';
		$denormalized_db = \Doctrine\DBAL\DriverManager::getConnection($denormalized_db_params);

		$schema = new \Application\DevBundle\DataTest\Schema\Denormalized();

		$this->_testSchema($denormalized_db, $schema, $output);
	}

	protected function _testSchema(\DeskPRO\DBAL\Connection $db, $schema, InputInterface $input, OutputInterface $output)
	{
		$output->write("\n<comment>########################################\nTESTING " . get_class($schema) . "\n########################################</comment>\n");

		$ref = new \ReflectionClass(get_class($schema));
		$tests = array();

		foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if (strpos($method->name, 'test') === 0) {
				$tests[] = $method->name;
			}
		}

		$output->write("<info>We have " . count($tests) . " tests to run through.</info>\n");

		$only_run = null;
		if ($input->getArgument('run')) {
			$only_run = $input->getArgument('run');
		}

		foreach ($tests as $method) {

			if ($only_run AND $method != $only_run) {
				continue;
			}

			$sqls = $schema->$method();
			if (!is_array($sqls)) $sqls = array('main' => $sqls);

			$output->write("<info>--------------------------------------------------\n");
			$output->write($method);
			$output->write("\n--------------------------------------------------</info>\n");

			$best = null;
			$best_name = null;
			foreach ($sqls as $name => $sql) {

				$sql = str_replace("SELECT", "SELECT SQL_NO_CACHE", $sql);

				$output->write(str_pad($name, 20, ' ') . ' ... ');

				$times = array();
				$times_all = 0.0;
				$times_max = null;
				for ($i = 0; $i < 5; $i++) {

					$db->executeQuery("RESET QUERY CACHE");
					$db->executeQuery("FLUSH QUERY CACHE");
					$db->executeQuery("FLUSH TABLES");

					$time_start = microtime(true);

					$q = $db->executeQuery($sql);
					$rows = $q->RowCount();

					$time_end = microtime(true);
					$time_total = $time_end - $time_start;
					$times_all += $time_total;

					$output->write(sprintf("%.5f ", $time_total));

					if ($time_total > 10) {
						$output->write("Took {$time_total} seconds. Took too long, I won't waste time getting averages.\n");
						break;
					}

					if ($times_max === null OR $time_total > $times_max) {
						$times_max = $time_total;
					}

					$times[] = (float)$times_all;
				}

				if ($times) {
					$times_avg = $times_all / 5;

					if ($times_avg < $best OR $best === null) {
						$best = $time_total;
						$best_name = $name;
					}

					$output->write(sprintf("\n".str_pad('', 25, ' ')."Rows: %6s, Avg: %.5f, Min: %.5f, Max: %.5f\n", $rows, $times_avg, min($times), $times_max));
				}

				$output->write("Query: $sql\n\n");
				$output->write("Here's an explain:\n");
				$explain = $db->fetchAll("EXPLAIN " . $sql);
				print_r($explain);

				$output->write("\n");
			}

			if ($best_name AND count($sqls) > 1) {
				$output->write("<info>Best: {$best_name}</info>\n");
			}

			$output->write("\n\n");
		}
	}
}