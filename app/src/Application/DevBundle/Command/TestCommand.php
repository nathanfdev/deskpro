<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$j = new \Application\DeskPRO\Entity\WorkerJob();
		$j['id'] = 'update_view_counts';
		$j['worker_group'] = 'update_view_counts';
		$j['title'] = 'Update View Counts';
		$j['description'] = 'Updates view counts on objects';
		$j['job_class'] = 'Application\\DeskPRO\\WorkerProcess\\Job\\UpdateViewCounts';
		$j['interval'] = \Application\DeskPRO\WorkerProcess\Job\UpdateViewCounts::DEFAULT_INTERVAL;

		$em = App::getOrm();
		$em->persist($j);
		$em->flush();
	}
}
