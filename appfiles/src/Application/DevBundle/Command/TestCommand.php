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

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$source = new \Orb\FileStorage\Filesystem('/media/psf/Sites/deskpro/dp_400/appfiles/bin');
		
		$file = $source->getFileDescriptor('2010-12/14/3/377aa2b2-21d0-4064-a550-2139e9580e25');
		$file->output();

		return;
		$file = $source->createRandomPath();
		$file->write('blah blah blah');

		echo $file->getPath();
		echo "\n";
	}
}