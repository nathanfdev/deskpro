<?php

namespace Application\DevBundle;

use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Util\Filesystem;
use Symfony\Bundle\FrameworkBundle\Util\Mustache;

use DeskPRO\Entities;

class CreateUserCommand extends Command
{
	protected function configure()
	{
		$this->setDefinition(array(
				new InputArgument('email', InputArgument::REQUIRED, 'The email address of the user'),
				new InputArgument('password', InputArgument::REQUIRED, 'The password of the user'),
				new InputOption('set-admin', null, InputOption::PARAMETER_OPTIONAL, 'Should this user be made an admin?', 1),
		))->setName('dpdev:create-user');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$profile = new Profile();

		$email = new ProfileEmail();
		$email['email_address'] = $input->getArgument('email');
		$email['is_validated'] = true;
		$profile['email_addresses']->add($email);

		$user = new User();
		$user['password'] = $input->getArgument('password');

		if ($input->getArgument('set-admin')) {
			$usergroup = new Usergroup();
			$usergroup['id'] = 1;
			$user['usergroups']->add($usergroup);
		}

		$user['profile'] = $profile;

		$em = $this->application->getKernel()->getContainer()->getService('doctrine.orm.entity_manager');
		$em->persist($user);

		$output->writeln("<info>User {$user['id']} was created</info>");
	}
}