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
		$this->em = $this->container->get('doctrine.orm.entity_manager');

		// Profile
		$person = new \Application\DeskPRO\Entity\Person();
		$person['password'] = 'pass';
		$person['first_name'] = 'John';
		$person['last_name'] = 'Doe';
		$person['name'] = 'John Doe';
		$person['is_contact'] = true;
		$person['is_user'] = true;
		$person['is_agent'] = true;
		$this->em->persist($person);
		$this->em->flush();

		$email = new \Application\DeskPRO\Entity\PersonEmail();
		$email['email'] = 'admin@example.com';
		$email['is_validated'] = true;
		$person->addEmailAddress($email);

		$this->em->persist($person);
		$this->em->flush();

		// Write the version file which will be used by
		// upgrade scripts
		$vfile = DP_ROOT.'/sys/VERSION';
		$version = new \DateTime();
		$version = $version->format('Y-m-d H:i:s');
		if (!@file_put_contents($vfile, $version)) {
			$output->write("<warn>\n Error writing $vfile\nThis file must contain the value: $version\nIf it does not, upgrading will not work\n</warn>\n");
		}
	}
}