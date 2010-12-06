<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \Application\DeskPRO\App;

class DatatestFillDbCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:datatest-fill-db');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$db = App::getDb();

		$dataset = new \Application\DevBundle\DataTest\DataSet\Basic();
		$schema_gen = new \Application\DevBundle\DataTest\Generator\Current($dataset);
		$schema_gen->run($db, $output);
	}
}