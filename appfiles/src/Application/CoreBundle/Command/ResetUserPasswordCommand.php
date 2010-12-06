<?php

namespace Application\CoreBundle\Command;

use \Application\DeskPRO\App;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class ResetUserPasswordCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputArgument('person-id', InputArgument::REQUIRED, 'The PersonID you want to set the password for')
		))->setName('deskpro:reset-user-password');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$em = App::getOrm();

		$person = $em->find('CoreBundle:Person', $input->getArgument('person-id'));
		if (!$person) {
			$output->write("<error>No record found with that PersonID</error>\n");
			return 1;
		}

		$output->write("<info>Person #{$person['id']} {$person['display_name']}</info>\n");

		$output->write("Please enter the new password> ");
		$password = trim(fgets(STDIN, 100));

		$person = $em->find('CoreBundle:Person', $input->getArgument('person-id'));
		$person['password'] = $password;

		$em->persist($person);
		$em->flush();

		$output->write("\n\nDone.\n");
	}
}