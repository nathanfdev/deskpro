<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

use Application\DeskPRO\App;

class DevCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dpdev:dev');
		$this->addOption('reset-routing', null, InputOption::VALUE_NONE, "Deletes cached routing so it will be regenerated next load");
		$this->addOption('reset-templates', null, InputOption::VALUE_NONE, "Deletes compiled template files");
		$this->addOption('reset-cache', null, InputOption::VALUE_NONE, "Deletes the `cache` table");
		$this->addOption('find-unused-templates', null, InputOption::VALUE_NONE, "Tries to find unused templates");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('reset-routing')) {
			$output->writeln("Deleting cached routing ...");

			$finder = new \Symfony\Component\Finder\Finder();

			$dirs = array();
			if (is_dir(DP_ROOT.'/sys/cache/dev')) $dirs[] = DP_ROOT.'/sys/cache/dev';
			if (is_dir(DP_ROOT.'/sys/cache/prod')) $dirs[] = DP_ROOT.'/sys/cache/prod';

			$finder->name('/Url(Generator|Matcher)/')->in($dirs);
			$fs = new \Symfony\Component\Filesystem\Filesystem();
			foreach ($finder as $file) {
				$output->writeln(sprintf("<info>Removing %s</info>", $file->getFileName()));
				$fs->remove($file->getRealPath());
			}

			$output->writeln("Done.");
		} else if ($input->getOption('reset-templates')) {
			$fs = new \Symfony\Component\Filesystem\Filesystem();
			$fs->remove(DP_ROOT.'/sys/cache/twig-compiled');
			$output->writeln("Done");
		} else if ($input->getOption('reset-templates')) {
			App::getDb()->exec("TRUNCATE TABLE cache");
			$output->writeln("Done");
		} else if ($input->getOption('find-unused-templates')) {
			return $this->executeFindUnusedTemplates($input, $output);
		}
	}

	protected function executeFindUnusedTemplates(InputInterface $input, OutputInterface $output)
	{
		$out = null;
		exec('ack --help', $out);
		$out = implode(' ', $out);
		if (!$out || strpos($out, 'ACK_OPTIONS') === false) {
			$output->writeln('This tool requires `ack`. See http://betterthangrep.com/');
			return 1;
		}

		$paths = array(
			'AdminBundle'      => DP_ROOT.'/src/Application/AdminBundle/Resources/views',
			'AgentBundle'      => DP_ROOT.'/src/Application/AgentBundle/Resources/views',
			'DeskPRO'          => DP_ROOT.'/src/Application/DeskPRO/Resources/views',
			'ReportBundle'     => DP_ROOT.'/src/Application/ReportBundle/Resources/views',
			'UserBundle'       => DP_ROOT.'/src/Application/UserBundle/Resources/views',
			'BillingBundle'    => DP_ROOT.'/src/Application/BillingBundle/Resources/views',
		);

		foreach ($paths as $bundle => $dir) {
			$finder = new \Symfony\Component\Finder\Finder();
			$finder->files()->name('*.twig')->in($dir);

			foreach ($finder as $file) {
				/** @var \Symfony\Component\Finder\SplFileinfo $file */

				$filepath = $file->getRealPath();

				$tplname = str_replace($dir . '/', ':', $filepath);
				$tplname = str_replace('/', ':', $tplname);
				if (substr_count($tplname, ':') < 2) {
					$tplname = ':' . $tplname; // for layouts that are in top dir, MyBundle::layout
				}
				$tplname = $bundle . $tplname;

				$out = null;
				$cmd = 'ack -r --literal --count --no-filename --max-count=1 -1 ' . escapeshellarg($tplname) . ' ' . DP_ROOT.'/src ' . DP_ROOT.'/sys';
				exec($cmd,$out);
				if (!$out) $out = array(0);
				$out = implode(' ', $out);
				$out = (int)$out[0];

				if (!$out) {
					echo "Template appears to be unused: " . $tplname;
					echo "\n";
				}
			}
		}
	}
}