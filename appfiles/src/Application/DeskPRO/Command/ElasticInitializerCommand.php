<?php

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Orb\Log;
use \Orb\Log\Logger;
use \Application\DeskPRO\Elastica\IndexInitializer\ContentInitializer;

class ElasticInitializerCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:elastic-initializer');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$elastica_manager = App::get('deskpro.elastica.manager');

		$logger = new Logger();
		$logger->addWriter(new Log\Writer\ConsoleOutputWriter($output));

		$content_initializer = new ContentInitializer($elastica_manager, $logger);
		$content_initializer->run();
	}
}
