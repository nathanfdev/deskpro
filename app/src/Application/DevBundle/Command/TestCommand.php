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
		/** @var $reader \Doctrine\Common\Annotations\CachedReader */
		$reader = $this->getContainer()->get('annotation_reader');

		/** @var $parser \Doctrine\Common\Annotations\DocParser */
		$parser = $reader->delegate->parser;

		$parser->parse('/** * Tracks notification preferences for each agent on each queue. * * @ORM_Mapping\\Entity(repositoryClass="Application\\DeskPRO\\EntityRepository\\AgentNotification") * @ORM_Mapping\\Table(name="agent_notifications") */');

		$parser->parser->setTarget(1);
        $parser->parser->setImports($reader->delegate->getImports($class));
        $parser->parser->setIgnoredAnnotationNames($reader->getIgnoredAnnotationNames($class));

		echo "done\n";
	}
}
