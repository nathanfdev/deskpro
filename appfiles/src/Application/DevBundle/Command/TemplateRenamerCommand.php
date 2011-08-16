<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Log\Logger;

/**
 * Used when Symfony changed naming scheme for tempaltes in PR5
 */
class TemplateRenamerCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dpdev:template-renamer');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('/\.php/')->in(DP_ROOT . '/src/Application/DevBundle/Resources/views');

		foreach ($finder as $filepath) {
			$new_filepath = str_replace('.php', '.html.php', $filepath);

			echo $new_filepath ."\n";

			\exec("git mv {$filepath} {$new_filepath}");
		}
	}
}
