<?php

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use DeskPRO\Bundle\CoreBundle\Entity;

class CreateUserCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
				new InputArgument('email', InputArgument::REQUIRED, 'The email address of the user'),
				new InputArgument('password', InputArgument::REQUIRED, 'The password of the user'),
				new InputOption('is-admin', null, InputOption::PARAMETER_NONE, 'Set this user as admin'),
		))->setName('dpdev:create-user');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$em = $this->container->get('doctrine.orm.entity_manager');
		$em->beginTransaction();

		$profile = new Entity\Profile();
		$em->persist($profile);
		$em->flush();

		$email = new Entity\ProfileEmail();
		$email['email_address'] = $input->getArgument('email');
		$email['is_validated'] = true;
		$email['profile'] = $profile;

		$em->persist($email);
		$em->persist($profile);
		$em->flush();

		$user = new Entity\User();
		$user['password'] = $input->getArgument('password');

		$usergroup = null;

		$user['profile'] = $profile;

		if ($input->hasOption('is-admin')) {
			$group = $em->find('DeskPRO\\Bundle\\Core\\Entity\\Usergroup', 1);
			$user['usergroups']->add($group);
		}

		$em->persist($user);
		$em->flush();
		$em->commit();

		$output->writeln("<info>User {$user['id']} was created</info>");
	}
}