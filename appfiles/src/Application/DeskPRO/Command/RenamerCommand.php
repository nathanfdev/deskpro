<?php

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Log\Logger;

class RenamerCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:renamer');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('/\.twig$/')->in(DP_ROOT . '/src/Application');

		foreach ($finder as $filepath) {
			$new_filepath = str_replace('.twig', '.twig.html', $filepath);

			echo $new_filepath ."\n";
		}
	}
}
